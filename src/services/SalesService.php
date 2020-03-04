<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\web\ForbiddenHttpException;

/**
 * @property int $count
 */
class SalesService extends Component
{
    /**
     * Returns plugin sales.
     *
     * @param int|null $limit
     *
     * @return SaleModel[]
     */
    public function get($limit = null): array
    {
        $saleModels = [];

        $saleRecords = SaleRecord::find()
            ->with('plugin')
            ->limit($limit)
            ->all();

        foreach ($saleRecords as $saleRecord) {
            $saleModel = new SaleModel();
            $saleModel->setAttributes($saleRecord->getAttributes(), false);

            $saleModel->pluginName = $saleRecord->plugin->name;
            $saleModel->grossAmount = number_format($saleModel->grossAmount, 2);
            $saleModel->netAmount = number_format($saleModel->netAmount, 2);

            $saleModels[] = $saleModel;
        }

        return $saleModels;
    }

    /**
     * Returns monthly plugin sale totals.
     *
     * @param int|null $limit
     *
     * @return array
     */
    public function getMonthlyTotals($limit = null): array
    {
        return SaleRecord::find()
            ->select([
                'MONTH(dateSold) as month',
                'YEAR(dateSold) as year',
                'COUNT(*) as count',
                'ROUND(SUM(grossAmount), 2) as grossAmount',
                'ROUND(SUM(netAmount), 2) as netAmount',
            ])
            ->groupBy(['YEAR(dateSold)', 'MONTH(dateSold)'])
            ->orderBy(['YEAR(dateSold)' => SORT_ASC, 'MONTH(dateSold)' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Returns first month of plugin sales.
     *
     * @return array|null
     */
    public function getFirstMonth()
    {
        return SaleRecord::find()
            ->select(['MONTH(dateSold) as month', 'YEAR(dateSold) as year'])
            ->orderBy(['YEAR(dateSold)' => SORT_ASC, 'MONTH(dateSold)' => SORT_ASC])
            ->asArray()
            ->one();
    }

    /**
     * Returns last month of plugin sales.
     *
     * @return array|null
     */
    public function getLastMonth()
    {
        return SaleRecord::find()
            ->select(['MONTH(dateSold) as month', 'YEAR(dateSold) as year'])
            ->orderBy(['YEAR(dateSold)' => SORT_DESC, 'MONTH(dateSold)' => SORT_DESC])
            ->asArray()
            ->one();
    }

    /**
     * Refreshes plugin sales.
     *
     * @throws ForbiddenHttpException
     */
    public function refresh()
    {
        $client = new Client([
            'base_uri' => 'https://id.craftcms.com/',
            'cookies' => true,
        ]);

        $response = $client->get('login');
        $body = $response->getBody();

        // Extract CSRF token value
        preg_match('/csrfTokenValue:\s* "([\s\S]*?)"/', $body, $matches);
        $csrfTokenValue = $matches[1] ?? null;
        $csrfTokenValue = json_decode('"'.$csrfTokenValue.'"');

        if ($csrfTokenValue === null) {
            throw new ForbiddenHttpException(Craft::t('plugin-sales', 'Could not fetch a valid CSRF token.'));
        }

        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-Token' => $csrfTokenValue,
        ];

        // Authenticate
        $client->post('index.php?p=actions//users/login', [
            'headers' => $headers,
            'multipart' => [
                [
                    'name' => 'loginName',
                    'contents' => 'putyourlightson',
                ],
                [
                    'name' => 'password',
                    'contents' => 'ETBRXGXZfZZTT3mxMKVYmRGG',
                ],
            ],
        ]);

        // Get total
        $response = $client->get('index.php?p=actions//craftnet/id/sales/get-sales&per_page=1', [
            'headers' => $headers,
        ]);

        $result = json_decode($response->getBody(), true);
        $total = $result['total'];
        $count = SaleRecord::find()->count();

        if ($total <= $count) {
            return;
        }

        $limit = $total - $count;

        // Get new sales
        $response = $client->get('index.php?p=actions//craftnet/id/sales/get-sales&per_page='.$limit, [
            'headers' => $headers,
        ]);

        $result = json_decode($response->getBody(), true);
        $sales = $result['data'];

        // Save sale records
        foreach ($sales as $sale) {
            if (empty($sale['plugin']['id'])) {
                Craft::dd($sale);
            }
            PluginSales::$plugin->plugins->create($sale['plugin']['id'], $sale['plugin']['name'], $sale['plugin']['hasMultipleEditions']);

            $saleRecord = SaleRecord::find()
                ->where(['saleId' => $sale['id']])
                ->one();

            if ($saleRecord === null) {
                $saleRecord = new SaleRecord();
            }

            $saleRecord->setAttributes([
                'saleId' => $sale['id'],
                'pluginId' => $sale['plugin']['id'],
                'edition' => $sale['edition']['handle'],
                'renewal' => ($sale['purchasableType'] == 'craftnet\\plugins\\PluginRenewal'),
                'grossAmount' => $sale['grossAmount'],
                'netAmount' => $sale['netAmount'],
                'email' => $sale['customer']['email'],
                'dateSold' => $sale['saleTime'],
            ], false);

            $saleRecord->save();
        }
    }
}

<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\records\FetchRecord;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\web\ForbiddenHttpException;

/**
 * @property SaleModel[] $sales
 * @property null|string|false $lastFetchedDate
 */
class SalesService extends Component
{
    /**
     * Returns plugin sales.
     *
     * @return SaleModel[]
     */
    public function getSales(): array
    {
        $saleModels = [];

        $saleRecords = SaleRecord::find()
            ->with('plugin')
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
     * Returns last fetched date.
     *
     * @return string|null|false
     */
    public function getLastFetchedDate()
    {
        return FetchRecord::find()
            ->select(['dateCreated'])
            ->orderBy(['dateCreated' => SORT_DESC])
            ->scalar();
    }

    /**
     * Refreshes plugin sales.
     *
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function refresh(): bool
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
                    'contents' => PluginSales::$plugin->settings->email,
                ],
                [
                    'name' => 'password',
                    'contents' => PluginSales::$plugin->settings->password,
                ],
            ],
        ]);

        // Get total
        try {
            $response = $client->get('index.php?p=actions//craftnet/id/sales/get-sales&per_page=1', [
                'headers' => $headers,
            ]);
        }
        catch (GuzzleException $exception) {
            return false;
        }

        $result = json_decode($response->getBody(), true);
        $total = $result['total'];
        $count = SaleRecord::find()->count();
        $sales = [];

        if ($total > $count) {
            $limit = $total - $count;

            // Get new sales
            $response = $client->get('index.php?p=actions//craftnet/id/sales/get-sales&per_page='.$limit, [
                'headers' => $headers,
            ]);

            $result = json_decode($response->getBody(), true);
            $sales = $result['data'];

            // Save sale records
            foreach ($sales as $sale) {
                PluginSales::$plugin->plugins->createIfNotExists(
                    $sale['plugin']['id'],
                    $sale['plugin']['name'],
                    $sale['plugin']['hasMultipleEditions']
                );

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

        $fetchRecord = new FetchRecord();
        $fetchRecord->fetched = count($sales);
        $fetchRecord->save();

        return true;
    }
}

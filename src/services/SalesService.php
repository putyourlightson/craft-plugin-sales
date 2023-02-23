<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\DateTimeHelper;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use putyourlightson\logtofile\LogToFile;
use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\records\PluginRecord;
use putyourlightson\pluginsales\records\RefreshRecord;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\web\ForbiddenHttpException;

/**
 *
 *
 * @property-read float $exchangeRate
 * @property-read null|array $lastRefresh
 * @property-read SaleModel[] $sales
 */
class SalesService extends Component
{
    /**
     * @var array|null
     */
    private $_lastRefresh;

    /**
     * Returns plugin sales.
     *
     * @return SaleModel[]
     */
    public function getSales(): array
    {
        $saleModels = [];

        /** @var SaleRecord[] $saleRecords */
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
     * Returns last refresh.
     *
     * @return array|null
     */
    public function getLastRefresh()
    {
        if ($this->_lastRefresh !== null) {
            return $this->_lastRefresh;
        }

        $this->_lastRefresh = RefreshRecord::find()
            ->orderBy(['dateCreated' => SORT_DESC])
            ->asArray()
            ->one();

        return $this->_lastRefresh;
    }

    /**
     * Returns exchange rate.
     *
     * @return float
     */
    public function getExchangeRate(): float
    {
        $lastRefresh = $this->getLastRefresh();

        return $lastRefresh['exchangeRate'] ?? 1;
    }

    /**
     * Refreshes plugin sales.
     *
     * @param callable|null $setProgressHandler
     *
     * @return int|bool
     * @throws ForbiddenHttpException
     */
    public function refresh(callable $setProgressHandler = null)
    {
        App::maxPowerCaptain();

        $client = new Client([
            'base_uri' => 'https://console.craftcms.com/',
            'cookies' => true,
        ]);

        try {
            $response = $client->get('login');
        }

        catch (GuzzleException $exception) {
            LogToFile::error($exception->getMessage(), 'plugin-sales');

            return false;
        }

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
                    'contents' => Craft::parseEnv(PluginSales::$plugin->settings->email),
                ],
                [
                    'name' => 'password',
                    'contents' => Craft::parseEnv(PluginSales::$plugin->settings->password),
                ],
            ],
        ]);

        // Get organisation ID
        $organisationId = PluginSales::$plugin->settings->organisationId;
        if (empty($organisationId)) {
            try {
                $response = $client->get('orgs', [
                    'headers' => $headers,
                ]);
            }
            catch (GuzzleException $exception) {
                LogToFile::error($exception->getMessage(), 'plugin-sales');

                return false;
            }

            $result = json_decode($response->getBody(), true);
            $organisationId = $result['orgs'][0]['id'] ?? null;

            if ($organisationId === null) {
                LogToFile::error('No organisation found.', 'plugin-sales');

                return false;
            }
        }

        $baseSalesUri = 'index.php?p=actions/craftnet/console/sales/get-sales&orgId=' . $organisationId;

        // Get total
        try {
            $response = $client->get($baseSalesUri . '&page=1&limit=1', [
                'headers' => $headers,
            ]);
        }
        catch (GuzzleException $exception) {
            LogToFile::error($exception->getMessage(), 'plugin-sales');

            return false;
        }

        $result = json_decode($response->getBody(), true);
        $total = $result['total'];
        $stored = SaleRecord::find()->count();
        $sales = [];

        if ($total > $stored) {
            // Add 1 because one too few sales were being returned for some strange reason
            $limit = $total - $stored + 1;

            // Get new sales
            $response = $client->get($baseSalesUri . '&page=1&limit='.$limit, [
                'headers' => $headers,
            ]);

            $result = json_decode($response->getBody(), true);
            $sales = $result['data'];
            $count = 0;

            // Save sale records
            foreach ($sales as $sale) {
                PluginSales::$plugin->plugins->create(
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

                if (is_callable($setProgressHandler)) {
                    $count++;
                    call_user_func($setProgressHandler, $count, $limit);
                }
            }
        }

        $refreshed = count($sales);

        $refreshRecord = new RefreshRecord();
        $refreshRecord->refreshed = $refreshed;
        $refreshRecord->currency = PluginSales::$plugin->settings->currency;
        $refreshRecord->exchangeRate = 1;

        // Get live exchange rate if not USD
        if (PluginSales::$plugin->settings->currency != 'USD') {
            $refreshRecord->exchangeRate = $this->_getExchangeRateFromApi($client);
        }

        $refreshRecord->save();

        return $refreshed;
    }

    /**
     * Deletes all plugin sales.
     */
    public function delete()
    {
        SaleRecord::deleteAll();
        PluginRecord::deleteAll();
    }

    /**
     * Returns the exchange rate from the API, at most once per day.
     *
     * @return float|null
     */
    private function _getExchangeRateFromApi(Client $client): float
    {
        $lastExchangeRate = 1;
        $lastRefresh = $this->getLastRefresh();

        if ($lastRefresh !== null) {
            $lastExchangeRate = $lastRefresh['exchangeRate'];

            $lastRefreshDate = DateTimeHelper::toDateTime($lastRefresh['dateCreated']);
            $now = new DateTime();

            if ($lastRefreshDate->format('Y-m-d') == $now->format('Y-m-d')) {
                return $lastExchangeRate;
            }
        }

        try {
            $response = $client->get('https://freecurrencyapi.net/api/v1/rates?base_currency=USD');
        }
        catch (GuzzleException $exception) {
            LogToFile::error($exception->getMessage(), 'plugin-sales');

            return $lastExchangeRate;
        }

        $result = json_decode($response->getBody(), true);
        $rates = reset($result['data']) ?? null;
        $rate = $rates[PluginSales::$plugin->settings->currency] ?? null;

        if ($rate === null) {
            LogToFile::error(Craft::t('plugin-sales', 'Could not find exchange rate for {currency}.', ['currency' => PluginSales::$plugin->settings->currency,]), 'plugin-sales');

            return $lastExchangeRate;
        }

        return $rate;
    }
}

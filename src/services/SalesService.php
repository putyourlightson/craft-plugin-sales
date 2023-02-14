<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\records\PluginRecord;
use putyourlightson\pluginsales\records\RefreshRecord;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\log\Logger;
use yii\web\ForbiddenHttpException;

/**
 * @property-read float $exchangeRate
 * @property-read null|array $lastRefresh
 * @property-read SaleModel[] $sales
 */
class SalesService extends Component
{
    /**
     * @var array|null
     */
    private ?array $_lastRefresh = null;

    /**
     * Returns plugin sales.
     *
     * @return SaleModel[]
     */
    public function getSales(string $email = null): array
    {
        $saleModels = [];
        $condition = $email ? ['email' => $email] : [];

        /** @var SaleRecord[] $saleRecords */
        $saleRecords = SaleRecord::find()
            ->where($condition)
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
     */
    public function getLastRefresh(): ?array
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
     */
    public function getExchangeRate(): float
    {
        $lastRefresh = $this->getLastRefresh();

        return $lastRefresh['exchangeRate'] ?? 1;
    }

    /**
     * Refreshes plugin sales.
     */
    public function refresh(callable $setProgressHandler = null): bool|int
    {
        App::maxPowerCaptain();

        $client = new Client([
            'base_uri' => 'https://console.craftcms.com/',
            'cookies' => true,
        ]);

        try {
            $response = $client->get('login');
        } catch (GuzzleException $exception) {
            PluginSales::$plugin->log($exception->getMessage(), [], Logger::LEVEL_ERROR);

            return false;
        }

        $body = $response->getBody();

        // Extract CSRF token value
        preg_match('/csrfTokenValue:\s* "([\s\S]*?)"/', $body, $matches);
        $csrfTokenValue = $matches[1] ?? null;
        $csrfTokenValue = json_decode('"' . $csrfTokenValue . '"');

        if ($csrfTokenValue === null) {
            throw new ForbiddenHttpException(Craft::t('plugin-sales', 'Could not fetch a valid CSRF token.'));
        }

        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-Token' => $csrfTokenValue,
        ];

        // Authenticate
        $client->post('index.php?p=actions/users/login', [
            'headers' => $headers,
            'multipart' => [
                [
                    'name' => 'loginName',
                    'contents' => App::parseEnv(PluginSales::$plugin->settings->email),
                ],
                [
                    'name' => 'password',
                    'contents' => App::parseEnv(PluginSales::$plugin->settings->password),
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
            } catch (GuzzleException $exception) {
                PluginSales::$plugin->log($exception->getMessage(), [], Logger::LEVEL_ERROR);

                return false;
            }

            $result = json_decode($response->getBody(), true);
            $organisationId = $result['orgs'][0]['id'] ?? null;

            if ($organisationId === null) {
                PluginSales::$plugin->log('No organisation found.', [], Logger::LEVEL_ERROR);

                return false;
            }
        }

        $baseSalesUri = 'index.php?p=actions/craftnet/console/sales/get-sales&orgId=' . $organisationId;

        // Get total
        try {
            $response = $client->get($baseSalesUri . '&page=1&limit=1', [
                'headers' => $headers,
            ]);
        } catch (GuzzleException $exception) {
            PluginSales::$plugin->log($exception->getMessage(), [], Logger::LEVEL_ERROR);

            return false;
        }

        $result = json_decode($response->getBody(), true);
        $total = $result['total'];
        $stored = SaleRecord::find()->count();
        $refreshCount = 0;

        if ($total > $stored) {
            // Divide total into pages to avoid timeouts
            $limit = 100;
            $amount = $total - $stored;
            $pages = (int)ceil($amount / $limit);

            for ($page = 1; $page <= $pages; $page++) {
                if ($pages == 1) {
                    $limit = $amount;
                }

                $response = $client->get($baseSalesUri . '&page=' . $page . '&limit=' . $limit, [
                    'headers' => $headers,
                ]);

                $result = json_decode($response->getBody(), true);
                $refreshCount += $this->_saveSales($result['data']);

                if (is_callable($setProgressHandler)) {
                    call_user_func($setProgressHandler, $refreshCount, $amount);
                }
            }
        }

        $this->_updateFirstSales();

        $refreshRecord = new RefreshRecord();
        $refreshRecord->refreshed = $refreshCount;
        $refreshRecord->currency = PluginSales::$plugin->settings->currency;
        $refreshRecord->exchangeRate = 1;

        // Get live exchange rate if not USD
        if (PluginSales::$plugin->settings->currency != 'USD') {
            $refreshRecord->exchangeRate = $this->_getExchangeRateFromApi($client);
        }

        $refreshRecord->save();

        return $refreshCount;
    }

    /**
     * Deletes all plugin sales.
     */
    public function delete()
    {
        SaleRecord::deleteAll();
        PluginRecord::deleteAll();

        // Reset the auto increment values
        Craft::$app->getDb()->createCommand()->executeResetSequence(SaleRecord::tableName());
        Craft::$app->getDb()->createCommand()->executeResetSequence(PluginRecord::tableName());
    }

    /**
     * Returns the exchange rate from the API, at most once per day.
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
            $response = $client->get('https://api.exchangerate.host/latest?base=USD');
        } catch (GuzzleException $exception) {
            PluginSales::$plugin->log($exception->getMessage(), [], Logger::LEVEL_ERROR);

            return $lastExchangeRate;
        }

        $result = json_decode($response->getBody(), true);
        $rates = $result['rates'] ?? null;
        $rate = $rates[PluginSales::$plugin->settings->currency] ?? null;

        if ($rate === null) {
            PluginSales::$plugin->log('Could not find exchange rate for {currency}.', ['currency' => PluginSales::$plugin->settings->currency, ], Logger::LEVEL_ERROR);

            return $lastExchangeRate;
        }

        return $rate;
    }

    /**
     * Updates or creates the provided sales as records.
     */
    private function _saveSales(array $sales): int
    {
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
                'notice' => $sale['adjustments'][0]['name'] ?? null,
                'dateSold' => $sale['saleTime'],
            ], false);

            if ($saleRecord->save()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Updates all first sale records.
     */
    private function _updateFirstSales(): void
    {
        // Reset all firsts
        Db::update(SaleRecord::tableName(), ['first' => false]);

        $pluginIds = PluginRecord::find()->select('id')->column();

        foreach ($pluginIds as $pluginId) {
            // Use a subquery to select first sale per customer per plugin
            $dateSoldArray = SaleRecord::find()
                ->select('MIN(dateSold) as dateSold')
                ->where(['pluginId' => $pluginId])
                ->groupBy(['email', 'pluginId'])
                ->column();

            $saleRecordIds = SaleRecord::find()
                ->select('id')
                ->where([
                    'pluginId' => $pluginId,
                    'dateSold' => $dateSoldArray,
                ])
                ->column();

            Db::update(
                SaleRecord::tableName(),
                [
                    'first' => true,
                ],
                [
                    'id' => $saleRecordIds,
                    'renewal' => false,
                    'first' => false,
                ]
            );
        }
    }
}

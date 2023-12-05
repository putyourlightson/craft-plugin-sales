<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use putyourlightson\pluginsales\PluginSales;
use yii\web\Response;

class SlideoutController extends Controller
{
    /**
     * Renders a slideout.
     */
    public function actionRender(): Response
    {
        $customer = $this->request->getRequiredParam('customer');
        $url = $this->_getUrlFromCustomer($customer);
        $sales = PluginSales::$plugin->reports->getSalesData($customer);

        return $this->asCpScreen()
            ->contentTemplate('plugin-sales/_slideout', [
                'customer' => $customer,
                'url' => $url,
                'sales' => $sales,
            ]);
    }

    private function _getUrlFromCustomer(?string $customer): ?string
    {
        if ($customer === null) {
            return null;
        }

        $domain = explode('@', $customer)[1] ?? null;

        if ($domain === null) {
            return null;
        }

        // Common email provider list taken from `common.json`.
        // https://www.npmjs.com/package/email-providers
        $path = Craft::getAlias('@putyourlightson/pluginsales/data/exclude-domains.json');
        $excludeDomains = Json::decodeFromFile($path);

        if (in_array($domain, $excludeDomains)) {
            return null;
        }

        return 'https://' . $domain;
    }
}

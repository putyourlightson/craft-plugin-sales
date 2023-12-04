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
        $email = $this->request->getRequiredParam('email');
        $url = $this->_getUrlFromEmail($email);
        $sales = PluginSales::$plugin->reports->getSalesData(email: $email);

        return $this->asCpScreen()
            ->contentTemplate('plugin-sales/_slideout', [
                'email' => $email,
                'url' => $url,
                'sales' => $sales,
            ]);
    }

    private function _getUrlFromEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $domain = explode('@', $email)[1] ?? null;

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

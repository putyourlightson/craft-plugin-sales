<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * ReportsAsset bundle
 */
class ReportsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@putyourlightson/pluginsales/resources';

        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page when this asset bundle is registered
        $this->css = [
            'https://cdn.jsdelivr.net/npm/daterangepicker@3/daterangepicker.css',
            'css/cp.css',
        ];
        $this->js = [
            'https://cdn.jsdelivr.net/npm/apexcharts@3',
            'https://cdn.jsdelivr.net/npm/moment@2',
            'https://cdn.jsdelivr.net/npm/daterangepicker@3',
            'js/cp.js',
            'js/CustomerSlideout.js',
        ];

        parent::init();
    }
}

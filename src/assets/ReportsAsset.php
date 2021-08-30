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
            'https://cdn.datatables.net/v/dt/dt-1.11.0/r-2.2.9/datatables.min.css',
            'css/cp.css',
        ];
        $this->js = [
            'https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.js',
            'https://cdn.jsdelivr.net/npm/moment@2/moment.min.js',
            'https://cdn.jsdelivr.net/npm/daterangepicker@3/daterangepicker.min.js',
            'https://cdn.datatables.net/v/dt/dt-1.11.0/r-2.2.9/datatables.min.js',
        ];

        parent::init();
    }
}

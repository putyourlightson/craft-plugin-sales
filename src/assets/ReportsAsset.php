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
            'css/cp.css',
            'css/jquery.dataTables.min.css',
        ];
        $this->js = [
            'js/apexcharts.js',
            'js/jquery.dataTables.min.js',
        ];

        parent::init();
    }
}

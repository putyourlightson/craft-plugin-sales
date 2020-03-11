<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use craft\base\Component;
use putyourlightson\pluginsales\models\PluginModel;
use putyourlightson\pluginsales\records\PluginRecord;

/**
 * @property PluginModel[] $plugins
 * @property string[] $names
 */
class PluginsService extends Component
{
    /**
     * @var int[]|null
     */
    private $_pluginIds;

    /**
     * Returns plugins.
     *
     * @return PluginModel[]
     */
    public function getPlugins(): array
    {
        $pluginModels = [];
        $pluginRecords = PluginRecord::find()->all();

        foreach ($pluginRecords as $pluginRecord) {
            $pluginModel = new PluginModel();
            $pluginModel->setAttributes($pluginRecord, false);
            $pluginModels[] = $pluginModel;
        }

        return $pluginModels;
    }

    /**
     * Returns plugin names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return PluginRecord::find()
            ->select('name')
            ->column();
    }

    /**
     * Creates a plugin if it doesn't already exist.
     *
     * @param int $id
     * @param string $name
     * @param bool $hasMultipleEditions
     */
    public function create(int $id, string $name, bool $hasMultipleEditions)
    {
        if ($this->_pluginIds === null) {
            $this->_pluginIds = PluginRecord::find()->select('id')->column();
        }

        if (in_array($id, $this->_pluginIds)) {
            return;
        }

        $pluginRecord = new PluginRecord([
            'id' => $id,
            'name' => $name,
            'hasMultipleEditions' => $hasMultipleEditions,
        ]);

        $pluginRecord->save();

        $this->_pluginIds[] = $pluginRecord->id;
    }
}

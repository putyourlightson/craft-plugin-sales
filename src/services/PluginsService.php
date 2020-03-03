<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use craft\base\Component;
use putyourlightson\pluginsales\models\PluginModel;
use putyourlightson\pluginsales\records\PluginRecord;

class PluginsService extends Component
{
    /**
     * @var int[]|null
     */
    private $_pluginIds;

    /**
     * Returns plugins.
     *
     * @param int|null $limit
     *
     * @return PluginModel[]
     */
    public function get(int $limit = null): array
    {
        $pluginModels = [];
        $pluginRecords = PluginRecord::find()->limit($limit)->all();

        foreach ($pluginRecords as $pluginRecord) {
            $pluginModel = new PluginModel();
            $pluginModel->setAttributes($pluginRecord, false);
            $pluginModels[] = $pluginModel;
        }

        return $pluginModels;
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

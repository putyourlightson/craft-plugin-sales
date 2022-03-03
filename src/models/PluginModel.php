<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\models;

use craft\base\Model;

class PluginModel extends Model
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var bool
     */
    public bool $hasMultipleEditions;
}

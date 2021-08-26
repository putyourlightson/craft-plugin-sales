<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\models;

use craft\base\Model;
use DateTime;

class SaleModel extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $saleId;

    /**
     * @var int
     */
    public $pluginId;

    /**
     * @var string
     */
    public $edition;

    /**
     * @var bool
     */
    public $renewal;

    /**
     * @var string
     */
    public $email;

    /**
     * @var float
     */
    public $grossAmount;

    /**
     * @var float
     */
    public $netAmount;

    /**
     * @var DateTime
     */
    public $dateSold;

    /**
     * @var string|null
     */
    public $pluginName;
}

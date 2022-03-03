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
    public int $id;

    /**
     * @var int
     */
    public int $saleId;

    /**
     * @var int
     */
    public int $pluginId;

    /**
     * @var string
     */
    public string $edition;

    /**
     * @var bool
     */
    public bool $renewal;

    /**
     * @var string
     */
    public string $email;

    /**
     * @var float
     */
    public float $grossAmount;

    /**
     * @var float
     */
    public float $netAmount;

    /**
     * @var DateTime
     */
    public DateTime $dateSold;

    /**
     * @var string|null
     */
    public ?string $pluginName;
}

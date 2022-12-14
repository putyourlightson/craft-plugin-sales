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
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var int|null
     */
    public ?int $saleId = null;

    /**
     * @var int|null
     */
    public ?int $pluginId = null;

    /**
     * @var string|null
     */
    public ?string $edition = null;

    /**
     * @var bool|null
     */
    public ?bool $renewal = null;

    /**
     * @var bool|null
     */
    public ?bool $first = null;

    /**
     * @var string|null
     */
    public ?string $email = null;

    /**
     * @var string|null
     */
    public ?string $notice = null;

    /**
     * @var string|float|null
     */
    public string|float|null $grossAmount = null;

    /**
     * @var string|float|null
     */
    public string|float|null $netAmount = null;

    /**
     * @var DateTime|null
     */
    public ?DateTime $dateSold = null;

    /**
     * @var string|null
     */
    public ?string $pluginName = null;
}

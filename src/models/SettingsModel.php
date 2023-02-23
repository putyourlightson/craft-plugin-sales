<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

class SettingsModel extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $organisationId;

    /**
     * @var string
     */
    public $currency = 'USD';

    /**
     * @var array
     */
    public $colourPalette = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#1b24ae', '#4dc6cb'];

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['email', 'password'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email']
        ];
    }
}

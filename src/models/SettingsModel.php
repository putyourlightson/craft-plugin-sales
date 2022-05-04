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
    public string $email = '';

    /**
     * @var string
     */
    public string $password = '';

    /**
     * @var string
     */
    public string $currency = 'USD';

    /**
     * @var array
     */
    public array $colourPalette = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#1b24ae', '#4dc6cb'];

    /**
     * @inheritdoc
     */
    protected function defineBehaviors(): array
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
    protected function defineRules(): array
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
        ];
    }
}

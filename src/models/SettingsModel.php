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
     * @var string|null
     */
    public ?string $email = null;

    /**
     * @var string|null
     */
    public ?string $password = null;

    /**
     * @var string|null
     */
    public ?string $organisationId = null;

    /**
     * @var string
     */
    public string $currency = 'USD';

    /**
     * @var int
     */
    public int $colours = 10;

    /**
     * @var array
     * https://tailwindcss.com/docs/customizing-colors
     */
    public array $colourPalette = ['3B82F6', '4ADE80', 'FBBF24', 'F43F5E', '7DD3FC', '059669', 'FB923C', 'D946EF', 'A16207', '94A3B8'];

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
        ];
    }
}

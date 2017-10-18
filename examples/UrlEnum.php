<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class UrlEnum extends EnumBehavior
{
    const WORK = 'work';
    const PERSONAL = 'personal';
    const FACEBOOK = 'facebook';
    const GOOGLEPLUS = 'googleplus';
    const TWITTER = 'twitter';
    const LINKEDIN = 'linkedin';
    const INSTAGRAM = 'instagram';
    const OTHER = 'other';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['type' => 'type_key'];

    /**
     * Values of Urls
     *
     * @return array
     */
    public static function values()
    {
        $values = parent::values();
        $values[static::WORK] = \Yii::t('app', $values[static::WORK]);
        $values[static::PERSONAL] = \Yii::t('app', $values[static::PERSONAL]);
        $values[static::GOOGLEPLUS] = 'GooglePlus';
        $values[static::LINKEDIN] = 'LinkedIn';
        $values[static::OTHER] = \Yii::t('app', $values[static::OTHER]);

        return $values;
    }
}
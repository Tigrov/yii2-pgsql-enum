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

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['type' => 'type_key'];

    /**
     * Values of Urls
     * @param bool $withEmpty with empty value at first
     * @return array
     */
    public static function values($withEmpty = false)
    {
        $values = parent::values($withEmpty);
        $values[static::WORK] = \Yii::t('app', $values[static::WORK]);
        $values[static::PERSONAL] = \Yii::t('app', $values[static::PERSONAL]);
        $values[static::GOOGLEPLUS] = 'GooglePlus';
        $values[static::LINKEDIN] = 'LinkedIn';

        return $values;
    }

    /**
     * @inheritdoc
     */
    public static function emptyValue()
    {
        return \Yii::t('app', 'URL');
    }
}
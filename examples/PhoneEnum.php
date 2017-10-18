<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class PhoneEnum extends EnumBehavior
{
    const WORK = 'work';
    const PERSONAL = 'personal';
    const MOBILE = 'mobile';
    const HOME = 'home';
    const FAX = 'fax';
    const SIP = 'sip';
    const OTHER = 'other';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['type' => 'type_key'];

    /** @var string a message category for translation the values */
    public static $messageCategory = 'app';

    /**
     * @inheritdoc
     */
    public static function values()
    {
        $values = parent::values();

        $values[static::SIP] = 'SIP';

        return $values;
    }
}
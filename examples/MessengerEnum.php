<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class MessengerEnum extends EnumBehavior
{
    const SKYPE = 'skype';
    const WHAPSAPP = 'whatsapp';
    const VIBER = 'viber';
    const FACEBOOK = 'facebook';
    const IMESSAGE = 'imessage';
    const TELEGRAM = 'telegram';
    const LINE = 'line';
    const JABBER = 'jabber';
    const QQ = 'qq';
    const BLACKBERRY = 'blackberry';
    const AIM = 'aim';
    const EBUDDY = 'ebuddy';
    const YAHOO = 'yahoo';
    const OTHER = 'other';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['type' => 'type_key'];

    /**
     * Values of Messengers
     * @param bool $addEmpty add empty value first
     * @return array
     */
    public static function values($addEmpty = false)
    {
        $values = parent::values($addEmpty);

        // Correct some display values
        $values[static::WHAPSAPP] = 'WhatsApp';
        $values[static::IMESSAGE] = 'iMessage';
        $values[static::QQ] = 'QQ';
        $values[static::BLACKBERRY] = 'BlackBerry';
        $values[static::AIM] = 'AIM';
        $values[static::EBUDDY] = 'eBuddy';
        $values[static::OTHER] = \Yii::t('app', $values[static::OTHER]);

        return $values;
    }
}
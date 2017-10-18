<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class AddressEnum extends EnumBehavior
{
    const BUSINESS = 'business';
    const MAILING = 'mailing';
    const WAREHOUSE = 'warehouse';
    const LEGAL = 'legal';
    const HOME = 'home';
    const OTHER = 'other';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['type' => 'type_key'];

    /** @var string a message category for translation the values */
    public static $messageCategory = 'app';
}
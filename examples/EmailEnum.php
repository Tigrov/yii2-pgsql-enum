<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class EmailEnum extends EnumBehavior
{
    const WORK = 'work';
    const PERSONAL = 'personal';
    const OTHER = 'other';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['type' => 'type_key'];

    /** @var string a message category for translation the values */
    public static $messageCategory = 'app';
}
<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class TimezoneEnum extends EnumBehavior
{
    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['timezone' => 'timezone_code'];
}
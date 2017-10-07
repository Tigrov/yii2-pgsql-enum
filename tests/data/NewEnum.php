<?php

namespace tigrov\tests\unit\pgsql\enum\data;

class NewEnum extends \tigrov\pgsql\enum\EnumBehavior
{
    /** @var array list of attributes that are to be automatically humanized value */
    public $attributes = ['type' => 'type_key'];
}
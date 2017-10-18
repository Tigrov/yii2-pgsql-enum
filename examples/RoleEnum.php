<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class RoleEnum extends EnumBehavior
{
    const ADMIN = 'admin';
    const MANAGER = 'manager';
    const CUSTOM = 'custom';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['role' => 'role_key'];

    /** @var string a message category for translation the values */
    public static $messageCategory = 'app';
}
<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class StatusEnum extends EnumBehavior
{
    const DELETED = 'deleted';
    const ACTIVE = 'active';
    const PENDING = 'pending';
    const INACTIVE = 'inactive';
    const DECLINED = 'declined';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['status' => 'status_key'];

    /** @var string a message category for translation the values */
    public static $messageCategory = 'app';
}
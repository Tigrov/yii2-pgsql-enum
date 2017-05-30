<?php

namespace tigrov\tests\unit\pgsql\enum\data;

class Status extends \tigrov\pgsql\enum\EnumBehavior
{
    const ACTIVE = 'active';
    const PENDING = 'pending';
    const REJECTED = 'rejected';
    const DELETED = 'deleted';

    public $attributes = ['status' => 'status_key'];
}
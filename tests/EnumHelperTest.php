<?php

namespace tigrov\tests\unit\pgsql\enum;

use tigrov\pgsql\enum\EnumHelper;

class EnumHelperTest extends TestCase
{
    const TEST_TYPE = 'test';

    public static $values = ['a', 'bob', 'pop_corn'];

    public static $addValues = ['add', 'value'];

    public static $addValuesBefore = ['more', 'Good values'];

    const TEST_BEFORE = 'pop_corn';

    public function testCreate()
    {
        EnumHelper::drop(static::TEST_TYPE);

        $this->assertFalse(EnumHelper::exists(static::TEST_TYPE));
        EnumHelper::create(static::TEST_TYPE, static::$values);
        $this->assertTrue(EnumHelper::exists(static::TEST_TYPE));
    }

    /**
     * @depends testCreate
     */
    public function testValues()
    {
        $this->assertSame(static::$values, EnumHelper::values(static::TEST_TYPE));
    }

    /**
     * @depends testValues
     */
    public function testAdd()
    {
        $values = static::$values;
        foreach (static::$addValues as $value) {
            EnumHelper::add(static::TEST_TYPE, $value);
            $values[] = $value;
            $this->assertSame($values, EnumHelper::values(static::TEST_TYPE));
        }

        // test add before
        foreach (static::$addValuesBefore as $value) {
            EnumHelper::add(static::TEST_TYPE, $value, static::TEST_BEFORE);
            array_splice($values, array_search(static::TEST_BEFORE, $values), 0, $value);
            $this->assertSame($values, EnumHelper::values(static::TEST_TYPE));
        }
    }

    /**
     * @depends testAdd
     */
    public function testDrop()
    {
        EnumHelper::drop(static::TEST_TYPE);
        $this->assertFalse(EnumHelper::exists(static::TEST_TYPE));
    }

    protected function arrayInsertBefore(array $array, $beforeKey, array $insertArray)
    {
        $keys = array_keys($array);
        $pos = array_search($beforeKey, $keys);

        return array_merge(array_slice($array, 0, $pos), $insertArray, array_slice($array, $pos));
    }
}
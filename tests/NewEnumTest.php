<?php

namespace tigrov\tests\unit\pgsql\enum;

use tigrov\pgsql\enum\EnumHelper;
use tigrov\tests\unit\pgsql\enum\data\NewEnum;
use yii\helpers\Inflector;

class NewEnumTest extends TestCase
{
    public static $values = ['a', 'bob', 'pop_corn'];
    public static $resultValues = ['a' => 'A', 'bob' => 'Bob', 'pop_corn' => 'Pop Corn'];

    public static $addValues = ['add', 'value'];

    public static $addValuesBefore = ['more', 'Good values'];

    const TEST_BEFORE = 'pop_corn';

    public function testTypeName()
    {
        $this->assertSame('new_enum', NewEnum::typeName());
    }

    public function testCreate()
    {
        NewEnum::drop();

        $this->assertFalse(NewEnum::exists());
        NewEnum::create(static::$values);
        $this->assertTrue(NewEnum::exists());
    }

    /**
     * @depends testCreate
     */
    public function testValues()
    {
        $this->assertSame(static::$resultValues, NewEnum::values());
    }

    /**
     * @depends testValues
     */
    public function testAdd()
    {
        $values = static::$resultValues;
        foreach (static::$addValues as $value) {
            NewEnum::add($value);
            $values[$value] = Inflector::humanize($value, true);
            $this->assertSame($values, NewEnum::values());
        }

        // test add before
        foreach (static::$addValuesBefore as $value) {
            NewEnum::add($value, static::TEST_BEFORE);
            $values = $this->arrayInsertBefore($values, static::TEST_BEFORE, [$value => Inflector::humanize($value, true)]);
            $this->assertSame($values, NewEnum::values());
        }
    }

    /**
     * @depends testAdd
     */
    public function testAddInTransaction()
    {
        $db = EnumHelper::getDb();
        $this->assertNull($db->getTransaction());
        $db->beginTransaction();
        $this->assertNotNull($db->getTransaction());

        NewEnum::add('new_value');

        $this->assertTrue(isset(NewEnum::values()['new_value']));
        $this->assertSame('New Value', NewEnum::values()['new_value']);
    }

    /**
     * @depends testAddInTransaction
     */
    public function testRemove()
    {
        NewEnum::remove(array_merge(static::$addValues, static::$addValuesBefore, ['new_value']));
        $this->assertSame(static::$resultValues, NewEnum::values());
    }

    /**
     * @depends testRemove
     */
    public function testDrop()
    {
        $this->assertTrue(NewEnum::exists());
        NewEnum::drop();
        $this->assertFalse(NewEnum::exists());
    }

    protected function arrayInsertBefore(array $array, $beforeKey, array $insertArray)
    {
        $keys = array_keys($array);
        $pos = array_search($beforeKey, $keys);

        return array_merge(array_slice($array, 0, $pos), $insertArray, array_slice($array, $pos));
    }
}
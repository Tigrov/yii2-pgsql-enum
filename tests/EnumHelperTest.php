<?php

namespace tigrov\tests\unit\pgsql\enum;

use tigrov\pgsql\enum\EnumHelper;
use tigrov\tests\unit\pgsql\enum\data\NewEnum;

class EnumHelperTest extends TestCase
{
    const TEST_TYPE = 'test';

    const TEST_RENAME_TYPE = 'new_test';

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
        EnumHelper::add(static::TEST_TYPE, static::$addValuesBefore, static::TEST_BEFORE);
        array_splice($values, array_search(static::TEST_BEFORE, $values), 0, static::$addValuesBefore);
        $this->assertSame($values, EnumHelper::values(static::TEST_TYPE));
    }

    /**
     * @depends testAdd
     */
    public function testRemove()
    {
        EnumHelper::remove(static::TEST_TYPE, array_merge(static::$addValues, static::$addValuesBefore));
        $this->assertSame(static::$values, EnumHelper::values(static::TEST_TYPE));

        // With table
        NewEnum::create(['untouched', 'replaced', 'removed', 'not used']);
        parent::createEnumtypesTable();

        $db = \Yii::$app->getDb();
        $db->createCommand()
            ->batchInsert(static::TABLE_NAME, ['type_key'], [['untouched'], ['replaced'], ['removed']])
            ->execute();

        $column = $db->getSchema()->getTableSchema(static::TABLE_NAME)->getColumn('type_key');
        $this->assertSame(['untouched', 'replaced', 'removed', 'not used'], $column->enumValues);

        EnumHelper::remove(NewEnum::typeName(), 'not used');
        $column = $db->getSchema()->getTableSchema(static::TABLE_NAME)->getColumn('type_key');
        $this->assertSame(['untouched', 'replaced', 'removed'], $column->enumValues);

        EnumHelper::remove(NewEnum::typeName(), ['removed', 'replaced' => 'new value'], true);
        $column = $db->getSchema()->getTableSchema(static::TABLE_NAME)->getColumn('type_key');
        $this->assertSame(['untouched', 'new value'], $column->enumValues);

        $values = $db->createCommand('SELECT type_key FROM ' . static::TABLE_NAME)->queryColumn();
        $this->assertSame(['untouched', 'new value', null], $values);

        parent::dropDatatypesTable();
        NewEnum::drop();
    }

    /**
     * @depends testRemove
     */
    public function testRename()
    {
        $this->assertTrue(EnumHelper::exists(static::TEST_TYPE));

        EnumHelper::rename(static::TEST_TYPE, static::TEST_RENAME_TYPE);

        $this->assertTrue(EnumHelper::exists(static::TEST_RENAME_TYPE));
        $this->assertFalse(EnumHelper::exists(static::TEST_TYPE));

        EnumHelper::rename(static::TEST_RENAME_TYPE, static::TEST_TYPE);

        $this->assertFalse(EnumHelper::exists(static::TEST_RENAME_TYPE));
        $this->assertTrue(EnumHelper::exists(static::TEST_TYPE));
    }

    /**
     * @depends testRename
     */
    public function testRecreate()
    {
        EnumHelper::recreate(static::TEST_TYPE, ['value1', 'value2']);
        $this->assertSame(['value1', 'value2'], EnumHelper::values(static::TEST_TYPE));

        // With table
        NewEnum::create(['string', 'more values', 'one more']);
        parent::createEnumtypesTable();

        EnumHelper::recreate(NewEnum::typeName(), ['value1', 'value2']);
        $this->assertSame(['value1', 'value2'], EnumHelper::values(NewEnum::typeName()));

        $db = \Yii::$app->getDb();
        $column = $db->getSchema()->getTableSchema(static::TABLE_NAME)->getColumn('type_key');
        $this->assertSame(['value1', 'value2'], $column->enumValues);

        parent::dropDatatypesTable();
        NewEnum::drop();
    }

    /**
     * @depends testRecreate
     */
    public function testRenameValue()
    {
        EnumHelper::renameValue(static::TEST_TYPE, 'value2', 'renamed value');
        $this->assertSame(['value1', 'renamed value'], EnumHelper::values(static::TEST_TYPE));
    }

    /**
     * @depends testRecreate
     */
    public function testDrop()
    {
        EnumHelper::drop(static::TEST_TYPE);
        $this->assertFalse(EnumHelper::exists(static::TEST_TYPE));
    }

    public function testColumns()
    {
        NewEnum::create(['string', 'more values', 'one more']);
        parent::createEnumtypesTable();

        $this->assertSame([['table_name' => static::TABLE_NAME, 'column_name' => 'type_key']], EnumHelper::columns(NewEnum::typeName()));

        parent::dropDatatypesTable();
        NewEnum::drop();
    }

    protected function arrayInsertBefore(array $array, $beforeKey, array $insertArray)
    {
        $keys = array_keys($array);
        $pos = array_search($beforeKey, $keys);

        return array_merge(array_slice($array, 0, $pos), $insertArray, array_slice($array, $pos));
    }
}
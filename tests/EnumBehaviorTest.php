<?php

namespace tigrov\tests\unit\pgsql\enum;

use tigrov\tests\unit\pgsql\enum\data\Enumtypes;
use tigrov\tests\unit\pgsql\enum\data\NewEnum;
use tigrov\tests\unit\pgsql\enum\data\Status;

class EnumBehaviorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        NewEnum::create(['string', 'more values', 'one more']);
        $this->createEnumtypesTable();
    }

    protected function tearDown()
    {
        $this->dropDatatypesTable();

        NewEnum::drop();

        parent::tearDown();
    }

    public function testHasProperty()
    {
        $model = new Enumtypes;
        $this->assertTrue($model->hasProperty('type'));
        $this->assertTrue($model->hasProperty('value'));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testEnumType($value, $expected)
    {
        $model = new Enumtypes;
        $model->type_key = $value;
        $this->assertTrue($model->save(false));

        $newModel = Enumtypes::findOne($model->id);
        $this->assertNotNull($newModel);
        $this->assertSame($value, $newModel->type_key);
        $this->assertSame($expected, $newModel->type);
        $this->assertSame($expected, $newModel->value);
    }

    public function testArray()
    {
        $model = new Enumtypes;
        $model->type_key = ['string', 'one more'];
        $this->assertSame(['string' => 'String', 'one more' => 'One More'], $model->type);
    }

    public function testConstants()
    {
        $this->assertSame([
            'ACTIVE' => 'active',
            'PENDING' => 'pending',
            'REJECTED' => 'rejected',
            'DELETED' => 'deleted',
        ], Status::constants());
    }

    public function testCreate()
    {
        $this->assertFalse(Status::exists());
        Status::create();
        $this->assertTrue(Status::exists());
        $this->assertSame([
            Status::ACTIVE => 'Active',
            Status::PENDING => 'Pending',
            Status::REJECTED => 'Rejected',
            Status::DELETED => 'Deleted',
        ], Status::values());

        Status::drop();
    }

    public function valuesProvider()
    {
        return [
            ['string', 'String'],
            ['more values', 'More Values']
        ];
    }
}
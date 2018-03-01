<?php

namespace tigrov\tests\unit\pgsql\enum\data;

use yii\db\ActiveRecord;

class Enumtypes extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'type' => NewEnum::class,
            'value' => [
                'class' => NewEnum::class,
                'attributes' => ['value' => 'type_key'],
            ],
        ];
    }
}
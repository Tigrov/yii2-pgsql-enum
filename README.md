yii2-pgsql-enum
==============

Enum type behavior and helper for Yii2, for PostgreSQL only.

Using enum types provides good readability of data in DataBase without spending extra resources on the storage of duplicate values (in cases with any string types).

Such types are more difficult to maintain in a project. This extension helps to simplify the use of enum types. 

[![Latest Stable Version](https://poser.pugx.org/Tigrov/yii2-pgsql-enum/v/stable)](https://packagist.org/packages/Tigrov/yii2-pgsql-enum)
[![Build Status](https://travis-ci.org/Tigrov/yii2-pgsql-enum.svg?branch=master)](https://travis-ci.org/Tigrov/yii2-pgsql-enum)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist tigrov/yii2-pgsql-enum
```

or add

```
"tigrov/yii2-pgsql-enum": "~1.0"
```

to the require section of your `composer.json` file.

	
Usage
-----

Once the extension is installed, you can create new enum types as follow:

```php
class NewEnum extends \tigrov\pgsql\enum\EnumBehavior
{
    /** @var array list of attributes that are to be automatically humanized value */
    public $attributes = ['type' => 'type_key'];
}
```

Create a table with the enum type
```php
// Create the enum in DB or in PHP code 
NewEnum::create(['value1', 'value2', 'value3']);

\Yii::$app->getDb()->createCommand()
    ->createTable('model', [
       'id' => 'pk',
       'type_key' => NewEnum::typeName(), // 'new_enum'
   ])->execute();
```

Create a model for the table
```php
class Model extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            NewEnum::className(),
            // 'type' => [
            //    'class' => NewEnum::className(),
            //    'attributes' => ['type' => 'type_key'],
            //],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        // You can add new values if they are not exists in the enum type
        try {
            return parent::save($runValidation, $attributeNames);
        } catch (\yii\db\Exception $e) {
            NewEnum::add($this->type_key);
            return parent::save($runValidation, $attributeNames);
        }
    }
}
```

and then use them in your code
```php
/**
 * @var ActiveRecord $model
 */
$model = new Model;
$model->type_key = 'value1';
$model->save();

$newModel = Model::findOne($model->id);
$newModel->type_key === 'value1';
$newModel->type === 'Value1'; // see yii\helpers\Inflector::humanize($word, true)

// The extension will try to add new value if it does not exist
$model->type_key = 'non-existent value';
$model->save();

// To get all enum values
NewEnum::values();

// To add new value to the enum type
NewEnum::add('new value');

// To check if the enum type exists
NewEnum::exists();

// To drop the enum type
NewEnum::drop();
```

Examples
--------

Gender codes:
```php
class GenderCode extends \tigrov\pgsql\enum\EnumBehavior
{
    /**
     * @var array list of attributes that are to be automatically humanized value.
     * humanized => original attribute
     */
    public $attributes = ['gender' => 'gender_code'];

    /**
     * Values of genders
     * @return array
     */
    public static function values()
    {
        return [
            'M' => \Yii::t('enum', 'Male'),
            'F' => \Yii::t('enum', 'Female'),
        ];
    }
}

class Model extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            GenderCode::className(),
        ];
    }
}

$model->gender_code = 'M';
$model->gender === 'Male'; // or translated value
```

Messenger names:
```php
class MessengerType extends \tigrov\pgsql\enum\EnumBehavior
{
    /** @var array list of attributes that are to be automatically humanized value */
    public $attributes = ['type' => 'type_key'];
    
    /**
     * Values of Messengers
     * @return array
     */
    public static function values()
    {
        return [
            'skype' => 'Skype',
            'whatsapp' => 'WhatsApp',
            'viber' => 'Viber',
            'facebook' => 'Facebook',
            'imessage' => 'iMessage',
            'telegram' => 'Telegram',
            'line' => 'Line',
            'jabber' => 'Jabber',
            'qq' => 'QQ',
            'blackberry' => 'BlackBerry',
            'aim' => 'AIM',
            'ebuddy' => 'eBuddy',
            'yahoo' => 'Yahoo',
            'other' => \Yii::t('enum', 'Other'),
        ];
    }
}

$model->type_key = 'whatsapp';
$model->type === 'WhatsApp';
```

Translate values:
```php
class TranslatableType extends \tigrov\pgsql\enum\EnumBehavior
{
    public static $messageCategory = 'app';
}

// $model->type is translated value
// TranslatableType::values() are all translated values
```

Type values as constants:
```php
class Status extends \tigrov\pgsql\enum\EnumBehavior
{
    const ACTIVE = 'active';
    const PENDING = 'pending';
    const REJECTED = 'rejected';
    const DELETED = 'deleted';

    public $attributes = ['status' => 'status_key'];
    public static $messageCategory = 'status';
}

// Do not forget to initialize the enum type in the database
// E.g. Status::create();

$model->status_key = Status::PENDING;
$model->status === 'Pending'; // or translated value
```

License
-------

[MIT](LICENSE)

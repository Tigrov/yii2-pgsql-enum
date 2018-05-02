<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql-enum
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql\enum;

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Parent class for an enum type behavior. Allows to manage the enum type and get humanized value of the enum type.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
abstract class EnumBehavior extends \tigrov\enum\EnumBehavior
{
    /**
     * Get name of the enum type
     *
     * @return string name of the enum type
     */
    public static function typeName()
    {
        return Inflector::camel2id(StringHelper::basename(static::class), '_');
    }

    /**
     * Create the enum type
     *
     * @param string[]|null $values values of the enum type, it will try to use class constants if null
     * @return int number of rows affected by the execution.
     */
    public static function create($values = null) {
        if ($values === null) {
            $values = array_values(static::constants());
        }

        return EnumHelper::create(static::typeName(), $values);
    }

    /**
     * Add new value to the enum type
     *
     * @param string $value new value for adding
     * @param string|null $before the value will be placed before
     * @return int number of rows affected by the execution.
     */
    public static function add($value, $before = null)
    {
        return EnumHelper::add(static::typeName(), $value, $before);
    }

    /**
     * Get values of the enum type
     *
     * @return array values of the enum type
     */
    public static function values() {
        $list = [];

        $values = EnumHelper::values(static::typeName());
        foreach ($values as $key) {
            $value = Inflector::humanize($key, true);
            $list[$key] = static::t($value);
        }

        return $list;
    }

    /**
     * Check if the enum type exists
     *
     * @return bool true if exists
     */
    public static function exists()
    {
        return EnumHelper::exists(static::typeName());
    }

    /**
     * Remove values from the enum type
     *
     * @param array $values values for removing
     * It is a list of values to be removed e.g. ['value1', 'value2']
     * the removed values will be replaced with null values.
     * Or a list of key => value pairs if you need to replace the removing values with new values
     * where keys are the removing values and values are the new values
     * e.g. ['removing value 1' => 'new value 1', 'removing value 2' => 'new value 2']
     * In this case the removing values will be changed to the new values.
     * If the new values are not exist in the enum type they will be added.
     * @param bool $updateTables indicates if required to replace the enum values in all tables with the enum type
     * before removing.
     * @throws \Exception
     */
    public static function remove($values, $updateTables = false)
    {
        return EnumHelper::remove(static::typeName(), $values, $updateTables);
    }

    /**
     * Drop the enum type
     *
     * @param bool $cascade if true, automatically drop objects that depend on the type (such as table columns, functions, operators).
     * @return int number of rows affected by the execution.
     */
    public static function drop($cascade = true)
    {
        return EnumHelper::drop(static::typeName(), $cascade);
    }
}
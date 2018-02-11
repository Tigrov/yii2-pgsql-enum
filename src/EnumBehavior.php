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
        return Inflector::camel2id(StringHelper::basename(static::className()), '_');
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
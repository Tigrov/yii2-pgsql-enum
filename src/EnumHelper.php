<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql-enum
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql\enum;

/**
 * Helper to manage a enum type
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class EnumHelper
{
    /**
     * Get the current DB connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return \Yii::$app->getDb();
    }

    /**
     * Create an enum type
     *
     * @param string $typeName name of the enum type
     * @param string[] $values values of the enum type
     * @return int number of rows affected by the execution.
     */
    public static function create($typeName, $values)
    {
        $db = static::getDb();
        $quotedValues = array_map([$db, 'quoteValue'], $values);

        return static::getDb()->createCommand(
            'CREATE TYPE ' . $db->quoteColumnName($typeName)
            . ' AS ENUM (' . implode(',', $quotedValues) . ')'
        )->execute();
    }

    /**
     * Add a value to an enum type
     *
     * @param string $typeName name of the enum type
     * @param string $value new value for adding
     * @param string|null $before the value will be placed before
     * @return int number of rows affected by the execution.
     */
    public static function add($typeName, $value, $before = null)
    {
        $db = static::getDb();
        if ($db->getTransaction()) {
            // It cannot add a value during a transaction.
            // Start new connection without transactions.
            $db = static::newConnection();
        }

        return $db->createCommand(
            'ALTER TYPE ' . $db->quoteColumnName($typeName)
            . ' ADD VALUE IF NOT EXISTS ' . $db->quoteValue($value)
            . ($before !== null ? ' BEFORE ' . $db->quoteValue($before) : '')
        )->execute();
    }

    /**
     * Get values of an enum type
     *
     * @param string $typeName name of the enum type
     * @return array values of the enum type
     */
    public static function values($typeName)
    {
        $db = static::getDb();

        return $db->createCommand(
            'SELECT unnest(enum_range(NULL::' . $db->quoteColumnName($typeName) . '))'
        )->queryColumn();
    }

    /**
     * Check if an enum type exists
     *
     * @param string $typeName name of the enum type
     * @return bool true if exists
     */
    public static function exists($typeName)
    {
        $db = static::getDb();

        return $db->createCommand(
            'SELECT EXISTS ('
            . 'SELECT 1 '
                . 'FROM ' . $db->quoteTableName('pg_type')
                . ' WHERE typname = ' . $db->quoteValue($typeName)
            . ')'
        )->queryScalar();
    }

    /**
     * Drop an enum type
     *
     * @param string $typeName name of the enum type
     * @param bool $cascade if true, automatically drop objects that depend on the type (such as table columns, functions, operators).
     * @return int number of rows affected by the execution.
     */
    public static function drop($typeName, $cascade = true)
    {
        $db = static::getDb();

        return $db->createCommand(
            'DROP TYPE IF EXISTS ' . $db->quoteColumnName($typeName)
            . ($cascade ? ' CASCADE' : '')
        )->execute();
    }

    /**
     * Create new DB connection like the current connection
     *
     * @return \yii\db\Connection
     */
    protected static function newConnection()
    {
        static $newDb;

        if ($newDb === null) {
            $db = static::getDb();
            $className = get_class($db);
            $newDb = new $className;

            $class = new \ReflectionClass($db);
            foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic()) {
                    $name = $property->getName();
                    $newDb->$name = $db->$name;
                }
            }

            $newDb->pdo = null;
        }

        return $newDb;
    }
}
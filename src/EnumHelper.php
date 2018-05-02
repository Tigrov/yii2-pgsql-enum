<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql-enum
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql\enum;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

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
     * @param string|string[] $values new value or values for adding
     * @param string|null $before the value will be placed before
     */
    public static function add($typeName, $values, $before = null)
    {
        $values = (array) $values;

        $db = static::getDb();
        if ($db->getTransaction()) {
            // It cannot add a value during a transaction.
            // Start new connection without transactions.
            $db = static::newConnection();
        }

        $query = 'ALTER TYPE ' . $db->quoteColumnName($typeName)
            . ' ADD VALUE IF NOT EXISTS ';

        $beforeQuery = $before !== null
            ? ' BEFORE ' . $db->quoteValue($before)
            : '';

        foreach ($values as $value) {
            $db->createCommand($query . $db->quoteValue($value) . $beforeQuery)->execute();
        }
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
     * Remove values from an enum type
     *
     * @param string $typeName name of the enum type
     * @param string|array $values values for removing
     * It is a value (e.g. 'value1') or list of values (e.g. ['value1', 'value2']) to be removed,
     * the removed values will be replaced with null values.
     * Or a list of key => value pairs if you need to replace the removing values with new values
     * where keys are the removing values and values are the new values
     * e.g. ['removing value 1' => 'new value 1', 'removing value 2' => 'new value 2']
     * In this case the removing values will be changed to the new values.
     * @param bool $updateTables indicates if required to replace the enum values in all tables with the enum type
     * before removing.
     * @throws \Exception
     */
    public static function remove($typeName, $values, $updateTables = false)
    {
        $values = (array) $values;

        $db = static::getDb();
        $transaction = $db->beginTransaction();
        try {
            $allValues = static::values($typeName);
            $removedValues = [];

            $isAssociative = ArrayHelper::isAssociative($values, false);
            if ($isAssociative) {
                $caseQuery = '';
                $placeholders = [];
                $phIndex = 0;
                foreach ($values as $k => $v) {
                    $ph1 = 'case' . $phIndex++;
                    $ph2 = 'case' . $phIndex++;
                    if (is_int($k)) {
                        $removedValues[] = $v;
                    } else {
                        $removedValues[] = $k;
                        $placeholders[$ph1] = $k;
                        $placeholders[$ph2] = $v;
                        if (!in_array($v, $allValues)) {
                            static::add($typeName, $v);
                            $allValues[] = $v;
                        }
                        $caseQuery .= ' WHEN :' . $ph1 . ' THEN :' . $ph2;
                    }
                }

                $caseQuery .= ' ELSE NULL END::' . $db->quoteColumnName($typeName);
            } else {
                $removedValues = $values;
            }

            if ($updateTables) {
                $columns = static::columns($typeName);
                foreach ($columns as $column) {
                    $columnName = $column['column_name'];
                    $value = $isAssociative
                        ? new Expression('CASE ' . $db->quoteColumnName($columnName) . $caseQuery, $placeholders)
                        : null;
                    $db->createCommand()
                        ->update($column['table_name'], [$columnName => $value], [$columnName => $removedValues])
                        ->execute();
                }
            }

            static::recreate($typeName, array_diff($allValues, $removedValues));

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Rename an enum type
     *
     * @param string $oldTypeName old name of the enum type
     * @param string $newTypeName new name of the enum type
     * @return int number of rows affected by the execution.
     */
    public static function rename($oldTypeName, $newTypeName)
    {
        $db = static::getDb();

        return $db->createCommand(
            'ALTER TYPE ' . $db->quoteColumnName($oldTypeName)
            . ' RENAME TO ' . $db->quoteColumnName($newTypeName)
        )->execute();
    }

    /**
     * Try to recreate an enum type with new values
     *
     * @param string $typeName name of the enum type
     * @param string[] $values new values of the enum type
     * @throws \Exception
     */
    public static function recreate($typeName, $values)
    {
        $db = static::getDb();
        $quotedType = $db->quoteColumnName($typeName);

        $transaction = $db->beginTransaction();
        try {
            $columns = static::columns($typeName);

            do {
                $tmpTypeName = md5(rand());
            } while (static::exists($tmpTypeName));

            static::rename($typeName, $tmpTypeName);
            static::create($typeName, $values);

            $schema = $db->getSchema();
            foreach ($columns as $column) {
                $quotedColumn = $db->quoteColumnName($column['column_name']);
                $db->createCommand(
                    'ALTER TABLE ' . $db->quoteTableName($column['table_name'])
                    . ' ALTER ' . $quotedColumn
                    . ' TYPE ' . $quotedType
                    . ' USING ' . $quotedColumn . '::text::' . $quotedType
                )->execute();
                $schema->refreshTableSchema($column['table_name']);
            }

            static::drop($tmpTypeName);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Drop an enum type
     *
     * @param string $typeName name of the enum type
     * @param bool $cascade if true, automatically drop objects that depend on the type (such as table columns, functions, operators).
     * @return int number of rows affected by the execution.
     */
    public static function drop($typeName, $cascade = false)
    {
        $db = static::getDb();

        return $db->createCommand(
            'DROP TYPE IF EXISTS ' . $db->quoteColumnName($typeName)
            . ($cascade ? ' CASCADE' : '')
        )->execute();
    }

    /**
     * Returns columns with an enum type
     *
     * @param string $typeName name of the enum type
     * @return array
     */
    public static function columns($typeName)
    {
        $db = static::getDb();

        return $db->createCommand(
            'SELECT c.relname AS table_name, a.attname AS column_name'
            . ' FROM pg_type t'
            . ' INNER JOIN pg_attribute a ON a.atttypid = t.oid'
            . ' INNER JOIN pg_class c ON c.oid = a.attrelid'
            . ' WHERE t.typname = :typeName',
            ['typeName' => $typeName]
        )->queryAll();
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
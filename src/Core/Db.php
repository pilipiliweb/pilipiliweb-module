<?php

namespace PilipiliWeb\PwCore\Core;

use Db as PsDb;
use DbQuery;
use PrestaShopDatabaseException;

final class Db
{
    /**
     * @var PsDb
     */
    protected static $db;

    public static function delete($table, $where = null): bool
    {
        return self::getDb()->delete($table, is_array($where) ? self::where($where) : $where);
    }

    public static function getDb(): PsDb
    {
        if (self::$db == false) {
            self::$db = PsDb::getInstance();
        }

        return self::$db;
    }

    /**
     * @param string|DbQuery $query
     *
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getResults($query)
    {
        return self::getDb()->executeS($query);
    }

    public static function getRow($query)
    {
        return self::getDb()->getRow($query);
    }

    public static function getValue($query)
    {
        return self::getDb()->getValue($query);
    }

    /**
     * @param string $table
     * @param array $data
     * @param int $type
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public static function insert($table, array $data, $type = PsDb::INSERT)
    {
        return self::getDb()->insert($table, $data, false, true, $type);
    }

    public static function query($table = null, $alias = null)
    {
        $query = new DbQuery();

        if ($table) {
            $query->from($table, $alias);
        }

        return $query;
    }

    public static function update($table, array $data, $where = null)
    {
        return self::getDb()->update($table, $data, is_array($where) ? self::where($where) : $where);
    }

    /**
     * @param array $criteria
     *
     * @return string
     */
    public static function where(array $criteria)
    {
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $where = '`' . bqSQL($key) . '` ';
            if (null === $value) {
                $where .= 'IS NULL';
            } elseif (is_string($value)) {
                $where .= '= \'' . pSQL($value) . '\'';
            } elseif (is_bool($value)) {
                $where .= '= ' . (int) $value;
            } else {
                $where .= '= ' . pSQL($value);
            }

            $conditions[] = $where;
        }

        return '(' . implode(') AND (', $conditions) . ')';
    }
}

<?php

namespace PilipiliWeb\PwCore\Core;

final class Schema
{
    public static function create($name, array $columns, array $primary = [])
    {
        $sql = [];
        foreach ($columns as $columnName => $type) {
            $sql[] = sprintf('`%1$s` %2$s', $columnName, $type);
        }

        if ($primary) {
            $sql[] = self::primary($primary);
        }

        return Db::getDb()->execute(
            sprintf(
                'CREATE TABLE IF NOT EXISTS `%1$s` (%2$s) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
                _DB_PREFIX_ . $name,
                implode(', ', $sql)
            )
        );
    }

    public static function drop($name)
    {
        return Db::getDb()->execute(
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $name . '`'
        );
    }

    protected static function primary(array $columns)
    {
        return 'PRIMARY KEY (`' . implode('`, `', $columns) . '`)';
    }
}

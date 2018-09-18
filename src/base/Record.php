<?php

namespace MiniFastORM;

class Record
{
    private $name;
    private $columns = [];
    private $types = [
        'INTEGER', 'INT', 'SMALLINT', 'TINYINT', 'MEDIUMINT', 'BIGINT',
        'DECIMAL', 'NUMERIC',
        'FLOAT', 'DOUBLE',
        'DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR',
        'CHAR', 'VARCHAR', 'BINARY', 'VARBINARY', 'BLOB', 'TEXT', 'ENUM', 'SET'
    ];
    private $attributes = [
        'PRIMARY'
        'AUTOINCREMENT',
        'DEFAULT',
        'NOTNULL'
    ];

    public function setTableName(string $name)
    {
        $this->name = $name;
    }

    public function hasColumn(string $name, string $type, $size, array $attributes = [])
    {
        if(!empty($name) and !empty($type) and in_array(strtoupper($type), $this->types;)) {
            $continue = true;
            $concerned = '';
            foreach ($attributes as $key => $value) {
                if (!in_array($key, $this->attributes)) {
                    $continue = false;
                    $concerned = $key;
                    break;
                }
            }

            if ($continue) {
                $this->columns[$name] = [
                    'type' => $type,
                    'size' => $size,
                    'attributes' => $attributes
                ];
            } else {
                throw new Exception("Unknow attribute $concerned.");
            }
        } else {
            throw new Exception('Column name and type cannot be empty.');
        }
    }

    public function getTable()
    {
        return [
            'name' => $this->name,
            'columns' => $this->columns
        ];
    }
}
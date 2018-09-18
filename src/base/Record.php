<?php

namespace MiniFastORM;

class Record
{
    private $name;
    private $columns = [];
    private $foreigns = [];
    private $types = [
        'INTEGER', 'INT', 'SMALLINT', 'TINYINT', 'MEDIUMINT', 'BIGINT',
        'DECIMAL', 'NUMERIC',
        'FLOAT', 'DOUBLE',
        'DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR',
        'CHAR', 'VARCHAR', 'BINARY', 'VARBINARY', 'BLOB', 'TEXT', 'ENUM', 'SET'
    ];
    private $attributes = [
        'PRIMARYKEY',
        'AUTOINCREMENT',
        'DEFAULT',
        'REQUIRED'
    ];

    public function setTableName(string $name)
    {
        $this->name = $name;
    }

    public function hasColumn(string $name, string $type, $size, array $attributes = [])
    {
        if(!empty($name) and !empty($type) and in_array(strtoupper($type), $this->types)) {
            $continue = true;
            $concerned = '';
            foreach ($attributes as $key => $value) {
                if (!in_array(strtoupper($key), $this->attributes)) {
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
                throw new \Exception("Unknow attribute $concerned.");
            }
        } else {
            throw new \Exception('Column name and type cannot be empty.');
        }
        return $this;
    }
    
    public function hasOne($class, array $attributes)
    {
        if (isset($attributes['local']) and isset($attributes['foreign'])) {
            $attributes['relation'] = 'hasOne';
            $this->foreigns[$class] = $attributes;
        }
    }
    
    public function hasMany($class, array $attributes)
    {
        if (isset($attributes['local']) and isset($attributes['foreign'])) {
            $attributes['relation'] = 'hasMany';
            $this->foreigns[$class] = $attributes;
        }
    }

    public function getTable()
    {
        return [
            'name' => $this->name,
            'columns' => $this->columns,
            'foreigns' => $this->foreigns
        ];
    }
}
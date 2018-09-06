<?php

class Database
{
    private $db;
    private $sql;
    private $dbName;
    private $tables = [];
    private $foreigns = [];
    private $supportedAttr = [
        'name' => 'string',
        'type'=> 'string',
        'size' => 'string',
        'default' => 'boolean',
        'required' => 'boolean',
        'primaryKey' => 'boolean',
        'autoIncrement' => 'boolean'
    ];
    private $supportedTypes = [
        'int',
        'varchar',
        'text',
        'boolean',
        'date',
        'datetime'
    ];
    
    public function __construct(string $file)
    {
        $this->db = $this->fileToArray($file);
        $this->checkDatabase($this->db);
    }
    
    private function fileToArray($file)
    {
        // Getting content of schema
        $xmlString = file_get_contents($file);
        $xml = new SimpleXMLElement($xmlString);

        // Convert XML into PHP array
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return $array;
    }
    
    private function checkDatabase(array $array)
    {
        // Check the database name
        if(isset($array['@attributes']['name']))
        {
            $this->dbName = $array['@attributes']['name'];
            
            // Check tables
            if(isset($array['table']))
            {
                if(isset($array['table']['@attributes']) or isset($array['table']['column']))
                {
                    $this->checkTable($array['table'], 0);
                }
                else
                {
                    foreach($array['table'] as $tableKey => $table)
                    {
                        $this->checkTable($table, $tableKey);
                    }
                }
            }
            else
            {
                die("$databaseName has no table\n");
            }
        }
        else
        {
            die("Database has no name\n");
        }
        
        return true;
    }
    
    private function checkTable(array $table, $key)
    {
        // Check the table name
        if(isset($table['@attributes']['name']))
        {
            $tableName = $table['@attributes']['name'];
            $this->tables[$tableName] = [];
            $this->foreigns[$tableName] = [];
            
            // Check columns
            if(isset($table['column']))
            {
                if(isset($table['column']['@attributes']))
                {
                    $this->checkColumn($table['column'], $tableName, 0);
                }
                else
                {
                    foreach($table['column'] as $columnKey => $col)
                    {
                        $this->checkColumn($col, $tableName, $columnKey);
                    }
                }
            }
            else
            {
                die("Table $tableName has no column\n");
            }

            // Check foreign keys
            if(isset($table['foreign-key']))
            {
                if(isset($table['foreign-key']['@attributes']) or isset($table['foreign-key']['reference']))
                {
                    $this->checkForeign($table['foreign-key'], $tableName, 0);
                }
                else
                {
                    foreach($table['foreign-key'] as $foreignKey => $foreign)
                    {
                        $this->checkForeign($foreign, $tableName, 0);
                    }
                }
            }
        }
        else
        {
            die("Table $key has no name\n");
        }
    }
    
    private function checkColumn(array $col, string $tableName, int $key)
    {
        $colName = '';
        // Check the column name
        if(isset($col['@attributes']['name']))
        {
            $colName = $col['@attributes']['name'];
        }
        else
        {
            die("Column $key name from $tableName is missing\n");
        }
        
        // Check the column type
        if(isset($col['@attributes']['type']))
        {
            $colName = $col['@attributes']['type'];
        }
        else
        {
            die("'$colName' column type from $tableName is missing\n");
        }
        
        $this->tables[$tableName][] = $col['@attributes'];
    }
    
    private function checkForeign(array $foreign, string $tableName, int $key)
    {
        // Check the foreign table
        if(isset($foreign['@attributes']['foreign-table']))
        {
            $foreignTable = $foreign['@attributes']['foreign-table'];
        }
        else
        {
            die("The foreign key $key from $tableName has no foreign-table attribute\n");
        }

        // Check the reference
        if(isset($foreign['reference']))
        {
            $attributes = [
                'local',
                'foreign'
            ];

            foreach($attributes as $attr)
            {
                if(!isset($foreign['reference']['@attributes'][$attr]))
                {
                    die("'$attr' attribute from foreign key $key from $tableName is missing\n");
                }
            }
        }
        
        $this->foreigns[$tableName][$key] = array_merge($foreign['@attributes'], $foreign['reference']['@attributes']);
    }
    
    public function createSQL()
    {
        $this->sql = '';
        $this->sql .= "CREATE DATABASE IF NOT EXISTS `$this->dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE `$this->dbName`;\n\n";
        
        foreach($this->tables as $tableName => $table)
        {
            $this->sql .= "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
            $i = 0;
            foreach($table as $col)
            {
                foreach($this->supportedAttr as $key => $expected)
                {
                    $$key = '';
                    if(isset($col[$key]))
                    {
                        if($key == 'type')
                        {
                            if(in_array($col[$key], $this->supportedTypes))
                            {
                                $$key = strtoupper($col[$key]);
                            }
                            else
                            {
                                die("Unsuported type $col[$key]\n");
                            }
                        }
                        else
                        {
                            if($expected == 'boolean')
                            {
                                $$key = json_decode($col[$key]);
                            }
                            else
                            {
                                $$key = $col[$key];
                            }
                        }
                    }
                }
                
                $this->sql .= ($i > 0 ? ",\n    ":"    ");
                $this->sql .= "`$name`";
                $this->sql .= " $type";
                $this->sql .= (!empty($size) ? " ($size)":'');
                $this->sql .= ($primaryKey ? ' PRIMARY KEY' : '') . ($autoIncrement ? ' AUTO_INCREMENT' : '') . ($required ? ' NOT NULL' : '') . (strlen($default) != 0 ? " DEFAULT `$default`": '');
                $i++;
            }
            $this->sql .= "\n) ENGINE=InnoDB;\n\n";
        }
        
        // TODO onDelete and onRestrict values
        foreach($this->foreigns as $tableName => $table)
        {
            $i = 0;
            foreach($table as $fk)
            {
                $this->sql .= ($i > 0 ? "\n" : '') . 'ALTER TABLE `' . $tableName . '` ADD CONSTRAINT `FK_' . ucfirst($fk['foreign-table']) . ucfirst($fk['foreign']) . ucfirst($tableName) . '` FOREIGN KEY (`' . $fk['local'] . '`) REFERENCES `' . $fk['foreign-table'] . '`(`' . $fk['foreign'] . '`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
                $i++;
            }
        }
    }
    
    public function createClass()
    {
        // TODO create classes from array
    }
    
    public function writeFile(string $fileName, string $content)
    {
        $file = fopen(__DIR__ . '/' . $fileName, 'a+');
        file_put_contents(__DIR__ . '/' . $fileName, '');
        fwrite($file, $content);
    }
    
    public function getSQL()
    {
        return $this->sql;
    }
    
    public function show($var)
    {
        var_dump($this->$var);
    }
}
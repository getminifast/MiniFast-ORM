<?php
$supportedAttr = [
    'name',
    'type',
    'size',
    'default',
    'required',
    'primaryKey',
    'autoIncrement'
];

function dbToArray($xml)
{
    global $supportedAttr;
    $db = [];
    $database = new SimpleXMLElement($xml);
    $databaseName = (string) $database->attributes()->name;
    $db['database'] = $databaseName;
    foreach($database->table as $table)
    {
        if(isset($table->attributes()->name) and isset($table->column))
        {
            $tableName = (string)$table->attributes()->name;
            $db['tables'][$tableName] = [];
            foreach($table->column as $column)
            {
                $columnName = (string) $column->attributes()->name;
                if(sizeof($column->attributes()) > 1)
                {
                    foreach($column->attributes() as $key => $attr)
                    {
                        if(in_array($key, $supportedAttr))
                        {
                            switch($key)
                            {
                                case 'size':
                                    $attr = intval($attr);
                                    break;
                                    
                                case 'primaryKey':
                                    $attr = (($attr == 'true' or $attr == true or $attr = "1") ? true : false);
                                    break;
                                    
                                case 'autoIncrement':
                                    $attr = (($attr == 'true' or $attr == true or $attr = "1") ? true : false);
                                    break;
                                    
                                default:
                                    break;
                            }
                            
                            $db['tables'][$tableName]['columns'][$columnName][$key] = (string)$attr;
                        }
                        else
                        {
                            die("Unsupported attribute \"$key\" in table `$tableName`\n");
                        }
                    }
                }
                else
                {
                    die('A column of ' . $tableName . ' table hasn\'t enough attributes.' . "\n");
                }
            }
            
            foreach($table->{'foreign-key'} as $key => $fk)
            {
                $db['tables'][$tableName]['foreign'][] = [
                    'foreign-table' => (string) $fk->attributes()->{'foreign-table'},
                    'reference' => [
                        'local-table' => (string) $fk->reference->attributes()['local-table'],
                        'local' => (string) $fk->reference->attributes()['local'],
                        'foreign' => (string) $fk->reference->attributes()['foreign']
                    ]
                ];
            }
        }
        else
        {
            die("An error occured: a table has no name or no column\n");
        }
    }
    return $db;
}

function arrayToSQL($database)
{
    global $supportedAttr;
    $sql = '';
//    var_dump($database);
    $sql .= "CREATE DATABASE IF NOT EXISTS `" . $database['database'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE `" . $database['database'] . "`;\n\n";
    foreach($database['tables'] as $key => $table)
    {
        $tableName = $key;
        foreach($table as $key => $column)
        {
            if(sizeof($column) > 1 or $key === 'foreign')
            {
                if($key !== 'foreign')
                {
                    $sql .= "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
                    
                    $name = $type = $size = $default = $required = $primaryKey = $autoIncrement = '';
                    
                    $i = 0;
                    foreach($column as $key => $attr)
                    {
                        $name = $type = $size = $default = $required = $primaryKey = $autoIncrement = '';
                        if(isset($attr['name']))
                        {
                            $name = (string) $attr['name'];
                        }
                        if(isset($attr['type']))
                        {
                            $type = (string) $attr['type'];
                        }
                        if(isset($attr['size']))
                        {
                            $size = (int) $attr['size'];
                        }
                        if(isset($attr['default']))
                        {
                            $default = (boolean) $attr['default'];
                        }
                        if(isset($attr['required']))
                        {
                            $required = (boolean) $attr['required'];
                        }
                        if(isset($attr['primaryKey']))
                        {
                            $primaryKey = (boolean) $attr['primaryKey'];
                        }
                        if(isset($attr['autoIncrement']))
                        {
                            $autoIncrement = (boolean) $attr['autoIncrement'];
                        }

                        $sql .= ($i > 0 ? ",\n" : '') . "\t";
                        $sql .= '`' . $name . '` ' . $type;
                        if(!empty($size))
                        {
                            $sql .= '(' . $size . ')';
                        }
                        else
                        {
                            switch(strtolower($type))
                            {
                                case 'int':
                                    $sql .= '(11)';
                                    break;
                                    
                                case 'varchar':
                                    $sql .= '(40)';
                                    
                                default:
                                    '';
                                    break;
                            }
                        }
                        $sql .= ($primaryKey ? ' PRIMARY KEY' : '') . ($autoIncrement ? ' AUTO_INCREMENT' : '') . ($required ? ' NOT NULL' : '') . (!empty($default) ? 'DEFAULT `'.$default.'`' : '');
                        
                        $i++;
                    }
                    $sql .= "\n) ENGINE=InnoDB;\n\n";
                }
                else
                {
                    $i = 0;
                    foreach($column as $key => $foreign)
                    {
                        $sql .= ($i > 0 ? "\n" : '') . 'ALTER TABLE `' . $foreign['reference']['local-table'] . '` ADD CONSTRAINT `FK_' . ucfirst($foreign['foreign-table']) . ucfirst($foreign['reference']['foreign']) . '` FOREIGN KEY (`' . $foreign['reference']['local'] . '`) REFERENCES `' . $foreign['foreign-table'] . '`(`' . $foreign['reference']['foreign'] . '`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
                        $i++;
                    }
                }
            }
            else
            {
                die("An error occured\n");
            }
        }
    }
    $sql .= "\n";
    return $sql;
}

function mkdirR($path)
{
    $path = explode('/', $path);

    $current = '';
    foreach($path as $dir)
    {
        $current .= (!empty($current) ? '/':'') . $dir;
        if(!file_exists($current))
        {
            mkdir($current);
        }
    }
}

function formatName(string $name)
{
    $newName = explode('_', $name);
    $names = [];
    foreach($newName as $Name)
    {
        $names[] = ucfirst(strtolower($Name));
    }
    
    return implode($names);
}

function arrayToClass($database)
{
    mkdirR('orm');
    $base = fopen(__DIR__ . '/orm/Base.php', 'a+');
    file_put_contents(__DIR__ . '/orm/Base.php', '');
    fwrite($base, file_get_contents(__DIR__ . '/test/Base.php'));
    fclose($base);

    $baseQuery = fopen(__DIR__ . '/orm/BaseQuery.php', 'a+');
    file_put_contents(__DIR__ . '/orm/BaseQuery.php', '');
    fwrite($baseQuery, file_get_contents(__DIR__ . '/test/BaseQuery.php'));
    fclose($baseQuery);
    
    foreach($database['tables'] as $key => $table)
    {
        $tableName = formatName($key);
        $path = 'orm/' . $tableName . '.php';
        $pathQuery = 'orm/' . $tableName . 'Query.php';
        $file = fopen($path, 'a+');
        $fileQuery = fopen($pathQuery, 'a+');
        file_put_contents($path, '');
        file_put_contents($pathQuery, '');
        $beginFile = "<?php

class $tableName extends Base
{
    private \$table = '$key';
    ";
        $beginFileQuery = "<?php

class $tableName\Query extends BaseQuery
{
    public function __construct(\$table = '$key')
    {
        parent::__construct(\$table);
    }
    ";
        fwrite($file, $beginFile);
        
        foreach($table as $key => $column)
        {
            if(sizeof($column) > 1 or $key === 'foreign')
            {
                if($key !== 'foreign')
                {
                    foreach($column as $key => $attr)
                    {
                        fwrite($file, "private \$$key = [
        'name' => '$key',
        'type' => '" . $attr['type'] . "',
        'required' => " . (isset($attr['required']) ? (string)$attr['required'] : 'false') . "
    ];
    ");
                    }
                    
                    fwrite($file, "\n\t");
                    fwrite($file, "public function __construct()
    {
        parent::__construct(\$this->table);
    }
    ");
                    fwrite($file, "\n\t");
                    
                    foreach($column as $key => $attr)
                    {
                        $colName = formatName($key);
                        fwrite($file, "public function set$colName(\$value)
    {
        parent::set('$key', \$value);
    }
    ");
                    }
                    fwrite($file, "\n}");
                }
            }
        }
    }
}

if(sizeof($argv) > 2)
{
    if($argv[1] === 'init')
    {
        $xmlPath = $argv[2];
        if(file_exists($xmlPath))
        {
            $xml = file_get_contents($xmlPath);
            $array = dbToArray($xml);
            $sql = arrayToSQL($array);
            if(!empty($sql))
            {
                $file = fopen('database.sql', 'a+');
                file_put_contents('database.sql', '');
                fwrite($file, $sql);
                fclose($file);
                
                arrayToClass($array);
            }
        }
    }
}
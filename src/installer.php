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

function printUsage()
{
    echo "Usage: installer.php <init|update> <xml_file>\n";
    echo " - init\t\tWill create the database script and classes from the xml file.\n";
    echo " - update\tWill create a script to update your database et create/update your classes.\n";
}

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
                        'local-table' => (string) $tableName,
                        'local' => (string) $fk->reference->attributes()['local'],
                        'foreign' => (string) $fk->reference->attributes()['foreign']
                    ]
                ];
                $db['tables'][$tableName]['columns'][(string)$fk->reference->attributes()['local']]['foreign'] = [
                    'table' => (string) $fk->attributes()->{'foreign-table'},
                    'col' => (string) $fk->reference->attributes()['foreign']
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
                            $type = (string) strtoupper($attr['type']);
                        }
                        if(isset($attr['size']))
                        {
                            $size = (int) $attr['size'];
                        }
                        if(isset($attr['default']))
                        {
                            if($type === 'INT')
                            {
                                $default = intval($attr['default']);
                            }
                            elseif($type == 'BOOLEAN')
                            {
                                $default = intval(json_decode($attr['default']));
                            }
                            else
                            {
                                $default = '\'' . strval($attr['default']) . '\'';
                            }
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
                        $sql .= ($primaryKey ? ' PRIMARY KEY' : '') . ($autoIncrement ? ' AUTO_INCREMENT' : '') . ($required ? ' NOT NULL' : '') . (strlen($default) != 0 ? ' DEFAULT '.$default : '');

                        $i++;
                    }
                    $sql .= "\n) ENGINE=InnoDB;\n\n";
                }
                else
                {
                    $i = 0;
                    foreach($column as $key => $foreign)
                    {
                        $sql .= ($i > 0 ? "\n" : '') . 'ALTER TABLE `' . $foreign['reference']['local-table'] . '` ADD CONSTRAINT `FK_' . ucfirst($foreign['foreign-table']) . ucfirst($foreign['reference']['foreign']) . ucfirst($foreign['reference']['local-table']) . '` FOREIGN KEY (`' . $foreign['reference']['local'] . '`) REFERENCES `' . $foreign['foreign-table'] . '`(`' . $foreign['reference']['foreign'] . '`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
                        $i++;
                    }
                    $sql .= "\n\n";
                }
            }
            else
            {
                die("An error occured\n");
            }
        }
    }
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
    $basepath = dirname(__FILE__);
    $dbName = $database['database'];
    $autoload = fopen($basepath . '/autoload.php', 'a+');
    file_put_contents($basepath . '/autoload.php', '');
    fwrite($autoload, "<?php\n");

    @mkdirR($basepath . '/minifast');
    $base = fopen($basepath . '/minifast/Base.php', 'a+');
    file_put_contents($basepath . '/minifast/Base.php', '');
    fwrite($base, str_replace('__DB_NAME__', $dbName, file_get_contents($basepath . '/class/Base.php')));
    fclose($base);
    fwrite($autoload, "include_once dirname(__FILE__).'/minifast/Base.php';\n");

    $baseQuery = fopen($basepath . '/minifast/BaseQuery.php', 'a+');
    file_put_contents($basepath . '/minifast/BaseQuery.php', '');
    fwrite($baseQuery, str_replace('__DB_NAME__', $dbName, file_get_contents($basepath . '/class/BaseQuery.php')));
    fwrite($autoload, "include_once dirname(__FILE__).'/minifast/BaseQuery.php';\n");

    foreach($database['tables'] as $key => $table)
    {
        $tableName = formatName($key);
        $path = $basepath . '/minifast/' . $tableName . '.php';
        $pathQuery = $basepath . '/minifast/' . $tableName . 'Query.php';
        $file = fopen($path, 'a+');
        $fileQuery = fopen($pathQuery, 'a+');
        file_put_contents($path, '');
        file_put_contents($pathQuery, '');

        fwrite($autoload, "include_once dirname(__FILE__).'/minifast/$tableName.php';\n");
        fwrite($autoload, "include_once dirname(__FILE__).'/minifast/$tableName" . "Query.php';\n");

        // Writting start of files
        $beginFile = file_get_contents($basepath . '/class/Child-Start.php');
        $beginFileQuery = file_get_contents($basepath . '/class/ChildQuery-Start.php');
        $beginFile = str_replace('__TABLE_FORMATED_NAME__', $tableName, $beginFile);
        $beginFileQuery = str_replace('__TABLE_FORMATED_NAME__', $tableName, $beginFileQuery);
        fwrite($file, $beginFile);
        fwrite($fileQuery, $beginFileQuery);

        // Writting constructors
        $constructFile = file_get_contents($basepath . '/class/Child-Construct.php');
        $constructFileQuery = file_get_contents($basepath . '/class/ChildQuery-Construct.php');
        $constructFile = str_replace('__TABLE_NAME__', $key, $constructFile);
        $constructFile = str_replace('__TABLE_FORMATED_NAME__', $tableName, $constructFile);
        $constructFileQuery = str_replace('__TABLE_NAME__', $key, $constructFileQuery);
        $constructFileQuery = str_replace('__TABLE_FORMATED_NAME__', $tableName, $constructFileQuery);
        fwrite($file, $constructFile);
        fwrite($fileQuery, $constructFileQuery);

        foreach($table as $key => $column)
        {
            if(sizeof($column) > 1 or $key === 'foreign')
            {
                if($key !== 'foreign')
                {
                    $i = 0;
                    $nbColumns = sizeof($column);
                    fwrite($file, "\tprivate \$vars = [\n");
                    foreach($column as $key => $attr)
                    {
                        $colName = formatName($key);
                        
                        // File
                        $varsFile = file_get_contents($basepath . '/class/Child-Vars.php');
                        $varsFile = str_replace('__COLUMN_NAME__', $key, $varsFile);
                        if(isset($attr['foreign']))
                        {
                            $str = "['table' => '" . formatName($attr['foreign']['table']) . "', 'col' => '" . $attr['foreign']['col'] . "']";
                            $varsFile = str_replace('__IS_FOREIGN__', $str, $varsFile);
                        }
                        else
                        {
                            $varsFile = str_replace('__IS_FOREIGN__', 'false', $varsFile);
                        }
                        $varsFile = str_replace('__COLUMN_TYPE__', $attr['type'], $varsFile);
                        $varsFile = str_replace('__IS_REQUIRED__', (isset($attr['required']) ? ($attr['required'] ? 'true' : 'false') : 'false'), $varsFile);
                        $varsFile = str_replace('__IS_PRIMARY__', (isset($attr['primaryKey']) ? ($attr['primaryKey'] ? 'true' : 'false') : 'false'), $varsFile);

                        fwrite($file, $varsFile . (($i < $nbColumns - 1) ? ',':'') . "\n");

                        // FileQuery
                        $methodsQuery = file_get_contents($basepath . '/class/ChildQuery-Methods.php');
                        $methodsQuery = str_replace('__COLUMN_FORMATED_NAME__', $colName, $methodsQuery);
                        $methodsQuery = str_replace('__COLUMN_NAME__', $key, $methodsQuery);
                        fwrite($fileQuery, $methodsQuery . "\n");
                        $i++;
                    }
                    fwrite($file, "\t];\n");

                    foreach($column as $key => $attr)
                    {
                        $colName = formatName($key);
                        $methodsFile = file_get_contents($basepath . '/class/Child-Methods.php');
                        $methodsFile = str_replace('__COLUMN_FORMATED_NAME__', $colName, $methodsFile);
                        $methodsFile = str_replace('__COLUMN_NAME__', $key, $methodsFile);
                        fwrite($file, $methodsFile . "\n");
                    }
                    fwrite($file, "}\n");
                    fwrite($fileQuery, "}\n");
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
    else
    {
        printUsage();
    }
}
else
{
    printUsage();
}
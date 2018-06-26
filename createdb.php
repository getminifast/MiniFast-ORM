<?php

$schema = __DIR__ . '/schema.xml';
$sql = '';
$alter = [];

if(file_exists($schema))
{
    $xml = simplexml_load_file($schema);
    $json = json_encode($xml);
    $database = json_decode($json, true);
    
    if(sizeof($database) > 1)
    {
        foreach($database as $key => $table)
        {
            // Checking if database has a name
            if($key === '@attributes')
            {
                if(isset($table['name']))
                {
                    $name = $table['name'];
                    $sql .= "CREATE DATABASE IF NOT EXISTS $name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
                }
                // TODO die la base de données n'a pas de nom
            }
            elseif($key === 'table')
            {
                // For each table
                foreach($table as $k => $t)
                {
                    // array(2){ ["@attributes", "column"] }
                    foreach($t as $k => $ta)
                    {
                        if($k === '@attributes')
                        {
                            if(isset($ta['name']))
                            {
                                $tableName = $ta['name'];
                                $sql .= "CREATE TABLE IF NOT EXISTS `$tableName` ";
                            }
                            else
                            {
                                die("The table has no name \n");
                            }
                        }
                        elseif($k === 'column')
                        {
                            // For each column
                            $sql .= "(\n";
                            $i = 0;
                            foreach($ta as $col => $attr)
                            {
                                $attr = $attr['@attributes'];
                                if(isset($attr['name']))
                                {
                                    if(!empty($attr['name']))
                                    {
                                        // TODO créer la classe [name].php et [name]Query.php
                                        // Creating name
                                        $name = '`' . $attr['name'] . '`';

                                        // Creating type
                                        $type = (isset($attr['type']) ? (!empty($attr['type']) ? strtoupper($attr['type']) : 'INT') : 'INT');

                                        // Creating size
                                        $size = '';
                                        if(isset($attr['size']))
                                        {
                                            if(empty($attr['size']) and $type === 'INT')
                                            {
                                                $size = 11;
                                            }
                                            else
                                            {
                                                $size = intval($attr['size']);
                                            }
                                        }
                                        else
                                        {
                                            if($type === 'INT')
                                            {
                                                $size = 11;
                                            }
                                        }

                                        // Creating primary key
                                        $pk = (isset($attr['primaryKey']) ? (!empty($attr['primaryKey']) ? (strtolower($attr['primaryKey']) === 'true' ? true : false) : false) : false);

                                        // Creating auto increment
                                        $ai = (isset($attr['autoIncrement']) ? (!empty($attr['autoIncrement']) ? (strtolower($attr['autoIncrement']) === 'true' ? true : false) : false) : false);

                                        // Creating not null field
                                        $required = (isset($attr['required']) ? (!empty($attr['required']) ? (strtolower($attr['required']) === 'true' ? true : false) : false) : false);

                                        // Adding column to SQL script
                                        $sql .= ($i > 0 ? ",\n" : '') . "\t" . $name . ' ' . $type . (!empty($size) ? '(' . $size . ')' : '') . ($pk ? ' PRIMARY KEY' : '') . ($ai ? ' AUTO_INCREMENT' : '') . ($required ? ' NOT NULL' : '');
                                    }
                                    else
                                    {
                                        die("The column name cannot be empty. \n");
                                    }
                                }
                                else
                                {
                                    die("You need to provide a column name for each column.\n");
                                }
                                $i++;
                            }
                            $sql .= "\n) ENGINE=InnoDB;\n\n";
                        }
                        // Pour les clés étrangères
                        elseif($k === 'foreign-key')
                        {
                            $alter[] = [
                                'table' => $tableName,
                                'column' => $ta['reference']['@attributes']['local'],
                                'foreignTable' => $ta['@attributes']['foreign-table'],
                                'foreignColumn' => $ta['reference']['@attributes']['foreign']
                            ];
                        }
                    }
                }
            }
        }
        
        $i = 0;
        foreach($alter as $foreign)
        {
            $sql .= ($i > 0 ? "\n" : '') . 'ALERT TABLE `' . $foreign['table'] . '` ADD CONSTRAINT `FK_' . ucfirst($foreign['column']) . ucfirst($foreign['table']) . '` FOREIGN KEY (`' . $foreign['column'] . '`) REFERENCES `' . $foreign['foreignTable'] . '`(`' . $foreign['foreignColumn'] . '`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
            $i++;
        }
        $sql .= "\n";
        
        // Creating sql file with database creation script
        $path = __DIR__ . '/database.sql';
        $file = fopen($path, 'a+');
        file_put_contents($path, '');
        fwrite($file, $sql);
        fclose($file);
        var_dump($database);
    }
    else
    {
        echo "There is an error loading the schema.xml\n";
    }
}

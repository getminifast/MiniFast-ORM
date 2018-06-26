<?php

if(sizeof($argv) > 1)
{
    $config = [];
    $bdd;
    $columnTypes = [
        'int',
        'tinyint',
        'smallint',
        'decimal',
        'float',
        'varchar',
        'text',
        'date'
    ];
    
    if($argv[1] == 'init')
    {
        echo "You will now be asked for some questions to initialize MiniFast\n";
        $config['schemaPath'] = readline('Where should the schema.xml be saved? > ');
        $config['classPath'] = readline('Where should the MiniFast ORM be installed? >');
        $database = [];
        do
        {
            $continue = readline('Would you like to create a new database? [yes/no] >');
            var_dump($continue);
        } while($continue != 'yes' and $continue != 'no');
        
        if($continue === 'yes')
        {
            $config['host'] = readline('What is the databasa located? [localhost/127.0.0.1] >');
            $config['dbname'] = readline('What is the database name? >');
            $config['user'] = readline('What is the user name? >');
            $config['password'] = readline('What is the '. $config['user'] . 'password? >');
            
            try
            {
                $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
                $bdd = new PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'].'', $config['user'], $config['password'], $pdo_options);
            }
            catch (Exception $e){
                die('Error: ' . $e->getMessage()."\n");
            }
            
            $continue = false;
            
            do
            {
                echo "\n";
                $table = readline('Choose a table name > ');
                $table = trim($table);
                if(!empty($table) and !in_array($table, $database))
                {
                    $continueCols = false;
                    do
                    {
                        $col = readline('Choose a column name > ');
                        if(!isset($database[$table][$col]))
                        {
                            $database[$table][$col] = [];
                            $type = readline('Choose the `' . $col . '` column type > ');
                            $type = strtolower($type);
                            if(in_array($type, $columnTypes))
                            {
                                // TODO
                            }
                            else
                            {
                                echo "MiniFast is not compatible with $type type";
                            }
                        }
                    } while(!$continueCols);
                }
                else
                {
                    echo "Table already exists\n";
                }
            } while($continue)
        }
    }
}
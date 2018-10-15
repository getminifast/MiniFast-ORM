<?php

require_once __DIR__ . '/vendor/autoload.php';

use \MiniFastORM\Core\Database;

function printUsage()
{
    echo "Usage: init.php <xml_file>" . PHP_EOL;
}

if(sizeof($argv) > 1)
{
    if(file_exists($argv[1]))
    {
        $database = new Database($argv[1]);
        $database->createSQL();
        $database->createClasses();
    }
}
else
{
    printUsage();
}
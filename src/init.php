<?php

require_once __DIR__ . '/vendor/autoload.php';

use \MiniFastORM\Core\Database;

function printUsage()
{
    echo "Usage: installer.php <init|update> <xml_file>\n";
    echo " - init\t\tWill create the database script and classes from the xml file.\n";
    echo " - update\tWill create a script to update your database et create/update your classes.\n";
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
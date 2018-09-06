<?php
include dirname(__FILE__) . '/Database.php';
function printUsage()
{
    echo "Usage: installer.php <init|update> <xml_file>\n";
    echo " - init\t\tWill create the database script and classes from the xml file.\n";
    echo " - update\tWill create a script to update your database et create/update your classes.\n";
}

if(sizeof($argv) > 2)
{
    if($argv[1] === 'init')
    {
        if(file_exists($argv[2]))
        {
            $database = new Database($argv[2]);
            $database->createSQL();
            $database->writeFile('database.sql', $database->getSQL());
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
<?php

namespace MiniFastORM\Core;

use \Nette\PhpGenerator\PhpLiteral;
use \Symfony\Component\Filesystem\Filesystem;

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
    
    /**
     * Convert XML file into a PHP array
     * @param  string $file Path to the file we need
     * @return array  A PHP array we can work with
     */
    private function fileToArray($file)
    {
        // Getting content of schema
        $xmlString = file_get_contents($file);
        $xml = new \SimpleXMLElement($xmlString);

        // Convert XML into PHP array
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return $array;
    }
    
    /**
     * Check if the database is good
     * @param  array   $array The database as an array
     * @return boolean True if database is ok
     */
    private function checkDatabase(array $array)
    {
        // Check the database name
        if (isset($array['@attributes']['name'])) {
            $this->dbName = $array['@attributes']['name'];
            
            // Check tables
            if (isset($array['table'])) {
                if (isset($array['table']['@attributes']) or isset($array['table']['column'])) {
                    $this->checkTable($array['table'], 0);
                } else {
                    foreach ($array['table'] as $tableKey => $table) {
                        $this->checkTable($table, $tableKey);
                    }
                }
            } else {
                die("$databaseName has no table\n");
            }
        } else {
            die("Database has no name\n");
        }
        
        return true;
    }
    
    /**
     * Check the table integrity
     * @param array $table A table as an array
     * @param int   $key   The index of the table in case table name is missing
     */
    private function checkTable(array $table, $key)
    {
        // Check the table name
        if (isset($table['@attributes']['name'])) {
            $tableName = $table['@attributes']['name'];
            $this->tables[$tableName] = [];
            $this->foreigns[$tableName] = [];
            
            // Check columns
            if (isset($table['column'])) {
                if (isset($table['column']['@attributes'])) {
                    $this->checkColumn($table['column'], $tableName, 0);
                } else {
                    foreach ($table['column'] as $columnKey => $col) {
                        $this->checkColumn($col, $tableName, $columnKey);
                    }
                }
            } else {
                die("Table $tableName has no column\n");
            }

            // Check foreign keys
            if (isset($table['foreign-key'])) {
                if (isset($table['foreign-key']['@attributes']) or isset($table['foreign-key']['reference'])) {
                    $this->checkForeign($table['foreign-key'], $tableName, 0);
                } else {
                    foreach ($table['foreign-key'] as $foreignKey => $foreign) {
                        $this->checkForeign($foreign, $tableName, 0);
                    }
                }
            }
        } else {
            die("Table $key has no name\n");
        }
    }
    
    /**
     * Check the column integrity
     * @param array  $col       The column to check as an array
     * @param string $tableName The table name in case of error
     * @param int    $key       The column index in case column name is missing
     */
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
        if (isset($col['@attributes']['type'])) {
            $colType = $col['@attributes']['type'];
        } else {
            die("'$colName' column type from $tableName is missing\n");
        }
        
        $this->tables[$tableName][$colName] = $col['@attributes'];
    }
    
    /**
     * Check foreign key integrity
     * @param array  $foreign   Foreign key as an array
     * @param string $tableName The table name in case of error
     * @param int    $key       The foreign key index in case forein attribtue is missing
     */
    private function checkForeign(array $foreign, string $tableName, int $key)
    {
        // Check the foreign table
        if (isset($foreign['@attributes']['foreign-table'])) {
            $foreignTable = $foreign['@attributes']['foreign-table'];
        } else {
            die("The foreign key $key from $tableName has no foreign-table attribute\n");
        }

        // Check the reference
        if (isset($foreign['reference'])) {
            $attributes = [
                'local',
                'foreign'
            ];

            foreach ($attributes as $attr) {
                if (!isset($foreign['reference']['@attributes'][$attr])) {
                    die("'$attr' attribute from foreign key $key from $tableName is missing\n");
                }
            }
        }
        
        $this->foreigns[$tableName][$key] = array_merge($foreign['@attributes'], $foreign['reference']['@attributes']);
        $this->tables[$tableName][$this->foreigns[$tableName][$key]['local']]['foreign'] = $key;
    }
    
    /**
     * Create an SQL database creation script from a PHP array
     */
    public function createSQL()
    {
        $this->sql = '';
        $this->sql .= "CREATE DATABASE IF NOT EXISTS `$this->dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE `$this->dbName`;\n\n";
        
        foreach ($this->tables as $tableName => $table) {
            $this->sql .= "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
            $i = 0;
            foreach ($table as $col) {
                foreach ($this->supportedAttr as $key => $expected) {
                    $$key = '';
                    if (isset($col[$key])) {
                        if ($key == 'type') {
                            if (in_array($col[$key], $this->supportedTypes)) {
                                $$key = strtoupper($col[$key]);
                            } else {
                                die("Unsuported type $col[$key]\n");
                            }
                        } else {
                            if ($expected == 'boolean') {
                                $$key = json_decode($col[$key]);
                            } else {
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
        foreach ($this->foreigns as $tableName => $table) {
            $i = 0;
            foreach ($table as $fk) {
                $this->sql .= ($i > 0 ? "\n" : '') . 'ALTER TABLE `' . $tableName . '` ADD CONSTRAINT `FK_' . ucfirst($fk['foreign-table']) . ucfirst($fk['foreign']) . ucfirst($tableName) . '` FOREIGN KEY (`' . $fk['local'] . '`) REFERENCES `' . $fk['foreign-table'] . '`(`' . $fk['foreign'] . '`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
                $i++;
            }
        }
    }
    
    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * @param    string   $str                     String in underscore format
     * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
     * @return   string                            $str translated into camel caps
     */
    protected function toCamelCase($str, $capitalise_first_char = false) {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }
    
    /**
     * Create all classes based on database
     */
    public function createClasses()
    {
        $basepath = dirname(__FILE__);
        $filesystem = new Filesystem();
        $filesystem->mkdir($basepath . '/MiniFastORM');
        $printer = new \Nette\PhpGenerator\PsrPrinter;
        
        // For each table
        foreach ($this->tables as $key => $table) {
            // Create files
            $file = new \Nette\PhpGenerator\PhpFile;
            $fileQuery = new \Nette\PhpGenerator\PhpFile;
            
            // Add namespace
            $namespace = $file->addNamespace('MiniFastORM');
            $namespaceQuery = $fileQuery->addNamespace('MiniFastORM');
            
            // Add classes
            $class = $namespace->addClass($this->toCamelCase($key, true))
                ->setExtends('\MiniFastORM\Core\Base');
            $methodConstruct = $class->addMethod('__construct')
                ->addBody('parent::__construct(?);', [$key]);
            $methodGetColumns = $class->addMethod('getColumns')
                ->addBody('return $this->vars;')
                ->addComment('Fetch all columns from this class')
                ->addComment('@return array All columns of this table');
            
            $classQuery = $namespaceQuery->addClass($this->toCamelCase($key, true) . 'Query')
                ->setExtends('\MiniFastORM\Core\BaseQuery');
            $methodQueryConstruct = $classQuery->addMethod('__construct')
                ->addBody('parent::__construct(?);', [$key])
                ->addBody('parent::setBase($this->base);');
            $methodQueryCreate = $classQuery->addMethod('create')
                ->setStatic()
                ->setVisibility('protected')
                ->addComment('Create a new instance of *' . $this->toCamelCase($key, true) . 'Query*')
                ->addComment('@param  string $table The table name')
                ->addComment('@return ' . $this->toCamelCase($key, true) . 'Query')
                ->addBody('return new ?Query($table);', [new PhpLiteral($this->toCamelCase($key, true))])
                ->addParameter('table', $key)
                ->setTypeHint('string');
            $methodQueryGetColumns = $classQuery->addMethod('getColumns')
                ->addBody('$class = $this->base;')
                ->addBody('$base = new $class();')
                ->addBody('return $base->getColumns();')
                ->addComment('Fetch all columns from the base class')
                ->addComment('@return array All columns of this table');
            
            // Gathering information about vars
            if (!empty($this->foreigns[$key])) {
                foreach ($this->foreigns[$key] as $foreign) {
                    $table[$foreign['local']]['foreign'] = $foreign;
                }
            }
            
            // For each column
            foreach ($table as $column) {
                // Add methods for base
                $methodSet = $class->addMethod('set' . $this->toCamelCase($column['name'], true))
                    ->addBody('parent::set(?, $value);', [$column['name']])
                    ->addBody('return $this;')
                    ->addComment('Set `' . $column['name'] . '` in order to insert or update')
                    ->addComment('@param $value The value to set')
                    ->addParameter('value');
                
                // Add methods for baseQuery
                $methodQuerySet = $classQuery->addMethod('set' . $this->toCamelCase($column['name'], true))
                    ->addBody('parent::set(?, $value);', [$column['name']])
                    ->addBody('return $this')
                    ->addComment('Set `' . $column['name'] . '` in order to query')
                    ->addComment('@param $value The value to set')
                    ->addComment('@return ' . $this->toCamelCase($key, true) . 'Query The same instance')
                    ->addParameter('value');
                $methodQueryFindBy = $classQuery->addMethod('findBy' . $this->toCamelCase($column['name'], true))
                    ->addBody('parent::findBy(?, $value);', [$column['name']])
                    ->addBody('return $this;')
                    ->addComment('Set ' . $column['name'] . ' in where clause')
                    ->addComment('@param $value The value to set')
                    ->addComment('@return ' . $this->toCamelCase($key, true) . 'Query The same instance')
                    ->addParameter('value');
                $methodQueryFilterBy = $classQuery->addMethod('filterBy' . $this->toCamelCase($column['name'], true))
                    ->addBody('parent::filterBy(?, $value, $criteria);', [$column['name']])
                    ->addBody('return $this;')
                    ->addComment('Set ' . $column['name'] . ' in where clause')
                    ->addComment('@param $value The value to set')
                    ->addComment('@return ' . $this->toCamelCase($key, true) . 'Query The same instance');
                $methodQueryFilterBy->addParameter('value');
                $methodQueryFilterBy->addParameter('criteria', new PhpLiteral('parent::EQUALS'));
                $methodQueryCount = $classQuery->addMethod('count')
                    ->addBody('parent::count(?, $name);', [$column['name']])
                    ->addBody('return $this')
                    ->addComment('Count ' . $column['name'] . ' occurences')
                    ->addComment('@param');
            }
            
            // Add properties
            $class->addProperty('tableName', $key)
                ->setVisibility('protected');
            $class->addProperty('vars', $table)
                ->setVisibility('protected');
            
            $classQuery->addProperty('base', $this->toCamelCase($key, true))
                ->setVisibility('protected');

            $filesystem->dumpFile($basepath . '/MiniFastORM/' . $this->toCamelCase($key, true) . '.php', $printer->printFile($file));
            $filesystem->dumpFile($basepath . '/MiniFastORM/' . $this->toCamelCase($key, true) . 'Query.php', $printer->printFile($fileQuery));
        }
    }
    
    /**
     * Return SQL script
     * @return string The SQL
     */
    public function getSQL()
    {
        return $this->sql;
    }
    
    /**
     * var_dump a variable for debug
     * @param string $var The output
     */
    public function show($var)
    {
        var_dump($this->$var);
    }
}
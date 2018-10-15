<?php

namespace MiniFastORM\Core;

class Base
{
    protected $container = new Container();
    private $cols = [];
    private $values = [];
    private $table;
    private $co;

    const INSERT = 'INSERT INTO';
    const VALUES = 'VALUES';

    public function __construct(string $table)
    {
        $this->co = $this->container->getConnection();

        if (!empty($table)) {
            $this->table = $table;
        } else {
            throw new \Exception('Used table is missing.' . PHP_EOL);
        }
    }

    /*
     * Set a value to its column
     * @param string $col
     * @param string $value
     */
    public function set(string $col, $value)
    {
        $this->cols[] = $col;
        $this->values[$col] = $value;
    }

    /*
     * Insert data into the database
     */
    public function save()
    {

        // If data has been set
        if (!empty($this->table) and !empty($this->cols) and !empty($this->values)) {
            $query = self::INSERT . ' ' . $this->table . '(' . implode(', ', $this->cols) . ') ' . self::VALUES . '(:' . implode(', :', $this->cols) . ')';
            $req = $this->co->prepare($query);
            $req->execute($this->values);
        }
        // Else, error
        else {
            throw new \Exception('You cannot save before inserting data.');
        }
    }
    
    public static function now()
    {
        return date('DD-MM-AAAA hh:mm:ss');
    }
}

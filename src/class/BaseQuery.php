<?php

class BaseQuery
{
    private $co;
    private $table;
    private $cols = [];
    private $values = [];
    private $filters = [];
    private $filterValues = [];
    private $limit;
    private $offset;
    private $criteria;

    const MIN = '>=';
    const STRICT_MIN = '>';
    const MAX = '<=';
    const STRICT_MAX = '<';
    const EQUALS = '=';
    const NOT_EQUALS = '<>';

    public function __construct($table)
    {
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        $host = 'localhost';
        $dbname = 'db_name';
        $user = 'root';
        $password = 'root';

        try
        {
            $this->co = new PDO('mysql:host='.$host.';dbname='.$dbname.'', $user, $password, $pdo_options);
        }
        catch (Exception $e)
        {
            die('Erreur : ' . $e->getMessage());
        }

        if(!empty($table))
        {
            $this->table = $table;
        }
        else
        {
            throw new Exception('Vous devez renseigner la table utilisée');
        }
    }

    public static function create(string $table)
    {
        // TODO chercher fonction create
        return new BaseQuery($table);
    }

    public function find()
    {
        // TODO fonction find
        $query = 'SELECT ' . $this->table . '.* FROM ' . $this->table;

        // where
        if(!empty($this->filters) and !empty($this->filterValues))
        {
            $i = 0;
            foreach($this->filters as $filter)
            {
                $query .= ($i > 0 ? ' AND ':' WHERE ') . $filter . ' ' . (!empty($criteria) ? $criteria : '=') . ' :' . $filter;
                $i++;
            }
        }

        // limit
        if(!empty($this->limit))
        {
            $query .= ' LIMIT ' . (!empty($this->offset) ? $this->offset . ', ' : '') . $this->limit;
        }

        $req = $this->co->prepare($query);
        $req->execute($this->filterValues);
        return $req->fetchAll();
    }

    public function findOneBy()
    {
        // TODO fonction findOneBy
    }

    public function filterBy(string $col, $value, $criteria = self::EQUALS)
    {
        // TODO filterBy
        $this->filters[] = $col;
        $this->filterValues[] = [$col => $value];

        if(defined('self::'.$criteria))
        {
            $this->criteria = $criteria;
        }

        return $this;
    }

    public function limit(int $max)
    {
        $this->limit = abs($max);
        return $this;
    }

    public function offset(int $length)
    {
        $this->offset = abs($length);
        return $this;
    }

    public function count()
    {
        // TODO count
    }

    public function set(string $col, $value)
    {
        // TODO set
        $this->cols[] = $col;
        $this->values[$col] = $value;
        return $this;
    }

    public function findPK(int $id)
    {
        if(!empty($this->table))
        {
            $query = 'SELECT ' . $this->table . '.* FROM ' . $this->table . ' WHERE ' . $this->table . '.id = :id';
            //            $req = $this->co->prepare($query);
            //            $req->execute([
            //                'id' => $id
            //            ]);

            //            return $req->fetch();
            return $query;
        }
        else
        {
            throw new Exception('Table cannot be empty');
        }
    }

    public function findPKs(array $keys)
    {
        // TODO findPKs
        if(!empty($this->table) and sizeof($keys) > 0)
        {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE ';

            $i = 0;
            foreach($keys as $key)
            {
                $query .= ($i > 0 ? ' OR ' : '') . 'id = ?';
                $i++;
            }

            return $query;

            //            $req = $this->co->prepare($query);
            //            $req->execute($keys);
            //            
            //            return $req->fetchAll();
        }
        else
        {
            throw new Exception('Table cannot be empty');
        }
    }

    public function save()
    {
        // TODO save
        if(!empty($this->table) and !empty($this->cols) and !empty($this->values))
        {
            $query = 'UPDATE ' . $this->table . ' SET ';

            $i = 0;
            foreach($this->cols as $col)
            {
                $query .= ($i > 0 ? ', ':'') . $col . ' = :' . $col;
                $i++;
            }

            $req = $this->co->prepare($query);
            $req->execute($this->values);
        }
        else
        {
            throw new Exception('Nothing to update');
        }
    }
}

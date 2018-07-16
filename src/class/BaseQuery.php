<?php

class BaseQuery
{
    private $co;
    private $table;
    private $base;
    private $cols = [];
    private $values = [];
    private $filters = [];
    private $filterValues = [];
    private $limit;
    private $offset;
    private $criteria;
    private $criterias = ['>=', '>', '<=', '<', '=', '<>'];
    private $count;

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
        $dbname = '__DB_NAME__';
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
            throw new Exception('You need to specify the table');
        }
    }

    public static function create(string $table)
    {
        return new BaseQuery($table);
    }

    public function setBase(string $base)
    {
        $this->base = $base;
    }

    private function createFind(string $table = '')
    {
        $table = !empty($table) ? $table : $this->table;
        $query = 'SELECT ' . $table . '.* ';

        // count
        if(!empty($this->count))
        {
            $query .= ' , COUNT(' . $this->count['col'] . ') AS ' . $this->count['name'] . ' ';
        }

        $query .= 'FROM ' . $table;

        // where
        if(!empty($this->filters) and !empty($this->filterValues))
        {
            $i = 0;
            foreach($this->filters as $filter)
            {
                $query .= ($i > 0 ? ' AND ':' WHERE ') . $filter . ' ' . (!empty($this->criteria) ? $this->criteria : '=') . ' :' . $filter . 'Filter';
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

        return $req;
    }

    private function fetchForeign(string $col, $value, string $table)
    {
        $base = $table . 'Query';
        $base = $base::create();
        $filter = 'FilterBy' . self::formatName($col);
        $base->$filter($value);
        $columns = $base->find(); // TODO return foreing values

        return $columns;
    }

    public function find(string $table = '', string $class = '')
    {
        $table = !empty($table) ? $table : $this->table;
        $class = !empty($class) ? $class : $this->base;
        $fetch = self::createFind($table)->fetch(PDO::FETCH_NAMED); // We need foreign values
        $base = new $class();
        $columns = $base->getColumns();

        foreach($columns as $key => $col)
        {
            if($col['foreign'])
            {
                $fetch[$key] = self::fetchForeign($col['foreign']['col'], $fetch[$key], $col['foreign']['table']);
            }
        }

        return $fetch;
    }

    public function findAll(string $table = '', string $class = '')
    {
        $table = !empty($table) ? $table : $this->table;
        $class = !empty($class) ? $class : $this->base;
        $fetchAll = self::createFind($table)->fetchAll(PDO::FETCH_NAMED);
        $base = new $class();
        $columns = $base->getColumns();
        $fks = [];

        foreach($columns as $key => $col)
        {
            if($col['foreign'])
            {
                $fks[$key] = $col['foreign'];
            }
        }

        if(sizeof($fks) > 0)
        {
            foreach($fetchAll as $key => $entry)
            {
                foreach($fks as $k2 => $fk)
                {
                    $fetchAll[$key][$k2] = self::fetchForeign($fk['col'], $fetchAll[$key][$k2], $fk['table']);
                }
            }
        }

        return $fetchAll;
    }

    public function findOneBy()
    {
        // TODO fonction findOneBy
    }

    public function filterBy(string $col, $value, $criteria = self::EQUALS)
    {
        $this->filters[] = $col;
        $this->filterValues[$col . 'Filter'] = $value;

        if(in_array($criteria, $this->criterias))
        {
            $this->criteria = $criteria;
        }
        else
        {
            throw new Exception("Unknow criteria `$criteria`.");
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

    public function count(string $col, string $name)
    {
        $this->count = [
            'col' => $col,
            'name' => $name
        ];
    }

    public function set(string $col, $value)
    {
        $this->cols[] = $col;
        $this->values[$col . 'Update'] = $value;
        return $this;
    }

    public function findPK(int $id)
    {
        if(!empty($this->table))
        {
            $query = 'SELECT ' . $this->table . '.* FROM ' . $this->table . ' WHERE ' . $this->table . '.id = :id';
            //                        $req = $this->co->prepare($query);
            //                        $req->execute([
            //                            'id' => $id
            //                        ]);
            //
            //                        return $req->fetch();
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

    public function delete($all = false)
    {
        if(!empty($this->filters) and !empty($this->filterValues))
        {
            $query = 'DELETE FROM ' . $this->table . ' WHERE ';
            $i = 0;
            foreach($this->filters as $col)
            {
                $query .= ($i > 0 ? ' AND ':'') . $col . ' = :' . $col . 'Filter';
            }

            $req = $this->co->prepare($query);
            $req->execute($this->filterValues);
        }
        else{
            if($all)
            {
                $req = $this->co->query("DELETE FROM $this->table");
            }
            else
            {
                throw new Exception("If you want to delete all from $this->table, you need to specify optional argument delete(\$all = true)\n");
            }
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
                $query .= ($i > 0 ? ', ':'') . $col . ' = :' . $col . 'Update';
                $i++;
            }

            if(!empty($this->filters) and !empty($this->filterValues))
            {
                $query .= ' WHERE ';
                $i = 0;
                foreach($this->filters as $col)
                {
                    $query .= ($i > 0 ? ' AND ':'') . $col . ' = :' . $col . 'Filter';
                }
            }

            $req = $this->co->prepare($query);
            $req->execute(array_merge($this->values, $this->filterValues));
        }
        else
        {
            throw new Exception('Nothing to update');
        }
    }

    private function formatName(string $name)
    {
        $newName = explode('_', $name);
        $names = [];
        foreach($newName as $Name)
        {
            $names[] = ucfirst(strtolower($Name));
        }

        return implode($names);
    }
}

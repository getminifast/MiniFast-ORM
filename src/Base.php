<?php

class Base
{
    private $cols = [];
    private $values = [];
    private $table;
    private $co;
    
    const INSERT = 'INSERT INTO';
    const VALUES = 'VALUES';
    
    public function __construct(string $table)
    {
        
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        $host = 'localhost';
        $dbname = 'main';
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
    
    /*
     * Attribut une nouvelle valeur à une colonne
     * @param string $col
     * @param string $value
     */
    public function set(string $col, $value)
    {
        $this->cols[] = $col;
        $this->values[] = [$col => $value];
    }
    
    /*
     * Insert les données dans la base de données
     */
    public function save()
    {

        // Si les données ont bien été renseignées
        if(!empty($this->table) and !empty($this->cols) and !empty($this->values))
        {
            $query = self::INSERT . ' ' . $this->table . '(' . implode(', ', $this->cols) . ') ' . self::VALUES . '(:' . implode(', :', $this->cols) . ')';
//            $req = $this->co->prepare($query);
//            $req->execute($this->values);
            echo $query;
        }
        // Sinon erreur
        else
        {
            throw new Exception('You cannot save before inserting data.');
        }
    }
}

<?php

class UserQuery extends BaseQuery
{
    public function __construct($table)
    {
        parent::__construct($table);
    }
    
    public static function create(string $table = 'user')
    {
        return new UserQuery($table);
    }
    
    public function findByPseudo(string $pseudo)
    {
        parent::findBy('pseudo', $pseudo);
    }
    
    public function filterByPseudo($pseudo)
    {
        parent::filterBy('pseudo', $pseudo, parent::EQUALS);
        return $this;
    }
    
    public function filterByEmail($email)
    {
        parent::filterBy('email', $email, parent::EQUALS);
        return $this;
    }
}
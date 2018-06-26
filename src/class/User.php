<?php

class User extends Base
{
    private $table = 'user';
    private $pseudo = 'pseudo';
    private $email = 'email';
    
    public function __construct()
    {
        parent::__construct($this->table);
    }
    
    public function setPseudo($value)
    {
        parent::set($this->pseudo, $value);
    }
    
    public function setEmail($value)
    {
        parent::set($this->email, $value);
    }
}
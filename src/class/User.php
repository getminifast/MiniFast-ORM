<?php

class User extends Base
{
    private $table = 'user';
    private $pseudo = [
        'name' => 'pseudo',
        'type' => 'varchar',
        'required' => true
    ];
    private $email = 'email';
    
    public function __construct()
    {
        parent::__construct('table');
    }
    
    public function setPseudo($value)
    {
        parent::set('pseudo', $value);
    }
    
    public function setEmail($value)
    {
        parent::set('email', $value);
    }
}
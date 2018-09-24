<?php

namespace MiniFastORM;

class Connect extends Singleton
{
    const PROD_HOST = '__PROD_HOST__';
    const PROD_DB_NAME = '__PROD__DB_NAME__';
    const PROD_USER = '__PROD_USER__';
    const PROD_PASSWORD = '__PROD_PASSWORD__';

    const DEV_HOST = '__DEV_HOST__';
    const DEV_DB_NAME = '__DEV__DB_NAME__';
    const DEV_USER = '__DEV_USER__';
    const DEV_PASSWORD = '__DEV_PASSWORD__';
    
    /**
     * Try to get a PDO connection in DEV en PROD environment
     * @return mixed Bool false if no connection, PDO if connection
     */
    public function getPDO()
    {
        if ($pdo = $this->tryProd()) {
            return $pdo;
        } elseif ($pdo = $this->tryDev()) {
            return $pdo;
        }
        
        throw new Exception('Could not connect to a database.');
        
        return false;
    }
    
    /**
     * Try to get a PDO connection in DEV evironment
     * @return mixed False if incorrect, Object if correct
     */
    protected function tryDev()
    {
        $pdo_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        $pdo = null;
        
        try {
            $pdo = new \PDO(
                'mysql:host=' . self::DEV_HOST
                . ';dbname=' . self::DEV_DB_NAME,
                self::DEV_USER,
                self::DEV_PASSWORD,
                $pdo_options
            );
        } catch (Exception $e) {
            return false;
        }
        
        return $pdo;
    }
    
    /**
     * Try to get a PDO connection in PROD environment
     * @return mixed False if incorrect, Object if correct
     */
    protected function tryProd()
    {
        $pdo = null;

        try {
            $pdo = new PDO(
                'mysql:host=' . self::PROD_HOST
                . ';dbname=' . self::PROD_DB_NAME,
                self::PROD_USER,
                self::PROD_PASSWORD
            );
        } catch (Exception $e) {
            return false;
        }

        return $pdo;
    }
}
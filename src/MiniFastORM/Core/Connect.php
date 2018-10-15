<?php

namespace MiniFastORM\Core;

class Connect
{
    protected $container = new Container();
    
    /**
     * Get a PDO connection
     * @return PDO PDO instance
     */
    public function getPDO()
    {
        if ($config = $this->getConfig()) {
            if (isset($config['MiniFastORM']['database'])) {
                if ($this->checkConfig($config['MiniFastORM']['database'])) {
                    $pdo = false;
                    $pdo_options = [];
                    if (isset($config['MiniFastORM']['database']['pdo_error'])) {
                        if ($config['MiniFastORM']['database']['pdo_error']) {
                            $pdo = true;
                            $pdo_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
                        }
                    }
                    
                    $config = $config['MiniFastORM']['database'];
                    
                    $host = $config['host'];
                    $dbname = $config['dbname'];
                    $user = $config['user'];
                    $password = $config['password'];
                    
                    if ($pdo) {
                        return new \PDO('mysql:host='.$host.';dbname='.$dbname.'', $user, $password, $pdo_options);
                    } else {
                        return new \PDO('mysql:host='.$host.';dbname='.$dbname.'', $user, $password);
                    }
                }
            }
        }
        
        throw new Exception('MiniFast ORM is unable to connect to your database. Please check your config file (minifast.json).' . PHP_EOL);
        return null;
    }
    
    /**
     * Get the config of MiniFast ORM
     * @return array The config file
     */
    public function getConfig()
    {
        $filesystem = $this->container->getFilesystem();
        $configPath = __DIR__ . '/minifast.json';
        
        if ($filesystem->exists($configPath)) {
            return json_decode($configPath, true);
        } else {
            throw new Exception('The MiniFast config file (minifast.json) is missing.' . PHP_EOL);
        }
        
        return null;
    }
    
    /**
     * Check if the config is complete
     * @param array   $config The config to check
     * @return boolean True if config is complete
     */
    public function checkConfig(array $config)
    {
        $params = ['host', 'dbname', 'user', 'password'];
        
        foreach ($params as $param) {
            if (!isset($config[$param])) {
                throw new Exception("The parameter $param in the MiniFast config file is missing." . PHP_EOL);
                return false;
            }
        }
        
        return true;
    }
}

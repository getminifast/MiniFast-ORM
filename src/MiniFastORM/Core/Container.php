<?php

namespace MiniFastORM\Core;

class Container
{
    /**
     * Get an instance of *Filesystem*
     * @return Filesystem The instance of Filesystem
     */
    public function getFilesystem()
    {
        return new \Symfony\Component\Filesystem\Filesystem();
    }
    
    /**
     * Get an Instance of *Connect*
     * @return Connect Instance of Conect
     */
    public function getConnect()
    {
        return new Connect();
    }
    
    /**
     * Get an instance of *PDO*
     * @return PDO Instance of PDO
     */
    public function getConnection()
    {
        $connect = $this->getConnect();
        return $connect->getPDO();
    }
}

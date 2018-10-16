<?php

/*
 * This file is part of the MiniFast Framework.
 *
 * (c) Vincent Bathelier <contact@getminifast.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

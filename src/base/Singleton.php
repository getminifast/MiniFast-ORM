<?php

namespace MiniFastORM;

class Singleton
{
    /**
     * Return *Singleton* instance of this class
     * 
     * @staticvar Singleton $instance *Singleton* instance of this class
     * @return Singleton *Singleton* instance
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Non public constructor to avoid new *Singleton* instance creation via `new`
     */
    protected function __construct()
    {}

    /**
     * clone methode is private to avoid *Singleton* instance clonning 
     * @return void
     */
    private function __clone()
    {}

    /**
     * Deserialization method is private to avoid *Singleton* instance clonning
     * @private
     */
    private function __wakeup()
    {}
}
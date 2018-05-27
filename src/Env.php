<?php

namespace SiteClone;

/**
 * Class Env
 * @package SiteClone
 */
class Env
{
    /**
     * @return string
     */
    public function currentDomain()
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    /**
     * @return string
     */
    public function requestURI()
    {
        return $_SERVER['REQUEST_URI'];
    }
}
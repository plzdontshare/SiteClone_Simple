<?php

namespace SiteClone;

/**
 * Class Cache
 * @package SiteClone
 */
class Cache
{
    /**
     * @var string
     */
    private $cache_dir;
    
    /**
     * Cache constructor.
     *
     * @param string $cache_dir
     */
    public function __construct($cache_dir)
    {
        $this->cache_dir = $cache_dir;
    }
    
    /**
     * @param $path
     *
     * @return string
     */
    public function makeHash($path)
    {
        return md5($path);
    }
    
    /**
     * @param string $hash
     *
     * @return bool
     */
    public function exists($hash)
    {
        return file_exists($this->getFullPath($hash));
    }
    
    /**
     * @param string $hash
     *
     * @return string
     */
    public function get($hash)
    {
        $data =  unserialize(file_get_contents($this->getFullPath($hash)));
        $data['content'] = base64_decode($data['content']);
        
        return $data;
    }
    
    /**
     * @param string $hash
     * @param string $data
     */
    public function save($hash, $data)
    {
        $data['content'] = base64_encode($data['content']);
        file_put_contents($this->getFullPath($hash), serialize($data));
    }
    
    /**
     * @param string $hash
     *
     * @return string
     */
    private function getFullPath($hash)
    {
        return $this->cache_dir . '/' . $hash;
    }
}
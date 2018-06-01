<?php

namespace SiteClone;

use Exception;
use SQLite3;

/**
 * Class Database
 * @package SiteClone
 */
class Database
{
    /**
     * @var SQLite3
     */
    private $db;
    /**
     * @var string
     */
    private $last_error;
    /**
     * @var array
     */
    private $config;
    
    /**
     * Database constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Open database file
     * Return `true` on success, `false` on failure
     * When error occurs, it can be fetched by `getLastError()` method.
     *
     * @param string $host
     *
     * @return bool
     */
    public function open($host)
    {
        if (!is_null($this->db)) {
            return true;
        }
        
        try
        {
            $db = $this->databasePath($host);
            $this->db = new SQLite3($db);
            $this->db->busyTimeout(5000);
            $this->db->exec("PRAGMA journal_mode = WAL;");
            $this->db->exec("PRAGMA page_size = 4096;");
            
            return true;
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Find row by ID
     *
     * @param integer $id
     *
     * @return array
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM `pages` WHERE `id` = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        if ($result === false) {
            return false;
        }
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    /**
     * Insert bulk keywords
     *
     * @param array $keywords
     */
    public function addKeywords(array $keywords)
    {
        $now = time();
        $keywords = array_chunk($keywords, 500);
        foreach ($keywords as $keywords_chunk) {
            $this->db->exec('BEGIN IMMEDIATE;');
            $placeholders = implode(', ', array_fill(0, count($keywords_chunk), "(?, 0, {$now})"));
            $stmt = $this->db->prepare("INSERT INTO `pages` (`keyword`, `parsed`, `created_at`) VALUES {$placeholders};");
    
            $i = 1;
            foreach ($keywords_chunk as $keyword) {
                $keyword = trim($keyword);
                $stmt->bindValue($i++, $keyword);
            }
            
            $stmt->execute();
            $this->db->exec('COMMIT;');
        }
    }
    
    /**
     * Fetch random page
     *
     * @return array
     */
    public function randomPage()
    {
        $result = $this->db->query("SELECT max(id) as max_id FROM `pages`");
        
        if ($result === false) {
            return false;
        }
        
        $max_id = $result->fetchArray(SQLITE3_ASSOC)['max_id'];
        
        if ($max_id == 0) {
            return false;
        }
        
        $id = mt_rand(1, $max_id);
        
        return $this->find($id);
    }
    
    /**
     * Return `$count` random rows
     *
     * @param integer $count
     *
     * @return array
     */
    public function randomPages($count)
    {
        $sql = "SELECT id from `pages` WHERE _ROWID_ >= (abs(random()) % (SELECT max(_ROWID_) FROM `pages`)) LIMIT {$count};";
    
        $results = $this->db->query($sql);
        
        if ($results === false) {
            return [];
        }
        
        $pages = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $pages[] = $row;
        }
        
        return $pages;
    }
    
    /**
     * Setting for current domain
     *
     * @return array
     */
    public function settings()
    {
        $query = $this->db->query("SELECT ext from `settings`;");
        
        if ($query === false) {
            return false;
        }
        
        return $query->fetchArray(SQLITE3_ASSOC);
    }
    
    /**
     * Update data
     * Accept ROW_ID and array of key-value pairs to update
     *
     * @param integer $row_id Row ID
     * @param array $data Key-Value pairs to update
     *
     * @return \SQLite3Result
     */
    public function update($row_id, array $data)
    {
        $sql = "UPDATE `pages` set ";
        foreach ($data as $k => $v) {
            $sql .= "`{$k}`=:{$k},";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE `id`=:id;";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $row_id);
        foreach ($data as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Get last error message
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->last_error;
    }
    
    /**
     * Set last error message
     *
     * @param string $message
     */
    public function setLastError($message)
    {
        $this->last_error = $message;
    }
    
    /**
     * @param string $host
     *
     * @return bool
     */
    public function exists($host)
    {
        return file_exists($this->databasePath($host));
    }
    
    /**
     * Install database
     *
     * @param string $host install hostname
     *
     * @return bool
     */
    public function install($host)
    {
        if ($this->open($host) === false) {
            return false;
        }
        
        $migration_file = $this->config['db']['migration'];
        if (!file_exists($migration_file)) {
            $this->setLastError("No migration file exists!");
        }
        $migration = file_get_contents($migration_file);
        $this->db->exec($migration);
        
        $ext = $this->config['extensions'][array_rand($this->config['extensions'])];
        $this->db->exec("INSERT INTO `settings` (`ext`) VALUES ('{$ext}');");
        
        return true;
    }
    
    /**
     * Close database
     */
    public function close()
    {
        $this->db->close();
    }
    
    /**
     * Return full path to database file
     *
     * @param string $host Hostname
     *
     * @return string
     */
    private function databasePath($host)
    {
        return DATABASE_DIR . '/' . $host . '.db';
    }
}
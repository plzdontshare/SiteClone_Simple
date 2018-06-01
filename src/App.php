<?php

namespace SiteClone;

/**
 * Class App
 * @package SiteClone
 */
class App
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var Env
     */
    private $env;
    /**
     * @var Database
     */
    private $db;
    /**
     * @var Parser
     */
    private $parser;
    
    /**
     * App constructor.
     *
     * @param array $config
     * @param Env $env
     * @param Parser $parser
     * @param Database $db
     */
    public function __construct(array $config, Env $env, Parser $parser, Database $db)
    {
        $this->config = $config;
        $this->env = $env;
        $this->db = $db;
        $this->parser = $parser;
    }
    
    /**
     * Process current request
     *
     * @return Response
     */
    public function processRequest()
    {
        $response = new Response;
        $response->setMime('text/html');
        
        $uri = $this->env->requestURI();
        $id = 1;
        $matches = [];
        preg_match("#^/(\d+)\.[a-z0-9]+$#Uuis", $uri, $matches);
        if (isset($matches[1])) {
            $id = (int)$matches[1];
        } else {
            if ($uri !== '/') {
                $response->setRedirect($this->randomPageUrl());
                return $response;
            }
        }
        
        $page = $this->db->find($id);
        
        if ($page === false) {
            $page = $this->db->randomPage();
            
            if ($page === false) {
                $response->setResponseCode(Response::ERROR_RESPONSE_CODE);
                return $response;
            }
        }
        
        $content = null;
        
        if ($page['parsed'] === 0) {
            $url = $this->parser->urlFromBing($page['keyword']);
            // Sometimes bing returns no results, so we shouldn't try to process empty url
            if ($url !== false) {
                $content = $this->parser->processUrl($url, $page['keyword']);
                if ($content !== false) {
                    $this->db->update($page['id'], ['parsed' => 1, 'url' => $url]);
                }
            }
        } else {
            $content = $this->parser->processUrl($page['url'], $page['keyword']);
        }
        
        // We should have something in the $content
        if (empty($content)) {
            $response->setResponseCode(Response::ERROR_RESPONSE_CODE);
            
            return $response;
        }
    
        $content = $this->preProcess($content);
        $content = $this->postProcess($content);
        $content = preg_replace("~#KEYWORD#~Uuis", $page['keyword'], $content);
        $response->setContent($content);
        
        if ($this->config['last_modified']) {
            $response->setLastModified($page['created_at']);
        }
        
        return $response;
    }
    
    /**
     * Pre-process content
     * Remove junk code, counters, etc...
     *
     * @param string $content
     *
     * @return string
     */
    private function preProcess($content)
    {
        $content = $this->applyRules($this->config['preprocess_rules'], $content);
        preg_match_all("~#RANDOM_URL#~Uuis", $content, $matches);
        $count = count($matches[0]);
        $current_domain = $this->env->currentDomain();
        $settings = $this->db->settings();
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $random_page = $this->db->randomPage();
                if ($random_page === false) {
                    continue;
                }
                $content = preg_replace("~#RANDOM_URL#~Uuis", "http://{$current_domain}/{$random_page['id']}.{$settings['ext']}", $content, 1);
            }
        }
        
        return $content;
    }
    
    /**
     * Post-process response
     * Add counter, cloacking, redirect...
     *
     * @param string $content
     *
     * @return string
     */
    private function postProcess($content)
    {
        $content = $this->applyRules($this->config['postprocess_rules'], $content);
        
        if ($this->config['linking']['enabled']) {
            $hosts = $this->getAvailableHosts();
            shuffle($hosts);
            array_splice($hosts, $this->config['linking']['count']);
            
            $links = [];
            foreach ($hosts as $host) {
                $host = basename($host, '.db');
                
                $host_parts = explode('.', $host);
                if (count($host_parts) > ($this->config['linking']['subdomains']['max_level'] - 1)) {
                    array_shift($host_parts);
                    $host = implode('.', $host_parts);
                }
                
                $db = new Database($this->config);
                if ($db->open($host) === false) {
                    continue;
                }
                
                $page = $db->randomPage();
                
                if ($page === false) {
                    continue;
                }
                
                $settings = $db->settings();
                $links[] = "<a href=\"http://{$host}/{$page['id']}.{$settings['ext']}\">{$page['keyword']}</a>";
    
                if ($this->config['linking']['subdomains']['enabled']) {
                    for ($i = 0; $i < $this->config['linking']['subdomains']['count']; $i++) {
                        $alpha = $this->config['linking']['subdomains']['alphabet'];
                        shuffle($alpha);
                        $alpha = implode('', $alpha);
                        $sub = substr($alpha, 0, $this->config['linking']['subdomains']['name_length']);
                        $page = $db->randomPage();
                        $links[] = "<a href=\"http://{$sub}.{$host}/\">{$page['keyword']}</a>";
                    }
                }
                
                $db->close();
            }
            
            if (!empty($links)) {
                $links = implode(' | ', $links);
                $content = preg_replace("#<body(.*)>#Uuis", "<body$1>{$links}", $content);
            }
        }
        
        return $content;
    }
    
    /**
     * Apply rules
     *
     * @param array $rules
     * @param string $content
     *
     * @return string
     */
    private function applyRules(array $rules, $content)
    {
        $current_domain = $this->env->currentDomain();
        foreach ($rules as $regex => $replacement) {
            $regex = str_replace("#CURRENT_DOMAIN#", $current_domain, $regex);
            $replacement = str_replace("#CURRENT_DOMAIN#", $current_domain, $replacement);
            $content = preg_replace($regex, $replacement, $content);
        }
        
        return $content;
    }
    
    /**
     * @return array
     */
    private function getAvailableHosts()
    {
        $hosts = scandir(DATABASE_DIR);
        $hosts = array_diff($hosts, ['..', '.', '.htaccess']);
        $hosts = array_filter($hosts, function ($file) {
            return !preg_match("#(db-shm|db-wal)#Uuis", $file);
        });
        
        return $hosts;
    }
    
    /**
     * @return bool|string
     */
    private function randomPageUrl()
    {
        $hosts = $this->getAvailableHosts();
        
        if (empty($hosts)) {
            return false;
        }
        
        shuffle($hosts);
        
        $host = $hosts[0];
        $host = basename($host, '.db');
        $db = new Database($this->config);
        if ($db->open($host) === false) {
            return false;
        }
    
        $page = $db->randomPage();
    
        if ($page === false) {
            return false;
        }
    
        $settings = $db->settings();
        
        
        return "http://{$host}/{$page['id']}.{$settings['ext']}";
    }
}
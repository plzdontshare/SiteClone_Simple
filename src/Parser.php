<?php

namespace SiteClone;

/**
 * Class Parser
 * @package SiteClone
 */
class Parser
{
    /**
     * @var array
     */
    private $config;
    
    /**
     * Parser constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * @param string $keyword
     *
     * @return bool|string
     */
    public function parse($keyword)
    {
        return $this->pageFromBing($keyword);
    }
    
    /**
     * @param string $keyword
     *
     * @return bool
     */
    public function urlFromBing($keyword)
    {
        $url = "https://www.bing.com/search?format=rss&q=" . urlencode($keyword . ' language:ru ' . $this->config['parser']['bing']['search_tail']);
        $response = $this->curlGET($url);

        $matches = [];
        preg_match_all("#<link>(.*)</link>#Uuis", $response, $matches);
        if (!isset($matches[1])) {
            return false;
        }
    
        $matches = $matches[1];
        // Remove bing from results
        array_shift($matches);
        array_shift($matches);
        
    
        if (empty($matches)) {
            return false;
        }
    
        shuffle($matches);
    
        return array_values($matches)[0];
    }
    
    /**
     * @param string $keyword
     *
     * @return bool|array
     */
    private function pageFromBing($keyword)
    {
        $url = $this->urlFromBing($keyword);
        $page = $this->processUrl($url, $keyword);
        
        return $page;
    }
    
    /**
     * @param string $url
     *
     * @return string|false
     */
    private function curlGET($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.86 Safari/533.4');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_COOKIE, "SRCHHPGUSR=ADLT=OFF&NRSLT=20;");
    
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    /**
     * @param string $url
     * @param string $keyword
     *
     * @return false|null|string|string[]
     */
    public function processUrl($url, $keyword)
    {
        $page = $this->curlGET($url);
        
        if (preg_match("#windows-1251#Uis", $page)) {
            $page = mb_convert_encoding($page, 'UTF-8', 'CP1251');
        }
        
        $url = rtrim($url, '/');
        $url .= '/';
        preg_match("#(https?)://(.*)/#Uuis", $url, $matches);
        $scheme = $matches[1];
        $domain = $matches[2];
        
        $keyword = mb_convert_case($keyword, MB_CASE_TITLE);
        
        $page = preg_replace("#(<a.*)href=[\"']+.*[\"']+#Uuis", "$1href=\"#RANDOM_URL#\"", $page);
        $page = preg_replace("#(<img.*)src=[\"']+/(.*)[\"']+#Uuis", "$1src=\"{$scheme}://{$domain}/$2\"", $page);
        $page = preg_replace("#(<link.*)href=[\"']+/(.*)[\"']+#Uuis", "$1href=\"{$scheme}://{$domain}/$2\"", $page);
        $page = preg_replace("#<title>(.*)</title>#Uuis", "<title>{$keyword}</title>", $page);
        $page = preg_replace("#url\(/(.*)\)#Uuis", "url(\"{$scheme}://{$domain}/$1\")", $page);
        $page = preg_replace("#(<h1.*>).*</h1>#Uuis", "$1{$keyword}</h1>", $page, 1);
        
        return $page;
    }
}
<?php

namespace SiteClone;

/**
 * Class Response
 * @package SiteClone
 */
class Response
{
    const ERROR_RESPONSE_CODE = 503;
    
    /**
     * Request mime type
     *
     * @var string
     */
    private $mime;
    
    /**
     * @var string
     */
    private $content;
    
    /**
     * @var integer
     */
    private $response_code = 200;
    
    /**
     * @var bool
     */
    private $redirect = false;
    
    /**
     * @var integer
     */
    private $last_modified;
    
    /**
     * @param integer $last_modified
     */
    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;
    }
    
    /**
     * @param bool $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }
    
    /**
     * Echo response headers
     */
    public function echoHeaders()
    {
        if ($this->redirect !== false) {
            header('Location: ' . $this->redirect);
        } else {
            http_response_code($this->response_code);
            if (!empty($this->last_modified)) {
                header("Last-Modified: " . gmdate('D, d M Y H:i:s', $this->last_modified), true);
            }
            header("Content-Type: {$this->getMime()}; charset=utf-8");
        }
    }
    
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }
    
    /**
     * @param string $mime
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }
    
    /**
     * @param integer $code
     */
    public function setResponseCode($code)
    {
        $this->response_code = $code;
    }
    
}
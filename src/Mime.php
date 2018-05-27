<?php

namespace SiteClone;

/**
 * Class Mime
 * @package SiteClone
 */
class Mime
{
    /**
     * @var array
     */
    private $mimes = [
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'txt'   => 'text/plain',
        'asc'   => 'text/plain',
        'bmp'   => 'image/bmp',
        'gif'   => 'image/gif',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'jpe'   => 'image/jpeg',
        'png'   => 'image/png',
        'ico'   => 'image/vnd.microsoft.icon',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mpe'   => 'video/mpeg',
        'qt'    => 'video/quicktime',
        'mov'   => 'video/quicktime',
        'avi'   => 'video/x-msvideo',
        'wmv'   => 'video/x-ms-wmv',
        'mp2'   => 'audio/mpeg',
        'mp3'   => 'audio/mpeg',
        'rm'    => 'audio/x-pn-realaudio',
        'ram'   => 'audio/x-pn-realaudio',
        'rpm'   => 'audio/x-pn-realaudio-plugin',
        'ra'    => 'audio/x-realaudio',
        'wav'   => 'audio/x-wav',
        'css'   => 'text/css',
        'zip'   => 'application/zip',
        'pdf'   => 'application/pdf',
        'doc'   => 'application/msword',
        'bin'   => 'application/octet-stream',
        'exe'   => 'application/octet-stream',
        'class' => 'application/octet-stream',
        'dll'   => 'application/octet-stream',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc'  => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'dvi'   => 'application/x-dvi',
        'spl'   => 'application/x-futuresplash',
        'gtar'  => 'application/x-gtar',
        'gzip'  => 'application/x-gzip',
        'js'    => 'application/x-javascript',
        'swf'   => 'application/x-shockwave-flash',
        'tar'   => 'application/x-tar',
        'xhtml' => 'application/xhtml+xml',
        'au'    => 'audio/basic',
        'snd'   => 'audio/basic',
        'midi'  => 'audio/midi',
        'mid'   => 'audio/midi',
        'm3u'   => 'audio/x-mpegurl',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'rtf'   => 'text/rtf',
        'wml'   => 'text/vnd.wap.wml',
        'wmls'  => 'text/vnd.wap.wmlscript',
        'xsl'   => 'text/xml',
        'xml'   => 'text/xml',
    ];
    
    /**
     * Map file extension to mime
     *
     * @param string $filename File name
     *
     * @return mixed|string
     */
    public function extToMime($filename)
    {
        $extension = explode('.', $filename);
        $extension = array_pop($extension);
        
        if (!isset($this->mimes[strtolower($extension)])) {
            $type = 'text/html';
        } else {
            $type = $this->mimes[strtolower($extension)];
        }
        
        return $type;
    }
}
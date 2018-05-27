<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require './init.php';

$config = include './conf/config.php';
$env = new \SiteClone\Env();
$db = new \SiteClone\Database($config);
$db->open($env->currentDomain());
$settings = $db->settings();
$pages = $db->randomPages(100);

header('Content-Type: text/xml; charset=UTF-8');

$time = time();

echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($pages as $page) {
    echo '<url>
       <loc>http://'.$env->currentDomain().'/' . $page['id'] . '.' . $settings['ext'] .'</loc>
       <lastmod>'.date('Y-m-d', $time).'</lastmod>
       <changefreq>weekly</changefreq>
       <priority>1.0</priority>
    </url>
    ';
}
echo '</urlset>';

$curl_arr = array();
$master = curl_multi_init();

$sitemap_xml_ping_links = array(
    'https://www.bing.com/webmaster/ping.aspx?sitemap='.urlencode('http://'.$env->currentDomain().'/sitemap.xml'),
    'https://www.google.com/webmasters/sitemaps/ping?sitemap='.urlencode('http://'.$env->currentDomain().'/sitemap.xml')
);

for($i = 0; $i < 2; $i++) {
    $url = trim($sitemap_xml_ping_links[$i]);
    $curl_arr[$i] = curl_init($url);
    curl_setopt($curl_arr[$i], CURLOPT_HEADER, false);
    curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_arr[$i], CURLOPT_DNS_CACHE_TIMEOUT, 600);
    curl_setopt($curl_arr[$i], CURLOPT_TIMEOUT, 4);
    curl_setopt($curl_arr[$i], CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_arr[$i], CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl_arr[$i], CURLOPT_MAXREDIRS, 2);
    curl_setopt($curl_arr[$i], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.56 (KHTML, like Gecko) Chrome/56.0.1750.154 Safari/537.36');
    curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl_arr[$i], CURLOPT_ENCODING, 'gzip');
    curl_setopt($curl_arr[$i], CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
    curl_multi_add_handle($master, $curl_arr[$i]);
}

do {curl_multi_exec($master, $running);} while($running > 0);
for($i = 0; $i < 2; $i++) {
    $outch = curl_multi_getcontent ($curl_arr[$i]);
}

$db->close();
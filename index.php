<?php

require_once './init.php';
require ROOT_DIR . '/yandex.php';

$config = include CONFIG_DIR . "/config.php";
$env = new \SiteClone\Env;
$parser = new \SiteClone\Parser($config);
$current_domain = $env->currentDomain();
$db = new \SiteClone\Database($config);

if ($db->exists($current_domain) === false) {
    if ($db->install($current_domain) === false) {
        die("Could not create database: {$db->getLastError()}");
    }
    
    $keywords_file = $config['keywords']['file'];
    if (!file_exists($keywords_file)) {
        die("Missing keywords file: {$keywords_file}");
    }
    
    $keywords = file($keywords_file);
    shuffle($keywords);
    array_splice($keywords, $config['keywords']['per_host']);
    $db->addKeywords($keywords);
}

if ($db->open($current_domain) === false) {
    die("Could not open database: {$db->getLastError()}");
}
    
$app = new \SiteClone\App($config, $env, $parser, $db);
$response = $app->processRequest();

$db->close();

$response->echoHeaders();
echo $response->getContent();

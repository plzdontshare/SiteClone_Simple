<?php

header('Content-Type: text/plain; charset=UTF-8');

$host = "http://" . $_SERVER['HTTP_HOST'];

$robots = <<<END
User-Agent: *
Crawl-delay: 2
Sitemap: $host/sitemap.xml

User-Agent: Yandex
Crawl-delay: 2
Sitemap: $host/sitemap.xml
Host: $host
END;

echo $robots;
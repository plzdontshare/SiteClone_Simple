<?php

$liveinternet = <<<LIVEINTERNET
LIVEINTERNET;

$cloak = <<<CLOAK
CLOAK;


return [
    'preprocess_rules'  => [
        // Отключаем передачу реферера (работает не во всех бразуерах)
        "#</head>#Uuis"                                                                => "<meta name=\"referrer\" content=\"no-referrer\" /></head>",
        // Вырезаем всякий мусор
        "/<!--LiveInternet counter-->.*<!--\/LiveInternet-->/Uuis"                     => '',
        "/<!-- Yandex\.Metrika counter -->.*<!-- \/Yandex\.Metrika counter -->/Uuis"   => '',
        "/<!-- Yandex\.Metrika informer -->.*<!-- \/Yandex\.Metrika informer -->/Uuis" => '',
        "/var _gaq.*<\/script>/Uuis"                                                   => '</script>',
        '#<meta name="google-site-verification" content=".*" />#Uuis'                  => '',
        '#<meta name="wmail-verification" content=".*" />#Uuis'                        => '',
        '#<meta name="yandex-verification" content=".*" />#Uuis'                       => '',
        '#<div id="MP_block_container_.*?</script>#Uuis'                               => '',
        '#<script.*>.*</script>#Uuis'                                                  => '',
        // Меняем кодировку на человеческую
        '#charset=windows-1251#Uuis'                                                   => 'charset=utf-8',
    ],
    'postprocess_rules' => [
        // Добавялем счетчик LI
        '#<body(.*)>#Uuis' => "<body$1>{$liveinternet}",
        // Добавляем свой код для слива (модалка, кнопка, редирект, клоака и т.д)
        "#</body>#Uuis"    => $cloak . '</body>',
    ],
    'extensions'        => [
        'html',
        'php',
        'htm',
        'asp',
        'aspx',
        'website',
        'site',
        'video',
        'movie',
        'hd720',
        'hd1080',
        'youtube',
        'google',
        'file',
    ],
    
    'keywords' => [
        'file'     => CONFIG_DIR . '/keywords.txt',
        'per_host' => mt_rand(100, 2000),
    ],
    
    'parser' => [
        'bing' => [
            'lang'        => 'ru',
            'search_tail' => '-site:wikipedia.org -site:youtube.com -site:twitter.com -site:instagram.com -site:vk.com -site:facebook.com',
        ],
    ],
    
    'linking' => [
        'enabled'    => true,
        'count'      => mt_rand(3, 5),
        'subdomains' => [
            'enabled'     => true,
            'name_length' => mt_rand(1, 2),
            'count'       => mt_rand(1, 4),
            'max_level'   => 3,
            'alphabet'    => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        ],
    ],
    
    'last_modified' => true,
    
    // SYSTEM
    'db'            => [
        'migration' => CONFIG_DIR . '/migration.sql',
    ],
];

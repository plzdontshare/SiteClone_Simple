<?php

if (preg_match('!\/yandex_([0-9a-z]+)\.html!siu', $_SERVER['REQUEST_URI'])) {
    preg_match('!\/yandex_([0-9a-z]+)\.html!siu', $_SERVER['REQUEST_URI'], $url);
    $yandex = @trim($url[1]);
    echo '<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>Verification: '.$yandex.'</body>
</html>';
    die();
}
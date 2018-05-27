# SiteClone (Public/Simple)

Скрипт для "проксирования" сайтов. **Использовать на свой страх и риск!**

## Установка

1) Залить содержимое архива на сервер

2) Выставить права на запись для вебсервера в папку db

3) Пример конфигурации для Nginx:

`/etc/nginx/sites-enabled/siteclone.conf`:
```
server {
    listen 80;
    # Можно поставить access_log off; если не нужны логи
    access_log /var/log/nginx/siteclone.access.log;
    # Пусть где лежат файлы
    root /var/www/siteclone;
    index index.php;

    rewrite sitemap\.xml$ /sitemap.php break;
    rewrite robots\.txt$ /robots.php break;

    if (!-e $request_filename) {
        rewrite ^.*$ /index.php last;
    }

    # В итоге будет доступен wildcard для домена + дор на самом домене
    # server_name ~^.*\.(domain|domain2)\.tld domain.tld domain2.tld ;
    # или так, чтобы сервер отвечал на любой домен
    # server_name _;
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PHP_VALUE "error_log=/var/log/nginx/pack_errors.log";
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        include fastcgi_params;
    }
}
```

4) Добавить кейворды в файл `conf/keywords.txt` (или в любой другой, но тогда придется поменять путь в конфиге).

5) Настроить `conf/config.php`

6) Если пояляются ошибки в процессе установки, в файле `init.php` выставить DEBUG - true, и смотреть логи. 

### config.php
- `preprocess_rules` - массив с регулярками для обработки спаршенного сайта. Сюда добавляются правила для вырезания счетчиков\рекламы и любого другого палева.
- `postprocess_rules` - массив с регулярками для пост-обработки. Сюда добавляются регулярки для добавления свои данных в спаршенный контент (счетчик, модалка, редиректы и т.д)
- `extensions` - массив с расширениями для "файлов". Скрипт для каждого дора берет рандомное расширение из этого массива.
- `keywords`
  - `file` - путь к файлу с кейвордами
  - `per_host` - кол-во кейвордов для каждого дора
- `linking` - перелинковка
  - `enabled` - включена true/false
  - `count` - кол-во ссылок в перелинковке
  - `subdomains` - настройки сабдоменов
    - `enabled` - true/false. Если true, то в линковку будут добавляться рандомные сабдомены
    - `name_length` - длина имени сгенерированного сабдомена
    - `count` - кол-во сабдоменов которые будут добавляться в перелинковку
    - `max_level` - масимальный уровень сабдоменов (sub.site.com, sub2.sub.site.com, etc...)
    - `alphabet` - алфавит из которого генерируется сабдомен
- `last_modified` - добавлять 'LAST MODIFIED' заголовок к ответу.

В `config.php` так же есть 2 переменные `$liveinternet` и `$cloak`, в них соотвественно вписывайте свой код счетчика и код клоаки.

В скрипте есть автоматическое подтверждение для YaWM через html файл.

7) Пример установки необходимого софта на VPS:
```bash
sudo apt-get install nginx sqlite3 php-fpm php-sqlite3 php-mbstring php-curl php-xml
``` 
  
  
## Макросы

- В `pre_process/post_process` секции можно использовать макрос `#CURRENT_DOMAIN#` для указания текущего домена
- В `pre_process` секции можно использовать макрос `#RANDOM_URL#` который заменится на рандомный URL для текущего дора
- Макрос `#KEYWROD#` можно использовать везде. Заменяется на кейворд текущей страницы

## Ошибки

Иногда возникают ошибки при парсинге страниц, не стоит обращать на них внимания.

## Автор

Автор: NoHate

Контакты: можно телеграфировать
# video-host-backend
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/xpadev-net/niconicomments/blob/master/LICENSE)  
以前自分用に作成したYoutubeもどきを公開できるように改変したものです  

フロントエンドはこっち -> https://github.com/xpadev-net/video-host

## Installation
```bash
git clone https://github.com/xpadev-net/video-host-backend
```

## Example
### Config
```php
<?php
const DB_HOST = 'localhost',
    DB_USER = 'hoge',
    DB_PASSWORD = 'hoge',
    DB_PORT = '3306',
    DB_NAME = 'video-host',
    ALLOWED_DOMAIN = ['http://localhost:3000','http://localhost:63342','video-host.xpadev.net'],
    IMG_DIR = '/usr/share/nginx/img/',
    NICONICO_EMAIL = 'hoge@example.com',
    NICONICO_PASSWORD = 'hogehoge';
```
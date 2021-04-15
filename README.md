<p align="center">
<img src="https://user-images.githubusercontent.com/2666735/30651452-58ae6c88-9deb-11e7-9e13-6beae3f6c54c.png" alt="Meting">
</p>

# meting-api

## Descriptions

- 这是基于[Meting](https://github.com/metowolf/Meting)创建的 APlayer API
- 灵感源于[https://api.fczbl.vip/163/](https://api.fczbl.vip/163/)

## Build Setup

```bash
# 克隆仓库
$ git clone https://github.com/injahow/meting-api.git

$ cd meting-api

# 安装依赖
$ composer install

# 或者使用中国镜像
$ composer config -g repo.packagist composer https://packagist.phpcomposer.com

$ composer install
```

或者下载打包文件
[https://github.com/injahow/meting-api/releases](https://github.com/injahow/meting-api/releases)

或者直接使用 Meting.php

```php
// require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Meting.php';
```

修改代码参数

```php
<?php
// 设置API路径
define('API_URI', '你的API地址');
// ...
```

## Demo

API-Demo:

- [https://api.injahow.cn/meting/?type=url&id=416892104](https://api.injahow.cn/meting/?type=url&id=416892104)
- [https://api.injahow.cn/meting/?type=single&id=591321](https://api.injahow.cn/meting/?type=single&id=591321)
- [https://api.injahow.cn/meting/?type=playlist&id=2619366284](https://api.injahow.cn/meting/?type=playlist&id=2619366284)

APlayer-Demo:

- [https://injahow.github.io/meting-api/](https://injahow.github.io/meting-api/)
- [https://injahow.github.io/meting-api/?id=2904749230](https://injahow.github.io/meting-api/?id=2904749230)

## Thanks

- [APlayer](https://github.com/MoePlayer/APlayer)
- [Meting](https://github.com/metowolf/Meting)
- [MetingJS](https://github.com/metowolf/MetingJS)

## Requirement

PHP 5.4+ and BCMath, Curl, OpenSSL extension installed.

## License

[MIT](https://github.com/injahow/meting-api/blob/master/LICENSE) license.

Copyright (c) 2019 injahow

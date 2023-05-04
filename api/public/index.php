<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <link rel="shortcut icon" href="favicon.png">
    <title>Meting-API</title>
</head>

<body>
    <h2>参数说明</h2>
    server: 数据源
    <br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;netease 网易云音乐(默认)<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tencent QQ音乐<br />
    <br />
    type: 类型<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;name 歌曲名<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;artist 歌手<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;url 链接<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pic 封面<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;lrc 歌词<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;song 单曲<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;playlist 歌单<br /><br />
    id: 类型ID（封面ID/单曲ID/歌单ID）<br />
    <br />
    GitHub：<a href="https://github.com/injahow/meting-api" target="_blank">meting-api</a>，此API基于 <a href="https://github.com/metowolf/Meting" target="_blank">Meting</a> 构建。<br /><br />
    例如：<a href="<?php echo API_URI ?>?type=url&id=416892104" target="_blank"><?php echo API_URI ?>?type=url&id=416892104</a><br />
    <a href="<?php echo API_URI ?>?type=song&id=591321" target="_blank" style="padding-left:48px"><?php echo API_URI ?>?type=song&id=591321</a><br />
    <a href="<?php echo API_URI ?>?type=playlist&id=2619366284" target="_blank" style="padding-left:48px"><?php echo API_URI ?>?type=playlist&id=2619366284</a>
</body>

</html>

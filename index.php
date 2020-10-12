<?php
// 设置API路径
$API_URI = 'https://api.injahow.cn/meting/';

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
?>
<?php if ($type == '' || $id == '') { ?>
    <!DOCTYPE HTML>
    <html>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

    <head>
        <link rel="shortcut icon" href="favicon.png">
        <title>Meting-API</title>
    </head>

    <body>
        <h1>参数说明</h1>
        server: 数据源<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;netease 网易云音乐(默认)<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tencent QQ音乐<br /><br />
        type: 类型<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;name 歌曲名<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;artist 歌手<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;url 链接<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;cover 封面<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;lrc 歌词<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;single 获取以上所有信息(单曲)<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;playlist 获取以上所有信息(歌单)<br /><br />
        id: 单曲ID或歌单ID<br /><br />
        Github：<a href="https://github.com/injahow/meting-api" target="_blank">meting-api</a>，此API基于 <a href="https://github.com/metowolf/Meting" target="_blank">Meting</a> 构建。<br /><br />
        例如：<a href="<?php echo $API_URI ?>?type=url&id=427139429" target="_blank"><?php echo $API_URI ?>?type=url&id=427139429</a><br />
        <a href="<?php echo $API_URI ?>?type=single&id=591321" target="_blank" style="padding-left:48px"><?php echo $API_URI ?>?type=single&id=591321</a><br />
        <a href="<?php echo $API_URI ?>?type=playlist&id=2619366284" target="_blank" style="padding-left:48px"><?php echo $API_URI ?>?type=playlist&id=2619366284</a>
    </body>

    </html>
<?php exit;
} ?>
<?php
// 数据格式
header('Content-type: application/json; charset=UTF-8;');
// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require 'vendor/autoload.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);
//$api->cookie('os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; MUSIC_U=****** ; __remember_me=true');

if ($type == 'playlist') {
    $data = $api->playlist($id);
    if ($data == '[]') {
        echo '[]';
        exit;
    }
    $data = json_decode($data);
    $playlist = array();
    foreach ($data as $song) {
        $name = $song->name;
        $artist = implode('/', $song->artist);
        $pic_id = $song->pic_id;
        $url_id = $song->url_id;
        $lyric_id = $song->lyric_id;
        $cover = json_decode($api->pic($pic_id))->url;
        $source = $song->source;
        $song = array(
            'name'   => $name,
            'artist' => $artist,
            'url'    => $API_URI . '?server=' . $source . '&type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => $API_URI . '?server=' . $source . '&type=lrc&id=' . $lyric_id
        );
        array_push($playlist, $song);
    }
    echo json_encode($playlist);
} else {

    $song = $api->song($id);

    if ($song == '[]') {
        echo '[]';
        exit;
    }

    $song = json_decode($song)[0];

    if ($type == 'name') {
        echo $song->name;
    } elseif ($type == 'artist') {
        echo implode('/', $song->artist);
    } elseif ($type == 'url') {
        $url_id = $song->url_id;
        $m_url = json_decode($api->url($url_id))->url;
        if ($m_url[4] != 's') { // 改https
            $m_url = str_replace('http', 'https', $m_url);
        }
        header('Location: ' . $m_url);
    } elseif ($type == 'cover') {
        $pic_id = $song->pic_id;
        echo json_decode($api->pic($pic_id))->url;
    } elseif ($type == 'lrc') {
        $lyric_id = $song->lyric_id;
        $lrc = json_decode($api->lyric($lyric_id))->lyric;

        if ($lrc == '') {
            $lrc = '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
        }
        echo $lrc;
    } elseif ($type == 'single') {
        $name = $song->name;
        $artist = implode('/', $song->artist);
        $url_id = $song->url_id;
        $pic_id = $song->pic_id;
        $cover = json_decode($api->pic($pic_id))->url;
        $lyric_id = $song->lyric_id;
        $msg = array(
            'name'   => $name,
            'artist' => $artist,
            'url'    => $API_URI . '?server=' . $source . '&type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => $API_URI . '?server=' . $source . '&type=lrc&id=' . $lyric_id
        );
        echo json_encode($msg);
    } else {
        echo '[]';
    }
}
?>
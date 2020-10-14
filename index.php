<?php
// 设置API路径
$API_URI = 'https://api.injahow.cn/meting/';

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($type == '' || $id == '') {
    include './public/index.html';
    exit;
}

// 数据格式
header('Content-type: application/json; charset=UTF-8;');
// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require 'vendor/autoload.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);

// 设置cookie
/*
if ($server == 'netease') {
    $api->cookie('os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; MUSIC_U=****** ; __remember_me=true');
}*/

if ($type == 'playlist') {
    $data = $api->playlist($id);
    if ($data == '[]') {
        echo 'ERROR';
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

        $playlist[] = array(
            'name'   => $name,
            'artist' => $artist,
            'url'    => $API_URI . '?server=' . $source . '&type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => $API_URI . '?server=' . $source . '&type=lrc&id=' . $lyric_id
        );
    }
    echo json_encode($playlist);
} else {

    $song = $api->song($id);

    if ($song == '[]') {
        echo 'ERROR';
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
        $source = $song->source;
        $msg = array(
            'name'   => $name,
            'artist' => $artist,
            'url'    => $API_URI . '?server=' . $source . '&type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => $API_URI . '?server=' . $source . '&type=lrc&id=' . $lyric_id
        );
        echo json_encode($msg);
    } else {
        echo 'ERROR';
    }
}

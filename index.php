<?php
// 设置API路径
define('API_URI', 'https://api.injahow.cn/meting/');
// 设置缓存及时间
define('CACHE', true);
define('CACHE_TIME', 86400); //86400s = 1 day

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    include __DIR__ . '/public/index.html';
    exit;
}

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type =  $_GET['type'];
$id =  $_GET['id'];

// 数据格式
header('Content-type: application/json; charset=UTF-8;');
// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require __DIR__ . '/vendor/autoload.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);

// 设置cookie
/*
if ($server == 'netease') {
    $api->cookie('os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; MUSIC_U=****** ; __remember_me=true');
}*/

if ($type == 'playlist') {

    // 设置缓存
    if (CACHE) {
        $file_name = __DIR__ . '/cache/playlist/' . $server . '_' . $id . '.json';
        if (file_exists($file_name)) {
            // < 1 day
            if ($_SERVER['REQUEST_TIME'] - filectime($file_name) < CACHE_TIME) {
                echo file_get_contents($file_name);
                exit;
            }
        }
    }

    $data = $api->playlist($id);
    if ($data == '[]') {
        echo '{"error":"unknown id"}';
        exit;
    }
    $data = json_decode($data);
    $playlist = array();
    foreach ($data as $song) {
        $playlist[] = array(
            'name'   => $song->name,
            'artist' => implode('/', $song->artist),
            'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id,
            'cover'  => json_decode($api->pic($song->pic_id))->url,
            'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id
        );
    }
    $playlist = json_encode($playlist);
    // 设置缓存
    if (CACHE) {
        // ! mkdir /cache/playlist
        file_put_contents($file_name, $playlist);
    }
    echo $playlist;
} else {

    $song = $api->song($id);
    if ($song == '[]') {
        echo '{"error":"unknown id"}';
        exit;
    }

    $song = json_decode($song)[0];

    switch ($type) {
        case 'name':
            echo $song->name;
            break;

        case 'artist':
            echo implode('/', $song->artist);
            break;

        case 'url':
            $m_url = json_decode($api->url($song->url_id))->url;
            if ($m_url[4] != 's') { // 改https
                $m_url = str_replace('http', 'https', $m_url);
            }
            header('Location: ' . $m_url);
            break;

        case 'cover':
            echo json_decode($api->pic($song->pic_id))->url;
            break;

        case 'lrc':
            $lrc = json_decode($api->lyric($song->lyric_id))->lyric;
            if ($lrc == '') {
                $lrc = '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
            }
            echo $lrc;
            break;

        case 'single':
            $msg = array(
                'name'   => $song->name,
                'artist' => implode('/', $song->artist),
                'url'    => API_URI . '?server=' . $source . '&type=url&id=' . $song->url_id,
                'cover'  => json_decode($api->pic($song->pic_id))->url,
                'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id
            );
            echo json_encode($msg);
            break;

        default:
            echo '{"error":"unknown type"}';
    }
}

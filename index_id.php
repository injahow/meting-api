<?php
define('API_URI', 'https://api.injahow.cn/meting/');

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    include __DIR__ . '/public/index.html';
    exit;
}

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type = $_GET['type'];
$id = $_GET['id'];

// 数据格式
header('Content-type: application/json; charset=UTF-8;');
// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// require __DIR__ . '/vendor/autoload.php';
// you can use 'Meting.php' instead of 'autoload.php'
require __DIR__ . '/src/Meting.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);

if ($type == 'playlist') {

    $data = $api->playlist($id);

    if ($data == '[]') {
        echo '{"error":"unknown playlist id"}';
        exit;
    }
    $data = json_decode($data);

    $count_num = 0;
    $last_id = count($data);

    $lastid = isset($_GET['lastid']) ? $_GET['lastid'] : '';
    $limit = isset($_GET['limit']) ? $_GET['limit'] : '';

    $lastid = $lastid == '' || $lastid == '0' ? $last_id + 1 : $lastid;
    $limit = $limit == '' ? 10 : $limit;

    $playlist = [];
    foreach ($data as $song) {

        if ($last_id >= $lastid) {
            --$last_id;
            continue;
        }
        if ($count_num == $limit) break;

        $playlist[] = array(
            'id'     => $last_id,
            'mid'    => $song->id,
            'name'   => $song->name,
            'artist' =>  implode('/', $song->artist),
            'cover'  => json_decode($api->pic($song->pic_id))->url
        );
        --$last_id;
        ++$count_num;
    }
    echo json_encode($playlist);
} elseif ($type == 'search') {
    // add search
    $search_msg = $_GET['data'];
    $data = $api->search($search_msg);
    if ($data == '[]') {
        echo '[]';
        exit;
    }
    $data = json_decode($data);

    $count_num = 0;
    $last_id = count($data);

    // ! need to optimize
    $lastid = isset($_GET['lastid']) ? $_GET['lastid'] : '';
    $limit = isset($_GET['limit']) ? $_GET['limit'] : '';

    $lastid = $lastid == '' || $lastid == '0' ? $last_id : $lastid;
    $limit = $limit == '' ? 10 : $limit;

    $searchlist = [];

    foreach ($data as $msg) {

        if ($last_id >= $lastid) {
            --$last_id;
            continue;
        }
        if ($count_num == $limit) break;

        $searchlist[] = array(
            'id'     => $last_id,
            'mid'    => $msg->id,
            'name'   => $msg->name,
            'artist' => implode('/', $msg->artist),
            'cover'  => json_decode($api->pic($msg->pic_id))->url
        );

        --$last_id;
        ++$count_num;
    }
    echo json_encode($searchlist);
} else {
    $song = $api->song($id);

    if ($song == '[]') {
        echo '{"error":"unknown song id"}';
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
            if ($m_url == '') {
                exit;
            }
            if ($m_url[4] != 's') { // 改https
                $m_url = str_replace('http', 'https', $m_url);
            }
            header('Location: ' . $m_url);
            break;

        case 'cover':
            $c_url = json_decode($api->pic($song->pic_id))->url;
            if ($c_url == '') {
                exit;
            }
            header('Location: ' . $c_url);
            break;

        case 'lrc':
            $lrc = json_decode($api->lyric($song->lyric_id))->lyric;
            if ($lrc == '') {
                $lrc = '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
            }
            echo $lrc;
            break;

        case 'single':
            // dt:播放页面需要时间
            $msg = array(
                'name'   => $song->name,
                'artist' => implode('/', $song->artist),
                'dt'     => json_decode($api->format(false)->song($id))->songs[0]->dt,
                'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id,
                'cover'  => json_decode($api->pic($song->pic_id))->url,
                'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id
            );
            echo json_encode($msg);
            break;

        default:
            echo '{"error":"unknown type"}';
    }
}

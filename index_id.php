<?php
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

if ($type == 'playlist') {

    $data = $api->playlist($id);

    if ($data == '[]') {
        echo 'ERROR';
        exit;
    }
    $data = json_decode($data);


    $count_num = 0;
    $last_id = count($data);

    $lastid = isset($_GET['lastid']) ? $_GET['lastid'] : '';
    $limit = isset($_GET['limit']) ? $_GET['limit'] : '';

    $lastid = $lastid == '' || $lastid == '0' ? $last_id + 1 : $lastid;
    $limit = $limit == '' ? 10 : $limit;

    $playlist = array();
    foreach ($data as $song) {

        if ($last_id >= $lastid) {
            --$last_id;
            continue;
        }
        if ($count_num == $limit) break;
        $m_id = $song->id;
        $name = $song->name;
        $artist_list = $song->artist;
        $artist = implode('/', $artist_list);
        $pic_id = $song->pic_id;
        $cover = json_decode($api->pic($pic_id))->url;

        $playlist[] = array(
            'id'     => $last_id,
            'mid'    => $m_id,
            'name'   => $name,
            'artist' => $artist,
            'cover'  => $cover
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
        echo 'ERROR';
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

    $searchlist = array();

    foreach ($data as $msg) {

        if ($last_id >= $lastid) {
            --$last_id;
            continue;
        }
        if ($count_num == $limit) break;
        $m_id = $msg->id;
        $name = $msg->name;
        $artist_list = $msg->artist;
        $artist = implode('/', $artist_list);
        $pic_id = $msg->pic_id;
        $cover = json_decode($api->pic($pic_id))->url;

        $searchlist[] = array(
            'id'     => $last_id,
            'mid'    => $m_id,
            'name'   => $name,
            'artist' => $artist,
            'cover'  => $cover
        );

        --$last_id;
        ++$count_num;
    }
    echo json_encode($searchlist);
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
        $artist = implode('/', $song->artist);
        echo $artist;
    } elseif ($type == 'url') {
        $url_id = $song->url_id;
        $m_url = json_decode($api->url($url_id))->url;
        if ($m_url[4] != 's') {
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
        $artist_list = $song->artist;
        $artist = implode('/', $artist_list);
        $url_id = $song->url_id;
        $pic_id = $song->pic_id;
        $cover = json_decode($api->pic($pic_id))->url;
        $lyric_id = $song->lyric_id;
        $source = $song->source;
        // 播放页面设置时间
        $dt = json_decode($api->format(false)->song($id))->songs[0]->dt;

        $msg = array(
            'name'   => $name,
            'artist' => $artist,
            'dt'     => $dt,
            'url'    => $API_URI . '?server=' . $source . '&type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => $API_URI . '?server=' . $source . '&type=lrc&id=' . $lyric_id
        );
        echo json_encode($msg);
    } else {
        echo 'ERROR';
    }
}

<?php
$API_URI = 'https://api.injahow.cn/meting/index_cn.php';

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

$api = new Meting('netease');
$api->format(true);

if ($type == 'playlist') {
    $data = $api->playlist($id);
    if ($data == '[]') {
        echo '[]';
        exit;
    }
    $data = json_decode($data);
    $msgs = array();
    foreach ($data as $msg) {
        $name = $msg->name;
        $artist_list = $msg->artist;
        $artist = implode('/', $artist_list);
        $pic_id = $msg->pic_id;
        $url_id = $msg->url_id;
        $lyric_id = $msg->lyric_id;
        $cover = json_decode($api->pic($pic_id))->url;
        $msg = array(
            'name'   => $name,
            'artist' => $artist,
            'url'    => $API_URI . '?server=' . $source . '&type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => $API_URI . '?server=' . $source . '&type=lrc&id=' . $lyric_id
        );
        $msgs[] = $msg;
    }
    echo json_encode($msgs);
} else {
    $msg = $api->song($id);
    if ($msg == '[]') {
        echo '[]';
        exit;
    }
    $msg = json_decode($msg);
    if ($type == 'name') {
        echo $msg[0]->name;
    } elseif ($type == 'artist') {
        $artist_list = $msg[0]->artist;
        $artist = implode('/', $artist_list);
        echo $artist;
    } elseif ($type == 'url') {
        $url_id = $msg[0]->url_id;
        $m_url = json_decode($api->url($url_id))->url;
        if ($m_url[4] != 's') {
            $m_url = str_replace('http', 'https', $m_url);
        }
        header('Location:' . $m_url);
    } elseif ($type == 'cover') {
        $pic_id = $msg[0]->pic_id;
        echo json_decode($api->pic($pic_id))->url;
    } elseif ($type == 'lrc') {
        $lyric_id = $msg[0]->lyric_id;
        $lrc_json = json_decode($api->lyric($lyric_id));
        $lrc = $lrc_json->lyric;
        if ($lrc == '') {
            echo '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
            exit;
        }
        /**
         * add lyric_cn
         */
        $lrc_arr = explode("\n", $lrc);
        $lrc_cn_arr = explode("\n", $lrc_json->tlyric);
        foreach ($lrc_cn_arr as $k => $v) {
            $lrc_cn_arr2[$k] = explode(']', $v);
        }
        foreach ($lrc_arr as $i => $i_v) {
            $lrc_arr2[$i] = explode(']', $i_v);
            foreach ($lrc_cn_arr2 as $cn_i => $cn_v) {
                if ($cn_v[0] == $lrc_arr2[$i][0] && $cn_v[1] != '') {
                    $lrc_arr[$i] .= '(' . $cn_v[1] . ')';
                    unset($lrc_cn_arr2[$cn_i]);
                }
            }
        }
        echo implode("\n", $lrc_arr);
    } elseif ($type == 'single') {
        $name = $msg[0]->name;
        $artist_list = $msg[0]->artist;
        $artist = implode('/', $artist_list);
        $url_id = $msg[0]->url_id;
        $pic_id = $msg[0]->pic_id;
        $cover = json_decode($api->pic($pic_id))->url;
        $lyric_id = $msg[0]->lyric_id;
        $msg = array(
            'name'   => $name,
            'artist' => $artist,
            'url'    => 'https://api.injahow.cn/meting/index_cn.php?type=url&id=' . $url_id,
            'cover'  => $cover,
            'lrc'    => 'https://api.injahow.cn/meting/index_cn.php?type=lrc&id=' . $lyric_id
        );
        echo json_encode($msg);
    } else {
        echo '[]';
    }
}

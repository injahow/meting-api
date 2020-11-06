<?php
// 设置API路径
define('API_URI', 'https://api.injahow.cn/meting/index_cn.php');

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
    echo json_encode($playlist);
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
            if ($m_url[4] != 's') { // 改https
                $m_url = str_replace('http', 'https', $m_url);
            }
            header('Location: ' . $m_url);
            break;

        case 'cover':
            echo json_decode($api->pic($song->pic_id))->url;
            break;

        case 'lrc':
            $lrc_data = json_decode($api->lyric($song->lyric_id));
            if ($lrc_data->lyric == '') {
                echo '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
                exit;
            }
            if ($lrc_data->tlyric == '') {
                echo $lrc_data->lyric;
                exit;
            }
            /**
             * add lyric_cn
             * ! "\n"
             */
            $lrc_arr = explode("\n", $lrc_data->lyric);
            $lrc_cn_arr = explode("\n", $lrc_data->tlyric);
            $lrc_cn_arr2 = array();
            foreach ($lrc_cn_arr as $i => $v) {
                if ($v == '') continue;
                $lrc_cn_arr2[$i] = explode(']', $v);
                unset($lrc_cn_arr[$i]);
            }
            foreach ($lrc_arr as $i => $i_v) {
                $lrc_arr_key = explode(']', $i_v)[0];
                foreach ($lrc_cn_arr2 as $cn_i => $cn_v) {
                    if ($cn_v[0] == $lrc_arr_key && $cn_v[1] != '' && $cn_v[1] != '//') {
                        $lrc_arr[$i] .= '(' . $cn_v[1] . ')';
                        unset($lrc_cn_arr2[$cn_i]);
                    }
                }
            }
            echo implode("\n", $lrc_arr);
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

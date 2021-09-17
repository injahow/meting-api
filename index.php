<?php
// 设置API路径
define('API_URI', api_uri());
// 设置中文歌词
define('TLYRIC', true);
// 设置歌单文件缓存及时间
define('CACHE', false);
define('CACHE_TIME', 86400);
// 设置短期缓存-需要安装apcu
define('APCU_CACHE', false);
// 设置AUTH密钥-更改'meting-secret'
define('AUTH', false);
define('AUTH_SECRET', 'meting-secret');

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    include __DIR__ . '/public/index.html';
    exit;
}

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type = $_GET['type'];
$id = $_GET['id'];

if (AUTH) {
    $auth = isset($_GET['auth']) ? $_GET['auth'] : '';
    if (in_array($type, ['url', 'cover', 'lrc'])) {
        if ($auth == '' || $auth != auth($server . $type . $id)) {
            http_response_code(403);
            exit;
        }
    }
}

// 数据格式
if (in_array($type, ['single', 'playlist'])) {
    header('content-type: application/json; charset=utf-8;');
} else if (in_array($type, ['lrc'])) {
    header('content-type: text/plain; charset=utf-8;');
}

// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// include __DIR__ . '/vendor/autoload.php';
// you can use 'Meting.php' instead of 'autoload.php'
include __DIR__ . '/src/Meting.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);

// 设置cookie
/*if ($server == 'netease') {
    $api->cookie('os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; MUSIC_U=****** ; __remember_me=true');
}*/

if ($type == 'playlist') {

    if (CACHE) {
        $file_path = __DIR__ . '/cache/playlist/' . $server . '_' . $id . '.json';
        if (file_exists($file_path)) {
            if ($_SERVER['REQUEST_TIME'] - filectime($file_path) < CACHE_TIME) {
                echo file_get_contents($file_path);
                exit;
            }
        }
    }

    $data = $api->playlist($id);
    if ($data == '[]') {
        echo '{"error":"unknown playlist id"}';
        exit;
    }
    $data = json_decode($data);
    $playlist = array();
    foreach ($data as $song) {
        $playlist[] = array(
            'name'   => $song->name,
            'artist' => implode('/', $song->artist),
            'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'url' . $song->url_id) : ''),
            'cover'  => API_URI . '?server=' . $song->source . '&type=cover&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'cover' . $song->url_id) : ''),
            'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id . (AUTH ? '&auth=' . auth($song->source . 'lrc' . $song->lyric_id) : '')
        );
    }
    $playlist = json_encode($playlist);

    if (CACHE) {
        // ! mkdir /cache/playlist
        file_put_contents($file_path, $playlist);
    }

    echo $playlist;
} else {

    if (!in_array($type, ['name', 'artist', 'url', 'cover', 'lrc', 'single'])) {
        echo '{"error":"unknown type"}';
        exit;
    }

    if (APCU_CACHE) {
        $apcu_time = $type == 'url' ? 600 : 36000;
        $apcu_key = $server . $type . $id;
        if (apcu_exists($apcu_key)) {
            $data = apcu_fetch($apcu_key);
            if (in_array($type, ['url', 'cover'])) {
                header('Location: ' . $data);
            } else {
                echo $data;
            }
            exit;
        }

        $song_apcu_key = $server . 'song' . $id;
        if (apcu_exists($song_apcu_key)) {
            $song = apcu_fetch($song_apcu_key);
        }
    }

    if (!isset($song)) {
        $song = $api->song($id);
    }
    if ($song == '[]') {
        echo '{"error":"unknown song id"}';
        exit;
    }

    if (APCU_CACHE) {
        apcu_store($song_apcu_key, $song, $apcu_time);
    }

    $data = song2data($api, json_decode($song)[0], $type);

    if (APCU_CACHE) {
        apcu_store($apcu_key, $data, $apcu_time);
    }

    if (in_array($type, ['url', 'cover'])) {
        header('Location: ' . $data);
    } else {
        echo $data;
    }
}

function api_uri() // static
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
}

function auth($name)
{
    return hash_hmac('sha1', $name, AUTH_SECRET);
}

function song2data($api, $song, $type)
{
    switch ($type) {
        case 'name':

            return $song->name;
            break;

        case 'artist':

            return implode('/', $song->artist);
            break;

        case 'url':
            $m_url = json_decode($api->url($song->url_id, 320))->url;
            if ($m_url == '') {
                exit;
            }
            if ($m_url[4] != 's') {
                $m_url = str_replace('http', 'https', $m_url);
            }

            return $m_url;
            break;

        case 'cover':
            $c_url = json_decode($api->pic($song->pic_id, 90))->url;
            if ($c_url == '') {
                exit;
            }

            return $c_url;
            break;

        case 'lrc':
            $lrc_data = json_decode($api->lyric($song->lyric_id));
            if ($lrc_data->lyric == '') {
                $lrc = '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
            } else if ($lrc_data->tlyric == '') {
                $lrc = $lrc_data->lyric;
            } else if (TLYRIC) { // lyric_cn
                $lrc_arr = explode("\n", $lrc_data->lyric);
                $lrc_cn_arr = explode("\n", $lrc_data->tlyric);
                $lrc_cn_map = array();
                foreach ($lrc_cn_arr as $i => $v) {
                    if ($v == '') continue;
                    $line = explode(']', $v, 2);
                    // 格式化处理
                    $line[1] = preg_replace('/\s\s+/', ' ', $line[1]);
                    $line[1] = trim($line[1]);
                    $lrc_cn_map[$line[0]] = $line[1];
                    unset($lrc_cn_arr[$i]);
                }
                foreach ($lrc_arr as $i => $v) {
                    if ($v == '') continue;
                    $key = explode(']', $v, 2)[0];
                    if (!empty($lrc_cn_map[$key]) && $lrc_cn_map[$key] != '//') {
                        $lrc_arr[$i] .= ' (' . $lrc_cn_map[$key] . ')';
                        unset($lrc_cn_map[$key]);
                    }
                }
                $lrc = implode("\n", $lrc_arr);
            } else {
                $lrc = $lrc_data->lyric;
            }

            return $lrc;
            break;

        case 'single':

            return json_encode(array(
                'name'   => $song->name,
                'artist' => implode('/', $song->artist),
                'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'url' . $song->url_id) : ''),
                'cover'  => API_URI . '?server=' . $song->source . '&type=cover&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'cover' . $song->url_id) : ''),
                'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id . (AUTH ? '&auth=' . auth($song->source . 'lrc' . $song->lyric_id) : '')
            ));
            break;
    }
}

<?php
$type = $_GET['type'];
$id = $_GET['id'];
?>
<?php if ($type == '' || $id == ''){ ?>
<!DOCTYPE HTML>
<html>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<head>
		<link rel="shortcut icon" href="favicon.png">
		<title>163Music-API</title>
	</head>
	<body>
		<h1>参数说明</h1>
		type: 类型<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;name 歌曲名<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;artist 歌手<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;url 链接<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;cover 封面<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;lrc 歌词<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;single 获取以上所有信息(单曲)<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;playlist 获取以上所有信息(歌单)<br /><br />
		id: 网易云单曲ID或网易云歌单ID<br /><br />
		此API基于 <a href="https://github.com/metowolf/Meting" target="_blank">Meting</a> 构建。<br /><br />
		例如：<a href="https://api.injahow.cn/meting/?type=url&id=427139429" target="_blank">https://api.injahow.cn/meting/?type=url&id=427139429</a><br />
		<a href="https://api.injahow.cn/meting/?type=single&id=591321" target="_blank" style="padding-left:48px">https://api.injahow.cn/meting/?type=single&id=591321</a><br />
		<a href="https://api.injahow.cn/meting/?type=playlist&id=2619366284" target="_blank" style="padding-left:48px">https://api.injahow.cn/meting/?type=playlist&id=2619366284</a>
	</body>
</html>
<?php exit;} ?>
<?php
header('Content-type: application/json; charset=UTF-8;');//数据格式
//header('Access-Control-Allow-Origin: https://injahow.com');//跨站设置
header('Access-Control-Allow-Methods:GET');
//header('Access-Control-Allow-Headers:x-requested-with,content-type');
require 'vendor/autoload.php';
use Metowolf\Meting;
$api = new Meting('netease');
$api->format(true);

if ($type == 'playlist' ){
	$data = $api->playlist($id);
	if ($data=='[]') {echo 'ERROR';exit;}
	$data = json_decode($data);
	$msgs = array();
	
	$count_num = 0;
	$last_id = count($data);
	
	$lastid = $_GET['lastid'];
	$limit = $_GET['limit'];
	
	$lastid = $lastid == '' || $lastid == '0' ? $last_id : $lastid;
	$limit = $limit == '' ? 10 : $limit;
	//add
	foreach ($data as $msg) {
		
		if ($last_id >= $lastid) {
			--$last_id;
			continue;
		}
		if ($count_num == $limit) break;
		//$m_id = $msg->id;
		//echo $m_id;exit;
		$name = $msg->name;
		$artist_list = $msg->artist;
		$artist = implode("/",$artist_list);
		$pic_id = $msg->pic_id;
		$url_id = $msg->url_id;
		$lyric_id = $msg->lyric_id;
		$cover = json_decode($api->pic($pic_id))->url;
		
		$msg = array(
			"id" => "$last_id",
			"name"=>$name,
			"artist"=>$artist,
			"url"=>"https://api.injahow.cn/meting/?type=url&id=$url_id",
			"cover"=>$cover,
			"lrc"=>"https://api.injahow.cn/meting/?type=lrc&id=$lyric_id",
		);
		--$last_id;
		++$count_num;
		array_push($msgs, $msg);
	}
	echo json_encode($msgs);
}else{
	$msg = $api->song($id);
	//echo $msg;exit;
	if ($msg == '[]') {echo 'ERROR'; exit;}
	$msg = json_decode($msg);
	if ($type == 'name') {
		echo $msg[0]->name;
	} elseif ($type == 'artist') {
		$artist_list = $msg[0]->artist;
		$artist = implode('/', $artist_list);
		echo $artist;
	} elseif ($type == 'url') {
		$url_id = $msg[0]->url_id;
		//print_r($xxx);exit;
		$m_url = json_decode($api->url($url_id))->url;
		if ($m_url[4] != 's') {//改https
			$m_url = str_replace('http', 'https', $m_url);
		}
		header("Location: $m_url");
	} elseif ($type == 'cover') {
		$pic_id = $msg[0]->pic_id;
		echo json_decode($api->pic($pic_id))->url;
	} elseif ($type == 'lrc') {
		$lyric_id = $msg[0]->lyric_id;
		$lrc = json_decode($api->lyric($lyric_id))->lyric;
		if ($lrc == '') {$lrc = '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';}	
		echo $lrc;
	} elseif ($type == 'single') {
		$name = $msg[0]->name;
		$artist_list = $msg[0]->artist;
		$artist = implode("/",$artist_list);
		$url_id = $msg[0]->url_id;
		$pic_id = $msg[0]->pic_id;
		$cover = json_decode($api->pic($pic_id))->url;
		$lyric_id = $msg[0]->lyric_id;
		$msg = array(
			"name" => $name,
			"artist" => $artist,
			"url" => "https://api.injahow.cn/meting/?type=url&id=$url_id",
			"cover" => $cover,
			"lrc" => "https://api.injahow.cn/meting/?type=lrc&id=$lyric_id"
		);
		echo json_encode($msg);
	} else {
	echo 'ERROR';
	}
}
?>
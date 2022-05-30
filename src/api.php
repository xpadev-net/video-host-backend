<?php
require __DIR__.'/pdo.php';
if (in_array($_SERVER['HTTP_ORIGIN'], ALLOWED_DOMAIN)) {
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}
$pdo=new PDOSQL();
if($_GET["q"]=="img"){
	if (strpos($_GET["src"], "/")!==false) {
		header("Content-Type: application/json; charset=utf-8");
		echo '{"status":"fail"}';
		exit;
	}
	if (file_exists(IMG_DIR.$_GET["src"].".avif")) {
		header("Content-type: image/avif");
		echo file_get_contents(IMG_DIR.$_GET["src"].".avif");
		exit;
	}
	header("Content-type: image/png");
    if (empty($img)||$img[0]["img"]==null) {
        echo file_get_contents(__DIR__.'/../assets/noimage.png');
        exit;
    }
	echo base64_decode($img[0]["img"]);
	exit;
}
if ($_GET['q']=="recentupdate") {
	header("Content-Type: application/json; charset=utf-8");
	$series = $pdo->execute("SELECT DISTINCT `series`,`title` FROM `video_list` WHERE `series` != 'stream' ORDER BY `video_list`.`id`  DESC limit 100;");
	$data = [];
	foreach ($series as $key => $value) {
		$eps = $pdo->execute("SELECT `id`,`actual_episode` as `episode`,`url`,`title`,`ep_title`,`master_series`,`length` as `movie_length` FROM `video_list` WHERE `series` = :series ORDER BY LENGTH(`actual_episode`) DESC,`actual_episode` DESC, LENGTH(`id`) DESC,`id` DESC limit 30",array("series"=>$value["series"]));
		$data[] = [
            "series" => $value["series"],
            "title" => $value["title"],
            "eps" => $eps
        ];
	}
	echo json_encode($data);
	exit;
}elseif($_GET["q"]=="search_suggest"){
	header("Content-Type: application/json; charset=utf-8");
	$query="%".$_POST["query"]."%";
	$result=$pdo->execute("SELECT DISTINCT `title` FROM `video_list` WHERE `title` LIKE :query order by `title` asc limit 5",array("query"=>$query));
	echo json_encode($result);
	exit;
}elseif($_GET["q"]=="comments"){
	header("Content-Type: application/json; charset=utf-8");
	require_once(__DIR__.'/nicoSession.php');
	echo getApidata($_POST["query"]);
	exit;
}elseif($_GET["q"]=="search"){
	header("Content-Type: application/json; charset=utf-8");
	if (mb_strlen($_POST["query"])<1) {
		echo "[]";
		exit;
	}
	$query="%".$_POST["query"]."%";
	$result=$pdo->execute("SELECT `title`,`ep_title`,`path`,`url`,`master_series`,`actual_episode`,`length` as `movie_length` FROM `video_list` WHERE (`title` LIKE :query OR `ep_title` LIKE :query) order by length(`master_series`),`master_series`,length(`series`),`series`, LENGTH(`actual_episode`) asc,`actual_episode` asc, LENGTH(`id`) asc,`id` asc;",array("query"=>$query));
	echo json_encode($result);
	exit;
}elseif($_GET["q"]=="series"){
	header("Content-Type: application/json; charset=utf-8");
		$result["video"]=$pdo->execute("SELECT `title`,`ep_title`,`path`,`url`,`master_series`,`actual_episode`,`length` as `movie_length` FROM `video_list` WHERE `master_series` like :query AND `series` != 'stream' order by length(`master_series`),`master_series`,length(`series`),`series`, LENGTH(`actual_episode`) asc,`actual_episode` asc, LENGTH(`id`) asc,`id` asc",array("query"=>$_POST["query"]));
		if (!empty($result["video"])&&array_key_exists(0,$result["video"])) {
			$result["title"]=$result["video"][0]["title"];
		}else{
			$result["title"]=false;
		}
	echo json_encode($result);
}elseif($_GET["q"]=="video"){
	header("Content-Type: application/json; charset=utf-8");
	$url = $_GET["src"];
	$video = $pdo->execute("SELECT `id`,`master_series`,`series`,`season`,`actual_episode`,`episode`,`url`,`title`,`ep_title`,`path`,`size`,`length` as `movie_length`,`resolution_width`,`resolution_height`,`on_added` FROM `video_list` WHERE url like :url",array("url"=>$url));
	if (empty($video)||$video[0]["movie_length"]===null) {
		header("http/1.1 404 not found");
		exit;
	}
	$result["playlist"] = $pdo->execute("SELECT `title`,`ep_title`,`master_series`,`url`,`length` as `movie_length` FROM `video_list` WHERE `master_series` like :master_series AND `series` != 'stream' order by `season`,`actual_episode`",array("master_series"=>$video[0]["master_series"]));
	foreach ($result["playlist"] as $key => $value) {
		if ($value["url"]===$url) {
			$result["index"] = $key;
			break;
		}
	}
	$result["prev"] = isset($result["playlist"][$result["index"]-1])?$result["playlist"][$result["index"]-1]:null;
	$result["next"] = isset($result["playlist"][$result["index"]+1])?$result["playlist"][$result["index"]+1]:null;
	$result["video"] = $video[0];
	$result["source"]="/video.mp4?q=".$_GET["src"];
	echo json_encode($result);
	exit;
}
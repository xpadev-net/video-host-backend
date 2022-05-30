<?php
function getApidata($url)
{
	global $pdo;
	$urls = $pdo->execute("SELECT `targeturl`,`targetdurl`,`targetoffset`,`targetdoffset` FROM `niconico_database` WHERE `baseurl` = :url",array("url"=>$_POST["query"]));
	if(empty($urls)){
		return "[]";
	}
	$query = $urls[0]["targeturl"];
	$queryd = $urls[0]["targetdurl"];
	if(empty($query)){
		$commentData = array();
	}else{
		$comment = $pdo->execute("SELECT `comment` FROM `niconico_comment` WHERE `url` = :query AND `archive_on` > :time",array("query" => $query,"time" => time() - 604800));
		if (!empty($comment)&&!empty($comment[0])&&!empty($comment[0]["comment"])) {
			$commentData = json_decode($comment[0]["comment"],true);
		}
		if ($urls[0]["targetoffset"]!=0) {
			foreach ($commentData as $key => $value) {
				if (isset($commentData[$key]["chat"])) {
					$commentData[$key]["chat"]["vpos"]+=$urls[0]["targetoffset"]*100;
				}
			}
		}
	}
	if(empty($queryd)){
		$commentDData = array();
	}else{
		$commentD = $pdo->execute("SELECT `comment` FROM `niconico_comment` WHERE `url` = :query AND `archive_on` > :time",array("query" => $queryd,"time" => time() - 604800));
		if (!empty($commentD)&&!empty($commentD[0])&&!empty($commentD[0]["comment"])) {
			$commentDData = json_decode($commentD[0]["comment"],true);
		}
		if ($urls[0]["targetdoffset"]!=0) {
			foreach ($commentDData as $key => $value) {
				if (isset($commentDData[$key]["chat"])) {
					$commentDData[$key]["chat"]["vpos"]+=$urls[0]["targetoffset"]*100;
				}
			}
		}
	}
	if(isset($commentData)&&isset($commentDData)){
		$response_=array_merge($commentData,$commentDData);
		$APIResponse = array();
		$tmp = array();
		foreach( $response_ as $key => $value ){
			if (isset($value["chat"])&&!in_array( $value['chat']['thread']."@".$value['chat']['vpos'].$value['chat']['date'].$value['chat']['date_usec']."@".$value['chat']['no'], $tmp )) {
				if ($value['chat']["vpos"]<0) {
					$value['chat']["vpos"]=0;
				}
				array_push($APIResponse,$value);
				array_push($tmp,$value['chat']['thread']."@".$value['chat']['vpos'].$value['chat']['date'].$value['chat']['date_usec']."@".$value['chat']['no']);
			}
		}
		return json_encode($APIResponse);
	}

	$tmp_path=tempnam(sys_get_temp_dir(), 'nicosession');
	$base_url = 'https://account.nicovideo.jp/login';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $base_url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_COOKIEFILE,$tmp_path);
	curl_setopt($curl, CURLOPT_COOKIEJAR, $tmp_path);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Host: www.nicovideo.jp","User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0","Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8","Accept-Language: ja,en;q=0.7,en-US;q=0.3","Accept-Encoding: gzip, deflate, br","DNT: 1","Connection: keep-alive","Upgrade-Insecure-Requests: 1","Sec-Fetch-Dest: document","Sec-Fetch-Mode: navigate","Sec-Fetch-Site: none","Sec-Fetch-User: ?1","Pragma: no-cache","Cache-Control: no-cache"));
	$response = curl_exec($curl);
	$header = substr($response, 0, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
	$cookies = array();
	foreach($matches[1] as $item) {
	    parse_str($item, $cookie);
	    $cookies=array_merge($cookies,$cookie);
	}
	$fp = tmpfile();
	ob_start();
	$base_url = 'https://account.nicovideo.jp/login/redirector?show_button_twitter=1&site=niconico&show_button_facebook=1&sec=header_pc&next_url=/';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $base_url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl, CURLOPT_POSTFIELDS, "mail_tel=".urlencode(NICONICO_EMAIL)."&password=".urlencode(NICONICO_PASSWORD)."&auth_id=0");
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_COOKIEFILE,$tmp_path);
	curl_setopt($curl, CURLOPT_COOKIEJAR, $tmp_path);
	curl_setopt($curl, CURLOPT_VERBOSE, true);
	curl_setopt($curl, CURLOPT_STDERR, $fp);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Host: www.nicovideo.jp","User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0","Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8","Accept-Language: ja,en;q=0.7,en-US;q=0.3","Accept-Encoding: gzip, deflate, br","DNT: 1","Connection: keep-alive","Upgrade-Insecure-Requests: 1","Sec-Fetch-Dest: document","Sec-Fetch-Mode: navigate","Sec-Fetch-Site: none","Sec-Fetch-User: ?1","Pragma: no-cache","Cache-Control: no-cache"));
	$response = curl_exec($curl);
	fseek($fp, 0);
	$header = fread($fp, 2048);
	fclose($fp);
	ob_end_clean();
	preg_match_all('/^< Set-Cookie:\s*([^;]*)/mi', $header, $matches);
	foreach($matches[1] as $item) {
	    parse_str($item, $cookie);
	    $cookies=array_merge($cookies,$cookie);
	}
	$base_url = 'https://www.nicovideo.jp/';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $base_url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, true);   // ヘッダーも出力する
	curl_setopt($curl, CURLOPT_COOKIEFILE,$tmp_path);
	curl_setopt($curl, CURLOPT_COOKIEJAR, $tmp_path);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Host: www.nicovideo.jp","User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0","Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8","Accept-Language: ja,en;q=0.7,en-US;q=0.3","Accept-Encoding: gzip, deflate, br","DNT: 1","Connection: keep-alive","Upgrade-Insecure-Requests: 1","Sec-Fetch-Dest: document","Sec-Fetch-Mode: navigate","Sec-Fetch-Site: none","Sec-Fetch-User: ?1","Pragma: no-cache","Cache-Control: no-cache"));
	$response = curl_exec($curl);
	$header = substr($response, 0, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
	foreach($matches[1] as $item) {
	    parse_str($item, $cookie);
	    $cookies=array_merge($cookies,$cookie);
	}
	$cookie_arr=[];
	foreach ($cookies as $key => $value) {
		$cookie_arr[] = $key."=".$value;
	}
	if(!isset($commentData)){
		$commentData=getCommentReqest($query,$cookie_arr,$tmp_path,$pdo);
		if ($urls[0]["targetoffset"]!=0) {
			foreach ($commentData as $key => $value) {
				if (isset($commentData[$key]["chat"])) {
					$commentData[$key]["chat"]["vpos"]+=$urls[0]["targetoffset"]*100;
				}
			}
		}
	}
	if(!isset($commentDData)){
		$commentDData=getCommentReqest($queryd,$cookie_arr,$tmp_path,$pdo);
		if ($urls[0]["targetdoffset"]!=0) {
			foreach ($commentDData as $key => $value) {
				if (isset($commentDData[$key]["chat"])) {
					$commentDData[$key]["chat"]["vpos"]+=$urls[0]["targetoffset"]*100;
				}
			}
		}
	}
	unlink($tmp_path);

	$response_=array_merge($commentData,$commentDData);
	$APIResponse = array();
	$tmp = array();
	foreach( $response_ as $key => $value ){
		if (isset($value["chat"])&&!in_array( $value['chat']['thread']."@".$value['chat']['no'], $tmp )) {
			if ($value['chat']["vpos"]<0) {
				$value['chat']["vpos"]=0;
			}
			array_push($APIResponse,$value);
			array_push($tmp,$value['chat']['thread']."@".$value['chat']['no']);
		}
	}
	return json_encode($APIResponse);
}
function getCommentReqest($url,$cookie,$path,$pdo){
	$base_url = 'https://www.nicovideo.jp/api/watch/v3/'.$url.'?_frontendId=6&_frontendVersion=0&actionTrackId=a_0';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $base_url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

	curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

	$headers = array();
	$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0';
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
	$headers[] = 'Accept-Language: ja,en;q=0.7,en-US;q=0.3';
	$headers[] = 'Referer: https://account.nicovideo.jp/';
	$headers[] = 'Dnt: 1';
	$headers[] = 'Connection: keep-alive';
	$headers[] = 'Upgrade-Insecure-Requests: 1';
	$headers[] = 'Sec-Fetch-Dest: document';
	$headers[] = 'Sec-Fetch-Mode: navigate';
	$headers[] = 'Sec-Fetch-Site: same-site';
	$headers[] = 'Sec-Fetch-User: ?1';
	$headers[] = 'Pragma: no-cache';
	$headers[] = 'Cache-Control: no-cache';
	$headers[] = 'Cookie: '.implode(";", $cookie);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_COOKIEFILE,$path);
	curl_setopt($curl, CURLOPT_COOKIEJAR, $path);

	$json = curl_exec($curl);
	$apidata = json_decode($json,true)["data"];
	$CommentApiParam = array();
	foreach ($apidata["comment"]["threads"] as $key => $value) {
		if (!$value["isActive"]) {
			continue;
		}
		$tmpParamThread = array(
			"thread"=>strval($value["id"]),
			"version"=>"20090904",
			"fork"=>$value["fork"],
			"language"=>0,
			"user_id"=>strval($apidata["viewer"]["id"]),
			"with_global"=>1,
			"scores"=>1,
			"nicoru"=>3
		);
		$tmpParam = array(
			"thread"=>strval($value["id"]),
			"fork"=>$value["fork"],
			"language"=>0,
			"user_id"=>strval($apidata["viewer"]["id"]),
			"scores"=>1,
			"nicoru"=>3
		);
		if (array_key_exists("threadkey", $value)&&!empty($value["threadkey"])) {
			$tmpParam["threadkey"]=$value["threadkey"];
			$tmpParamThread["threadkey"]=$value["threadkey"];
		}else{
			$tmpParam["userkey"]=$apidata["comment"]["keys"]["userKey"];
			$tmpParamThread["userKey"]=$apidata["comment"]["keys"]["userKey"];
		}
		if (array_key_exists("is184Forced",$value)&&$value["is184Forced"]==true) {
			$tmpParam["force_184"]="1";
			$tmpParamThread["force_184"]="1";
		}
		if ($value["label"]=="easy") {
			$tmpParam["content"]="0-24:25,250,nicoru:100";
		} else {
			$tmpParam["content"]="0-24:100,1000,nicoru:100";
		}
		if ($value["fork"]!==1) {
			array_push($CommentApiParam, array("thread_leaves"=>$tmpParam));
		}else{
			$tmpParamThread["res_from"] = -1000;
			unset($tmpParamThread["force_184"]);
		}
		array_push($CommentApiParam, array("thread"=>$tmpParamThread));
	}
	$base_url = $apidata["comment"]["threads"][0]["server"]."/api.json";
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $base_url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($CommentApiParam));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$APIResponse = json_decode(curl_exec($curl),true);
	$comment = $pdo->execute("SELECT `comment` FROM `niconico_comment` WHERE `url` = :query",array("query" => $url));
	if (empty($comment)) {
		$pdo->execute("INSERT INTO `niconico_comment` (`id`, `url`, `comment`, `archive_on`) VALUES (NULL, :query, '[]', 0)", array("query"=>$url));
	}else{
		$response_ = array_merge(json_decode($comment[0]["comment"],true),$APIResponse);
		$APIResponse = array();
		$tmp = array();
		foreach( $response_ as $key => $value ){
			if (isset($value["chat"])&&!in_array( $value['chat']['thread']."@".$value['chat']['vpos'].$value['chat']['date'].$value['chat']['date_usec']."@".$value['chat']['no'], $tmp )) {
				array_push($APIResponse,$value);
				array_push($tmp,$value['chat']['thread']."@".$value['chat']['vpos'].$value['chat']['date'].$value['chat']['date_usec']."@".$value['chat']['no']);
			}
		}
	}
	$pdo->execute("UPDATE `niconico_comment` SET `comment` = :comment WHERE `niconico_comment`.`url` = :query; ", array("query"=>$url,"comment"=>json_encode($APIResponse)));
	$pdo->execute("UPDATE `niconico_comment` SET `archive_on` = :archive_on WHERE `niconico_comment`.`url` = :query; ", array("query"=>$url,"archive_on"=>time()));
	return $APIResponse;
}

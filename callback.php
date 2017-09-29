<?php

$accessToken = 'ruBTtw8ReOZZXK7SN8j/Z3q4xlL8NwNmSpOUBwt+z+1nM/SCM7/t7DxIdxMn62EDfPHK0vOPxvcyFwpEeQ/LYVFUyA+q6Hl9Gh7/1gdR7NC8fDPTQCd14VlKusWX8jwwwvzwkP4GCY4IyphjskonAgdB04t89/1O/w1cDnyilFU=';

$jsonString = file_get_contents('php://input');
error_log($jsonString);
$jsonObj = json_decode($jsonString);

$message = $jsonObj->{"events"}[0]->{"message"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$beacon = $jsonObj->{"events"}[0]->{"beacon"};

// 送られてきたメッセージの中身からレスポンスのタイプを選択
if (strpos($message->{"text"},'sample') !== false || strpos($message->{"text"},'サンプル') !== false) {
  // それ以外は送られてきたテキストをオウム返し
  $messageData = [
      'type' => 'text',
      'text' => 'サンプル'
  ];
} elseif (strpos($message->{"text"},'貯金') !== false ) {
require './vendor/autoload.php';
Predis\Autoloader::register();
$redis = new Predis\Client(getenv('REDIS_URL'));

$m = 500;
//$redis->set('otu', $m);
$data = $redis->get('otu');
$sum = $data + $m;
$v = $redis->set('otu', $sum);
  $messageData = [
      'type' => 'text',
      'text' => '貯金'.$sum.'円！'
  ];

} elseif (strpos($message->{"text"},'合計') !== false || strpos($message->{"text"},'ごうけい') !== false) {
require './vendor/autoload.php';
Predis\Autoloader::register();
$redis = new Predis\Client(getenv('REDIS_URL'));

$m = 500;
//$redis->set('otu', $m);
$data = $redis->get('nao');
$otu_data = $redis->get('otu');
$sum = $data + $otu_data;

  $messageData = [
      'type' => 'text',
      'text' => '合計'.$sum.'円'
  ];
} elseif (strpos($message->{"text"},'教えて') !== false || strpos($message->{"text"},'おしえて') !== false) {
require_once('phpQuery-onefile.php');
$html = file_get_contents('http://www.will-be.co.jp/ikejiri_family.html?grid=rosen');
$doc = phpQuery::newDocument($html);
$titleArr = $doc[".blogbox_710"]->find("td");
$arr = Array();
foreach($titleArr as $val) {
 $arr += Array(pq($val)->text() => pq($val)->find("a")->attr("href"));
}

$today = date("n/j");
$v = Array();
foreach($arr as $key => $aaa) {
 if(strpos($key,$taday) !== false){
 $v += Array($aaa);
 }
}

$ans = Array();
if (empty($v)){
  $ans += Array("今日は新作物件はない");
} else {
 $ans += '今日の新着物件 '.$v;
}

  $messageData = [
      'type' => 'text',
      'text' => $ans[0]
  ];
} else {
    // それ以外は送られてきたテキストをオウム返し
    $messageData = [
        'type' => 'text',
        'text' => $message->{"text"}
    ];
}

$response = [
    'replyToken' => $replyToken,
    'messages' => [$messageData]
];
error_log(json_encode($response));

$ch = curl_init('https://api.line.me/v2/bot/message/reply');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
));
$result = curl_exec($ch);
error_log($result);
curl_close($ch);

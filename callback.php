<?php

$accessToken = 'アクセストークン';

$jsonString = file_get_contents('php://input');
error_log($jsonString);
$jsonObj = json_decode($jsonString);

$message = $jsonObj->{"events"}[0]->{"message"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$beacon = $jsonObj->{"events"}[0]->{"beacon"};

if (strpos($message->{"text"},'sample') !== false || strpos($message->{"text"},'サンプル') !== false) {
  $messageData = [
      'type' => 'text',
      'text' => 'サンプル'
  ];
} elseif (strpos($message->{"text"},'貯金') !== false ) {
require './vendor/autoload.php';
Predis\Autoloader::register();
$redis = new Predis\Client(getenv('REDIS_URL'));

$m = 500;
$data = $redis->get('o');
$sum = $data + $m;
$v = $redis->set('o', $sum);
  $messageData = [
      'type' => 'text',
      'text' => '貯金'.$sum.'円！'
  ];

} elseif (strpos($message->{"text"},'合計') !== false || strpos($message->{"text"},'ごうけい') !== false) {
require './vendor/autoload.php';
Predis\Autoloader::register();
$redis = new Predis\Client(getenv('REDIS_URL'));

$m = 500;
$otu_data = $redis->get('o');
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

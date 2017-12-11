<?php

$accessToken = 'トークン';
$jsonString = file_get_contents('php://input');

error_log($jsonString);
$jsonObj = json_decode($jsonString);

$message = $jsonObj->{"events"}[0]->{"message"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$beacon = $jsonObj->{"events"}[0]->{"beacon"};

if (strpos($beacon->{"type"},'enter') !== false) {

  $messageData = [
      'type' => 'text',
      'text' => '到着！'
  ];
} elseif (strpos($message->{"text"},'貯金') !== false ) {
  require './vendor/autoload.php';
  Predis\Autoloader::register();
  $redis = new Predis\Client(getenv('REDIS_URL'));

  //500円貯金用
  $m = 500;

  $data = $redis->get('otu');
  $sum = $data + $m;
  $v = $redis->set('otu', $sum);
    $messageData = [
        'type' => 'text',
        'text' => '貯金'.$sum.'円！'
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
    $ans += Array("新作物件なし");
  } else {
    $ans += '新着物件'.$v;
  }
  $messageData = [
    'type' => 'text',
    'text' => $ans[0]
  ];
} elseif ($message->{"text"} == 'ボタン') {
  // ボタンタイプ
  $messageData = [
      'type' => 'template',
      'altText' => 'ボタン',
      'template' => [
          'type' => 'buttons',
          'title' => 'タイトルです',
          'text' => '選択してね',
          'actions' => [
              [
                  'type' => 'postback',
                  'label' => 'webhookにpost送信',
                  'data' => 'value'
              ],
              [
                  'type' => 'uri',
                  'label' => 'googleへ移動',
                  'uri' => 'https://google.com'
              ]
          ]
      ]
  ];
} elseif ($message->{"text"} == 'カルーセル') {
  $messageData = [
      'type' => 'template',
      'altText' => 'カルーセル',
      'template' => [
          'type' => 'carousel',
          'columns' => [
              [
                  'title' => 'カルーセル1',
                  'text' => 'カルーセル1です',
                  'actions' => [
                      [
                          'type' => 'postback',
                          'label' => 'webhookにpost送信',
                          'data' => 'value'
                      ],
                      [
                          'type' => 'uri',
                          'label' => 'google',
                          'uri' => 'http://google.com'
                      ]
                  ]
              ],
              [
                  'title' => 'カルーセル2',
                  'text' => 'カルーセル2です',
                  'actions' => [
                      [
                          'type' => 'postback',
                          'label' => 'webhookにpost送信',
                          'data' => 'value'
                      ],
                      [
                          'type' => 'uri',
                          'label' => 'テスト',
                          'uri' => 'https://google.com/'
                      ]
                  ]
              ],
          ]
      ]
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

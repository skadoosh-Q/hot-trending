<?php
date_default_timezone_set('Asia/Shanghai');
header("Content-type:application/json; charset=utf-8");

function Curl_ssp($url, $header = [
    "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
    "Accept-Encoding: gzip, deflate, br",
    "Accept-Language: zh-CN,zh;q=0.9",
    "Connection: keep-alive",
    "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1"
  ], $cookie = null, $refer = 'https://www.baidu.com')
  {
    $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
    $header[] = "CLIENT-IP:" . $ip;
    $header[] = "X-FORWARDED-FOR:" . $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //设置传输的 url
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //发送 http 报头
    curl_setopt($ch, CURLOPT_COOKIE, $cookie); //设置Cookie
    curl_setopt($ch, CURLOPT_REFERER,  $refer); //设置Referer
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // 解码压缩文件
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
  }
  
function sspai()
  {
    $jsonRes = json_decode(Curl_ssp('https://sspai.com/api/v1/article/tag/page/get?limit=100000&tag=%E7%83%AD%E9%97%A8%E6%96%87%E7%AB%A0', null, null, "https://sspai.com"), true);
    $tempArr = [];
    foreach ($jsonRes['data'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['title'],
        'createdAt' => date('Y-m-d', $v['released_time']),
        'other' => $v['author']['nickname'],
        'like_count' => $v['like_count'],
        'comment_count' => $v['comment_count'],
        'url' => 'https://sspai.com/post/'.$v['id'],
        'mobilUrl' => 'https://sspai.com/post/'.$v['id']
      ]);
    }
    return [
      'success' => true,
      'title' => '少数派',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
	$_res = sspai();
	$json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json = str_replace('\/', '/', $json);
	echo $json;
}

?>
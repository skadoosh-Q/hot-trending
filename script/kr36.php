<?php
date_default_timezone_set('Asia/Shanghai');
header("Content-type:application/json; charset=utf-8");

function curl_kr36($url, $header = [
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

function startfun()
{
  $today = date("Y-m-d");
  $html = curl_kr36("https://36kr.com/hot-list/renqi/{$today}/1", null, null, "36kr.com");

    if ($html) {
    $encoding = mb_detect_encoding($html, 'UTF-8, GBK');
    $html = mb_convert_encoding($html, 'UTF-8', $encoding);
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $articleWrappers = $xpath->query('//div[@class="article-list"]/div[contains(@class, "article-wrapper")]');

    $results = [];
    foreach ($articleWrappers as $index => $wrapper) {
      if ($index >= 10) break;

      $descriptionLink = '';
      $descriptionContent = '';

      $linkNode = $xpath->query('//p[contains(@class, "title-wrapper")]/a[contains(@class, "article-item-title")]', $wrapper)[0];
      if ($linkNode) {
        $descriptionLink = $linkNode->getAttribute('href');
        $descriptionContent = $linkNode->nodeValue; // 或者 $linkNode->textContent;
      }

      $results[] = [
        'link' => $descriptionLink,
        'title' => $descriptionContent,
      ];
    }
  }

  $tempArr = [];

  if ($results) {
    foreach ($results as $index => $v) {
      array_push($tempArr, [
        'index' => $index + 1,
        'title' => $v['title'],
        'url' => $v['link'] ? 'https://36kr.com' . $v['link'] : '',
      ]);
    }
  }

  return [
    'success' => true,
    'title' => '36氪',
    'subtitle' => '热榜',
    'update_time' => date('Y-m-d H:i:s', time()),
    'data' => $tempArr
  ];
}

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
  $_res = startfun();
  $json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  $json = str_replace('\/', '/', $json);
  echo $json;
}

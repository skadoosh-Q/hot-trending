<?php
date_default_timezone_set('Asia/Shanghai');
header("Content-type:application/json; charset=utf-8");

/**
 * 豆瓣小组
 */
function curl_douban_group($url, $header = [
  "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
  "Accept-Encoding: gzip, deflate, br",
  "Accept-Language: zh-CN,zh;q=0.9",
  "Connection: keep-alive",
  "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1"
], $cookie = null, $refer = 'https://www.baidu.com')
{
  $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
  // $header[] = "CLIENT-IP:" . $ip;
  // $header[] = "X-FORWARDED-FOR:" . $ip;
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
  $html = curl_douban_group(
    "https://www.douban.com/group/explore", 
    [
      "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
      "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,ru;q=0.7,id;q=0.6",
      "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
      'sec-ch-ua: "Not/A)Brand";v="8", "Chromium";v="126", "Google Chrome";v="126"',
      'sec-ch-ua-mobile: ?0',
      'sec-ch-ua-platform: "macOS"',
      'sec-fetch-dest: document',
      'sec-fetch-mode: navigate',
      'sec-fetch-site: none',
      'sec-fetch-user: ?1'
    ], 
    'bid=YyOA1j2BW9Q; _pk_id.100001.8cb4=3dfb7925bee17a61.1710745804.; ll="118281"; _ga=GA1.1.1186810724.1716800023; ap_v=0,6.0; __utma=30149280.1141111159.1710745807.1716799958.1720578131.6; __utmc=30149280; __utmz=30149280.1720578131.6.6.utmcsr=baidu|utmccn=(organic)|utmcmd=organic; _pk_ref.100001.8cb4=%5B%22%22%2C%22%22%2C1720578137%2C%22https%3A%2F%2Fwww.baidu.com%2Flink%3Furl%3DGPTT5bB2zKR31oIA06cF3iSg2InxqdX0ZJ-INEphVklx6PEKsY2hRWv2A5iadrEt79EeB--NmEkc6YvqDMSyYq%26wd%3D%26eqid%3Da716c7ec005ac85000000006668df048%22%5D; _pk_ses.100001.8cb4=1; loc-last-index-location-id="118281"; _ga_RXNMP372GL=GS1.1.1720578263.2.0.1720578265.58.0.0; _vwo_uuid_v2=DE670393D4F252B1067F4D4026217DDE2|c78af67049ea26ec51f7f61c70bc996b; __utmt=1; __utmb=30149280.21.5.1720578332061',
    "www.douban.com"
  );
  if ($html) {
    $encoding = mb_detect_encoding($html, 'UTF-8, GBK');
    $html = mb_convert_encoding($html, 'UTF-8', $encoding);
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $domList = $xpath->query('//div[@class="channel-item"]');

    $results = [];
    foreach ($domList as $index => $wrapper) {
      if ($index >= 20) break;

      $descriptionLink = '';
      $descriptionContent = '';

      $linkNode = $xpath->query('.//div[@class="bd"]/h3/a', $wrapper)[0];
      if ($linkNode) {
        $descriptionLink = $linkNode->getAttribute('href');
        $descriptionContent = $linkNode->nodeValue; // 或者 $linkNode->textContent;
      }

      $results[] = [
        'link' => $descriptionLink,
        'title' => $descriptionContent,
      ];
      unset($wrapper);
    }
  }

  $tempArr = [];

  if ($results) {
    foreach ($results as $index => $v) {
      array_push($tempArr, [
        'index' => $index + 1,
        'title' => $v['title'],
        'url' => $v['link'],
      ]);
    }
  }

  return [
    'success' => true,
    'title' => '豆瓣小组',
    'subtitle' => '豆瓣小组',
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

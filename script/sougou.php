<?php
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: application/json; charset=utf-8');
function sougou(){
	$urls = "https://www.sogou.com/web?query=%E6%90%9C%E7%8B%97%E7%83%AD%E6%90%9C";
	$context = stream_context_create([
	    "http" => [
	        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
	    ],
	]);
	
	$html = file_get_contents($urls, false, $context);
	
	// 获取响应头中的Content-Type信息
	$encoding = mb_detect_encoding($html, 'UTF-8, GBK');
	$html = mb_convert_encoding($html, 'UTF-8', $encoding);

	// 使用DOMDocument加载HTML字符串
	$dom = new DOMDocument();
	libxml_use_internal_errors(true); // 禁用错误报告，避免因HTML格式问题报错
	$dom->loadHTML($html);
	libxml_clear_errors(); // 清除之前禁用错误报告时可能产生的错误信息
	$xpath = new DOMXPath($dom);

	// 使用XPath查询class为"right"的div元素
	$divs = $xpath->query('//div[@class="right"]');
	// var_dump($divs);
	// die;
	$hotDom = $divs[0] ? $dom->saveXML($divs[0]) : '';
	$hotDom = str_replace('&#13;', '', $hotDom); // 移除&#13;
	// var_dump($hotDom);
	// die;

	// 正则表达式
	$pattern = '/<p>\s*<a href="(.*?)"[^>]*>(.*?)<\/a>\s*<\/p>/';
	preg_match_all($pattern, $hotDom, $matches, PREG_SET_ORDER);

	$results = [];
	foreach ($matches as $index => $match) {
		// 删除空格
	    $title = $match[2];
	    $url = $match[1];
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    		'index' => $index+1,
	        'title' => $title,
	        'url' => $url,
	    ];
}
 	return  [
      'success' => true,
      'title' => '搜狗',
      'subtitle' => '搜狗热搜榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
}

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
	$_res = sougou();
	$json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json = str_replace('\/', '/', $json);
	echo $json;
}
?>

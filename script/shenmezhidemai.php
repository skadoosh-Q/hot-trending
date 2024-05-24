<?php
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: application/json; charset=utf-8');
function smzdm()
{
    $urls = "https://www.smzdm.com/";
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

    // 提取class为`feed-hot-wrap-haojia`的节点
    $feedHotWrapNodes = $xpath->query('//div[contains(@class, "feed-hot-wrap-haojia")]')[0];

    $feedHotLists = $feedHotWrapNodes->getElementsByTagName('div');
    $results = [];
    foreach ($feedHotLists as $feedHotList) {
        if (in_array('feed-hot-list', explode(' ', $feedHotList->getAttribute('class')))) {
            // 获取a标签的href链接
            $aTags = $feedHotList->getElementsByTagName('a');
            foreach ($aTags as $aTag) {
                $href = $aTag->getAttribute('href');

                $feedHotTitles = $aTag->getElementsByTagName('div');
                foreach ($feedHotTitles as $feedHotTitle) {
                    if (in_array('feed-hot-title', explode(' ', $feedHotTitle->getAttribute('class')))) {
                        $title = $feedHotTitle->textContent;
                    }
                }

                $zHighlights = $aTag->getElementsByTagName('span');
                foreach ($zHighlights as $zHighlight) {
                    if (in_array('z-highlight', explode(' ', $zHighlight->getAttribute('class')))) {
                        $highlight = $zHighlight->textContent;
                    }
                }

                $results[] = [
                    'href' => $href,
                    'title' => $title,
                    'highlight' => $highlight
                ];
            }
        }
    }

    // var_dump($results);die;

    $resultsContent = [];
    foreach ($results as $index => $result) {
        // 删除空格
        $title = $result['title'];
        $url = $result['href'];

        $resultsContent[] = [
            'index' => $index + 1,
            'url' => $url,
            'title' => $title,
            'price' => $result['highlight']
        ];
    }
    return  [
        'success' => true,
        'title' => '什么值得买',
        'subtitle' => '好价排行榜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $resultsContent
    ];
}

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $_res = smzdm();
    $json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $json = str_replace('\/', '/', $json);
    echo $json;
}

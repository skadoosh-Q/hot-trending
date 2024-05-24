<?php
date_default_timezone_set('Asia/Shanghai');
header("Content-type:application/json; charset=utf-8");

$resultDirectory = __DIR__ . '/../articles';

function returnJsonFail($msg = 'fail', $data = null, $code = 500) {
    $_res = [
        "code" => $code,
        "msg" => $msg,
        "data" => $data
    ];
    $json = json_encode($_res, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    echo $json;
    exit();
}

function returnJsonSuccess($data = null, $msg='success', $code = 200) {
    $_res = [
        "code" => $code,
        "msg" => $msg,
        "data" => $data
    ];
    // $json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $json = json_encode($_res, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    echo $json;
    exit();
}

function getAllData() {
    global $resultDirectory;
    $allResultData = [];

    // 检查目录
    if (is_dir($resultDirectory)) {
        // 设置文件名
        $dateYearMonth = date('Y-m');
        $dateDay = date('d');
        $pathYearMonth = $resultDirectory . '/' . $dateYearMonth;
        $pathYearMonthDay = $pathYearMonth . '/' . $dateDay;
        if (!is_dir($pathYearMonthDay)) {
            returnJsonFail("今日热点数据未抓取");
        }

        if ($handle = opendir($pathYearMonthDay)) {
            // 循环读取目录中的文件
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    // 获取文件路径
                    $filePath = $pathYearMonthDay . '/' . $file;
                    $fileInfo = pathinfo($filePath);
                    $fileName = $fileInfo['filename'];
                    if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) == 'json') {
                        $allResultData[$fileName] = $filePath;
                    }
                }
            }
            closedir($handle);
        } else {
            returnJsonFail("查找今日热点数据失败");
        }
    } else {
        returnJsonFail("热点数据不存在");
    }
    return $allResultData;
}

function mergeHotData($hotDataPathList) {
    $mergeHotDataMap = [];
    foreach ($hotDataPathList as $fileName => $resultData) {
        $jsonContent = file_get_contents($resultData);
        if ($jsonContent === false) {
            // 读取文件内容失败
            // echo '读取文件内容失败' . $fileName . PHP_EOL;
            continue;
        }

        $hotData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // 解析json错误
            // echo '解析json错误' . $fileName . PHP_EOL;
            continue;
        }

        if (!$hotData || !isset($hotData['data']) || !$hotData['data']) {
            // 没数据结果
            // echo '没数据结果' . $fileName . PHP_EOL;
            continue;
        }

        $hotData['key'] = $fileName;
        $mergeHotDataMap[] = $hotData;
    }
    return $mergeHotDataMap;
}

$resultDataPath = getAllData();
$allData = mergeHotData($resultDataPath);
returnJsonSuccess($allData);

?>
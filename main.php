<?php
/**
 * 执行命令: 
 * php ./main.php > "./logs/$(date +'%Y-%m-%d_%H-%M-%S').log"
 */
date_default_timezone_set('Asia/Shanghai');

/** 单进程执行 */
// include_once(__DIR__ . '/FetchHotData.php');
// new FetchHotData();

/** 多进程执行 */
include_once(__DIR__ . '/FetchHotDataMulti.php');
new FetchHotDataMulti();

?>
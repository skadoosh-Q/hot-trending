<?php

// 设置时区
date_default_timezone_set('Asia/Shanghai');

/**
 * 互联网热点数据获取
 * 测试命令: php ./FetchHotData.php test checkEmpty
 * 参数说明: 
 * - test是直接运行本脚本
 * - checkEmpty是仅执行 空数据重试 方法
 */
class FetchHotData {

    /**
     * .env文件设置的环境变量
     */
    protected $SYS_ENV = [];

    /**
     * 脚本目录
     */
    protected $scriptDir = __DIR__ . '/script';

    /**
     * 数据储存目录
     */
    protected $resultDirectory = __DIR__ . '/articles';

    /**
     * 黑名单数组; 要跳过执行的脚本
     */
    protected $blacklist = [ 'all.php' ];

    public $allFileScript = [];

    public function __construct()
    {   
        // 命令行中的参数
        global $argv, $argc;
        $argvList = [];
        for ($i = 1; $i < count($argv); $i++) { // 跳过索引0，它是脚本名
            $argvList[] = $argv[$i];
        }
        // var_dump($argvList);die;

        $this->startLoadEnv();

        if (in_array('checkEmpty', $argvList)) {
            // 仅执行检查
            $this->cheackEmptyRetry();
            return;
        }

        // 程序正常流程开始
        $this->getAllScript();
        $this->startApp();
        $this->cheackEmptyRetry();
    }

    public function startLoadEnv() {
        $defEnvDir = __DIR__ . '/.env';
        $productionEnvDir = __DIR__ . '/.env.production';
        $developmentEnvDir = __DIR__ . '/.env.development';
        $reEnv1 = [];
        if (file_exists($productionEnvDir)) {
            $reEnv1 = $this->parseFileEnv($productionEnvDir);
        } else if (file_exists($developmentEnvDir)) {
            $reEnv1 = $this->parseFileEnv($developmentEnvDir);
        }

        $defEnv = $this->parseFileEnv($defEnvDir);
        $this->SYS_ENV = array_merge($defEnv, $reEnv1);
    }

    public function parseFileEnv($filePath) {
        if (!file_exists($filePath)) { return false; }
        $envRes = [];
      
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
          if (strpos(trim($line), '#') === 0) { continue; }
      
          if (preg_match('/\A([^\s=]+)\s*=\s*(["\']?)(.*)\2\z/', $line, $matches)) {
            $key = trim($matches[1]);
            $value = trim($matches[3], "\"'");
            $envRes[$key] = $value;
          }
        }
        return $envRes;
    }

    public function startApp() 
    {
        $countScriptNum = count($this->allFileScript);
        echo '获取所有脚本, 共: ' . $countScriptNum . '个' . PHP_EOL;
        // echo implode("\n", $this->allFileScript) . "\n";
        
        $this->startFetchHotData(0, $countScriptNum === 0 ? false : $countScriptNum - 1);
    }

    /**
     * 获取指定目录下所有脚本
     */
    public function getAllScript() {
        // 检查目录是否存在
        if (is_dir($this->scriptDir)) {
            // 打开目录
            if ($handle = opendir($this->scriptDir)) {
                // 循环读取目录中的文件
                while (false !== ($file = readdir($handle))) {
                    // 跳过当前目录和上级目录的指针
                    if ($file != '.' && $file != '..') {
                        // 获取文件路径
                        $filePath = $this->scriptDir . '/' . $file;
                        
                        // 检查文件是否为PHP文件并且不在黑名单中
                        if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) == 'php' && !in_array($file, $this->blacklist)) {
                            $this->allFileScript[] = $filePath;
                        }
                    }
                }
                // 关闭目录句柄
                closedir($handle);
            } else {
                echo "无法打开目录: $this->scriptDir" . PHP_EOL;
                echo "停止执行脚本";
                exit();
            }
        } else {
            echo "目录不存在: $this->scriptDir" . PHP_EOL;
            echo "停止执行脚本";
            exit();
        }

    }

    /**
     * 记录错误内容日志; 按日期+当前具体时间 起文件名
     */
    public function writeErrorLog($errorContent = '') {
        // 获取当前日期和时间
        $currentDateTime = date('Y-m-d_H-i-s');

        // 设置日志目录
        $logDirectory = './logs';

        // 确保日志目录存在
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        // 设置日志文件名
        $logFileName = $logDirectory . '/error_log_' . $currentDateTime . '.log';

        // 写入错误内容到日志文件
        file_put_contents($logFileName, $errorContent);
    }

    /**
     * 记录结果
     */
    public function writeResult($fileName, $content) {
        if (!$fileName) {
            echo '请取一个文件名!' . PHP_EOL;
            return false;
        }

        // 确保日志目录存在
        if (!is_dir($this->resultDirectory)) {
            mkdir($this->resultDirectory, 0777, true);
        }

        // 设置文件名
        $dateYearMonth = date('Y-m');
        $dateDay = date('d');
        $pathYearMonth = $this->resultDirectory . '/' . $dateYearMonth;
        if (!is_dir($pathYearMonth)) {
            mkdir($pathYearMonth, 0777, true);
        }
        $pathYearMonthDay = $pathYearMonth . '/' . $dateDay;
        if (!is_dir($pathYearMonthDay)) {
            mkdir($pathYearMonthDay, 0777, true);
        }

        $fullFilePath = $pathYearMonthDay . '/' . $fileName . '.json';

        // 传进来的 $content 若不是一个正常json 或者 data 里没有数据，
        // 则判断数据文件里的是否正常，若是正常的就不覆盖
        $noHotData = false;
        $hotData = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '准备写入: 解析 $content 错误: ' . $content . PHP_EOL;
            $noHotData = true;
        }elseif (!$hotData || !isset($hotData['data']) || !$hotData['data']) {
            echo '准备写入: $content 中 data 没数据: ' . $content . PHP_EOL;
            $noHotData = true;
        }
        if ($noHotData === true) {
            if (file_exists($fullFilePath)) {
                $lastJsonContent = file_get_contents($fullFilePath);
                if ($lastJsonContent) {
                    $lastHotData = json_decode($lastJsonContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if ($lastHotData && isset($lastHotData['data']) && $lastHotData['data']) {
                            echo '准备写入: $content 异常, 但上次数据正常, 本次不覆盖更新;' . PHP_EOL;
                            return;
                        }
                    }
                }
            }
        }

        // 覆盖更新
        file_put_contents($fullFilePath, $content);
    }

    /**
     * 开始执行脚本
     * @param Int $startIdx 从 $this->allFileScript[] 下标几开始执行
     * @param Int $endIdx 执行到 $this->allFileScript[] 下标几结束
     */
    public function startFetchHotData($startIdx = 0, $endIdx = 0) {
        if ($endIdx === false) return;
        
        for ($i=$startIdx; $i <= $endIdx; $i++) {
            // echo "正在进行第{$i}个: " . ($this->allFileScript[$i] ?? "不存在") . PHP_EOL;
            if (!isset($this->allFileScript[$i])) {
                continue;
            }

            $filePath = $this->allFileScript[$i];
            // putenv 设置的环境变量在当前进程及其子进程中有效。
            foreach ($this->SYS_ENV as $envKey => $envValue) {
                $envStr = $envKey . '=' . $envValue;
                putenv($envStr);
            }
            // 通过命令行执行PHP文件
            $command = escapeshellcmd("php $filePath");

            $output = null;
            $return_var = null;
            exec($command, $output, $return_var);

            if ($return_var !== 0) {
                // 异常记录
                $this->writeErrorLog(implode("\n", $output) . "\n");
                continue;
            }
            // 输出执行结果
            // echo implode("\n", $output) . "\n";
            $fileInfo = pathinfo($filePath);
            $fileName = $fileInfo['filename'];
            // echo $fileName;
            
            $this->writeResult($fileName, implode("\n", $output));

            $output = null;
            $return_var = null;
        }

    }

    /**
     * 检查空数据的，并重新获取数据
     */
    public function cheackEmptyRetry() {
        $allResultData = [];

        // 检查目录
        if (is_dir($this->resultDirectory)) {
            // 设置文件名
            $dateYearMonth = date('Y-m');
            $dateDay = date('d');
            $pathYearMonth = $this->resultDirectory . '/' . $dateYearMonth;
            $pathYearMonthDay = $pathYearMonth . '/' . $dateDay;
            if (!is_dir($pathYearMonthDay)) {
                echo "今日目录未创建: $pathYearMonthDay" . PHP_EOL;
                exit();
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
                echo "无法打开目录: $pathYearMonthDay" . PHP_EOL;
                echo "停止执行脚本";
                exit();
            }
        } else {
            echo "目录不存在: $this->resultDirectory" . PHP_EOL;
            echo "停止执行脚本";
            exit();
        }

        $countResultNum = count($allResultData);
        echo '获取所有结果, 共: ' . $countResultNum . '个' . PHP_EOL;

        $this->allFileScript = []; // init
        foreach ($allResultData as $fileName => $resultData) {
            $scriptPath = $this->scriptDir . '/' . $fileName . '.php';

            $jsonContent = file_get_contents($resultData);
            if ($jsonContent === false) {
                // 读取文件内容失败, 重试脚本获取数据
                echo '读取文件内容失败, 重试脚本获取数据' . $fileName . PHP_EOL;
                $this->allFileScript[] = $scriptPath;
                continue;
            }

            $hotData = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // 解析json错误, 重试脚本获取数据
                echo '解析json错误, 重试脚本获取数据' . $fileName . PHP_EOL;
                $this->allFileScript[] = $scriptPath;
                continue;
            }

            if (!$hotData || !isset($hotData['data']) || !$hotData['data']) {
                // 没数据结果, 重试脚本获取数据
                echo '没数据结果, 重试脚本获取数据' . $fileName . PHP_EOL;
                $this->allFileScript[] = $scriptPath;
                continue;
            }
        }

        // var_dump($this->allFileScript);
        $countScriptNum = count($this->allFileScript);
        if ($countScriptNum > 0) { 
            $this->startFetchHotData(0, $countScriptNum === 0 ? false : $countScriptNum - 1);
        } else {
            echo '检查完毕, 无空数据, 无需重新执行的脚本;'. PHP_EOL;
        }
    }
}

// debug test
if(count($argv) > 1) {
    $_argvList = [];
    // 遍历命令行参数（从索引1开始，因为索引0是脚本名称）
    foreach($argv as $key => $value) {
        if($key > 0) { // 跳过脚本名称
            $_argvList[] = $value;
        }
    }
    if (in_array('test', $_argvList)) {
        // debug test
        new FetchHotData();
    }
}

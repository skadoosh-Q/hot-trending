<?php
include_once('./FetchHotData.php');

/**
 * 多进程版本
 * 测试命令: php ./FetchHotDataMulti.php checkEmpty
 * 参数说明: checkEmpty是仅执行 空数据重试 方法
 */
class FetchHotDataMulti extends FetchHotData {
    /**
     * 黑名单数组; 要跳过执行的脚本
     */
    protected $blacklist = [ 'all.php' ];

    /**
     * 进程数
     */
    protected $forkNum = 3;

    public function __construct()
    {   
        global $argv, $argc;
        $argvList = [];
        for ($i = 1; $i < count($argv); $i++) { // 跳过索引0，它是脚本名
            $argvList[] = $argv[$i];
        }
        // var_dump($argvList);die;

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

    public function startApp() 
    {
        $countScriptNum = count($this->allFileScript);
        echo '获取所有脚本, 共: ' . $countScriptNum . '个' . PHP_EOL;
        if ($countScriptNum === 0) {
            $this->writeErrorLog("获取到脚本数为0, 退出程序。" . "\n");
            exit();
        }
        // echo implode("\n", $this->allFileScript) . "\n";
        // exit();

        // 进程号记录
        $pids = [];
        for ($i = 0; $i < $this->forkNum; $i++) {
            // 创建子进程；子进程代码完全复制(此时的)父进程，包括变量状态; 
            // 子进程会从此行代码后执行
            // 创建子进程成功时在父进程内，返回进程号，在子进程内返回0。失败时在父进程返回 -1，不会创建子进程，并且会引发 PHP 错误;
            $pid = pcntl_fork(); 

            if ($pid) {
                $pids[] = $pid;
            } elseif ($pid == 0) {
                // 按设置的进程数量平均能分成几份   // 向上取整
                $averageNum = ceil($countScriptNum / $this->forkNum);
                if ($averageNum <= 1) {
                    $averageNum = 1;
                }

                // 开始下标
                $startIdx = $i * $averageNum;
                if ($startIdx > $countScriptNum) {
                    $startIdx = false;
                }

                // 结束下标
                $endIdx = (($i + 1) * $averageNum) - 1;
                if ($endIdx < 0) {
                    $endIdx = false;
                } elseif ($endIdx >= $countScriptNum) {
                    $endIdx = $countScriptNum - 1; // 不嫩能超出总下标
                }

                if ($endIdx < $startIdx) {
                    $endIdx = false;
                }

                echo "开始: $startIdx" . " 结束: $endIdx" . PHP_EOL;
                $this->startFetchHotData($startIdx, $endIdx);

                exit(); // 执行完，一定要结束，不然就会走进创建子进程的死循环
            } else {
                echo '创建子进程 ' . $i . ' 异常' . PHP_EOL;
            }
        }

        // 检查所有子进程状态
        foreach ($pids as $pid) {
            // $res = pcntl_waitpid($pid, $status, WNOHANG); // WNOHANG 子进程没有退出的话 立马返回 0；使用这个的话就需要定时轮询反复检查状态

            // 等待指定pid的进程完成
            $res = pcntl_waitpid($pid, $status);
            if ($res == -1 || $res > 0) {
                if (!pcntl_wifexited($status)) {
                    // 进程非正常退出
                    echo "进程: $pid 非正常退出" . PHP_EOL;
                } else {
                    // 获取进程终端的退出状态码;
                    $code = pcntl_wexitstatus($status);
                    echo "进程: $pid 已退出, 退出状态码为: $code" . PHP_EOL;
                }

                if (pcntl_wifsignaled($status)) {
                    // 不是通过接受信号中断
                    echo "进程: $pid 已退出, 但不是通过接受信号中断" . PHP_EOL;
                } else {
                    $signal = pcntl_wtermsig($status);
                    echo "进程: $pid 已退出, 是通过接受信号而中断的" . PHP_EOL;
                }
                echo PHP_EOL;
            }
        }
    }

}

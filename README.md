## 热搜数据抓取

## 说明
基于`https://github.com/WShuai123/api_for_hot_searchs`改造

### 目录
```txt
/
 ├── 📂script   # 所有获取热点数据脚本
 ├── 📂articles # 获取热点数据后保存到这
 ├── 📂logs     # 日志
 ├── main.php   # 入口文件, 调用`FetchHotData.php`或`FetchHotDataMulti.php`
 ├── FetchHotData.php       # 单进程执行
 ├── FetchHotDataMulti.php  # 多进程执行
 ├── 📂api
 │   └── getHotData.php # 将 📂articles 目录下的数据聚合json返回
 └── README.md
```

### 使用

php需要开启函数`exec`, `pcntl_fork`, `pcntl_waitpid`, `pcntl_wifexited`, `pcntl_wexitstatus`, `pcntl_wifsignaled`, `pcntl_wtermsig`。

抓取数据; (自行配置定时任务，例如配合宝塔的定时任务功能)
```shell
php ./main.php > "./logs/$(date +'%Y-%m-%d_%H-%M-%S').log"

# 仅检查空数据
php ./main.php checkEmpty > "./logs/$(date +'%Y-%m-%d_%H-%M-%S').log"
```

开发调试
```shell
php ./FetchHotData.php test
php ./FetchHotData.php test checkEmpty
```

前端页面
![](home-ex.jpg)

部署 nginx
```Nginx
server {
    listen 80;

    index index.html index.htm;
    server_name hot-trending.local;
    charset utf-8;

    location / {
      root  /hot-trending/html;
      try_files $uri $uri/ /index.html;
    }

    location /api {
      alias /hot-trending/api;
      try_files $uri $uri/ =404;

      # 配置php处理程序
      location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_read_timeout 3000;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_index index.php;
        fastcgi_buffers 768 3072k;
        fastcgi_buffer_size 3m;
      }
    }
}

```

### 目前平台：

+ 百度
+ 百度贴吧
+ 微博
+ 知乎
+ 哔哩哔哩
+ 抖音
+ 今日头条
+ 历史上的今天
+ CSDN
+ 少数派
+ 36kr
+ IT之家
+ ACFun
+ 腾讯新闻
+ GitHub
+ 懂球帝
+ 吾爱破解
+ 安全客
+ 易车网
+ V2EX
+ 虎扑(篮球、足球、步行街)
+ 360搜索  
+ 什么值得买-好价排行榜       
+ 游戏葡萄            
...完整见`script`目录               

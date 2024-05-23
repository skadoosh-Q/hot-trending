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
 └── README.md
```

### 使用

```shell
php ./main.php > "./logs/$(date +'%Y-%m-%d_%H-%M-%S').log"
```

开发调试
```shell
php ./FetchHotData.php test
php ./FetchHotData.php test checkEmpty
```

### 目前平台：

+ 百度
+ 百度贴吧
+ 微博
+ 知乎
+ 哔哩哔哩: 
    + 热搜榜
    + 全站日榜
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

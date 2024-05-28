<?php
function loadEnv($filePath) {
  if (!file_exists($filePath)) { return false; }

  $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) { continue; }

    if (preg_match('/\A([^\s=]+)\s*=\s*(["\']?)(.*)\2\z/', $line, $matches)) {
      $key = trim($matches[1]);
      $value = trim($matches[3], "\"'");
      $_ENV[$key] = $value;
    }
  }
  return true;
}

$environment = getenv('APP_ENV') ?: 'development';
$envFileLoaded = false;

if ($environment === 'production') {
  $envFileLoaded = loadEnv(__DIR__ . '/.env.production');
} else {
  $envFileLoaded = loadEnv(__DIR__ . '/.env.development');
}

if (!$envFileLoaded) {
  loadEnv(__DIR__ . '/.env');
}

function getAppEnv($key, $default = null) {
  return $_ENV[$key] ?? $default;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1,viewport-fit=cover">
  <link rel="shortcut icon" href="<?php echo getAppEnv('HEAD_ICO'); ?>">
  <title><?php echo getAppEnv('HEAD_TITLE'); ?></title>
  <meta name="Keywords" content="<?php echo getAppEnv('HEAD_KEYWORDS'); ?>">
  <meta name="Description" content="<?php echo getAppEnv('HEAD_DESCRIPTION'); ?>">
  <style>
    * {
      box-sizing: border-box;
    }

    body,
    div,
    p,
    input {
      margin: 0;
      padding: 0;
    }

    a {
      color: inherit;
      outline: 0 !important;
      text-decoration: none;
    }

    /* 自适应字体大小 */
    html {
      font-size: calc(100vw / 10);
    }

    /* 最小宽度定义 */
    @media screen and (max-width: 375Px) {
      html {
        font-size: calc(375Px / 10);
      }
    }

    html body {
      min-width: 375Px;
      margin-right: auto;
      margin-left: auto;
    }

    /* 最大宽度定义 */
    @media screen and (min-width: 750Px) {
      html {
        font-size: calc(750Px / 10);
      }
    }

    /* Theme color variables */
    html {
      --body-bg-color: #f9f9f9;
      --text-def-color: #282a2d;
      --header-bg-color: #fff;
      --header-box-shadow: 0px 0px 25px 5px rgba(10, 10, 10, .05);
      --card-bg-color: #fff;
      --card-box-shadow: 0px 0px 20px -5px rgba(158, 158, 158, .2);
      --footer-text-color: #555;
      --footer-bg-color: rgba(210, 210, 210, .5);
      --hot-item-a-visited-color: #f1b0b4;
      --hot-item-a-hover-color: #f1404b;
    }

    html.dark {
      --body-bg-color: #1b1d1f;
      --text-def-color: #c6c9cf;
      --header-bg-color: #2c2e2f;
      --header-box-shadow: none;
      --card-bg-color: #2c2e2f;
      --card-box-shadow: none;
      --footer-text-color: rgb(204, 204, 204);
      --footer-bg-color: rgba(10, 10, 10, 0);
      --hot-item-a-hover-color: #b55c62;
    }

    /* toast */
    .toast-overlay {
      position: fixed;
      font-size: 14Px;
      top: 20%;
      left: 50%;
      transform: translate(-50%, 0);
      background-color: rgba(0, 0, 0, 0.8);
      color: #fff;
      padding: 9.975px 20.025px;
      border-radius: 3.975px;
      text-align: center;
      z-index: 9999;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      display: none;
    }

    .toast-overlay.show {
      display: block;
      opacity: 1;
    }
  </style>
  <style>
    [v-cloak] {
      display: none !important;
    }

    body {
      background: var(--body-bg-color);
      font-size: 16Px;
    }

    @font-face {
      font-family: 'fagun Font';
      font-weight: 400;
      font-style: normal;
      font-display: swap;
      src: url('./styles/fagun.ttf') format('woff2');
    }

    ::-webkit-scrollbar-thumb {
      background-color: rgba(130, 130, 130, .5);
      -webkit-border-radius: .053rem;
      border-radius: .053rem;
    }

    ::-webkit-scrollbar-thumb:hover {
      background-color: var(--hot-item-a-hover-color);
    }

    ::-webkit-scrollbar-corner {
      background: rgba(0, 0, 0, 0);
      border-radius: 0 0 .107rem 0;
    }

    ::-webkit-scrollbar-button {
      background-color: rgba(0, 0, 0, 0);
      height: .027rem;
      width: .027rem;
    }

    ::-webkit-scrollbar {
      width: .067rem;
      height: .067rem;
    }

    .header-nav {
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      width: 100%;
      height: 56Px;
      background-color: var(--header-bg-color);
      box-shadow: var(--header-box-shadow);
      z-index: 99;
    }

    .header-nav .header-wrap {
      width: 100%;
      padding: .107rem .213rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: var(--text-def-color);
    }

    .header-nav .header-wrap .left {
      display: flex;
      align-items: center;
      font-family: 'fagun Font';
      font-size: 18Px;
      font-weight: 700;
      min-width: 30Px;
      min-height: 100%;
    }

    .header-nav .header-wrap .left .ico-img {
      width: 20Px;
    }

    .header-nav .header-wrap .left:focus {
      border: none;
      outline: none;
    }

    .header-nav .header-wrap .right {
      display: flex;
      align-items: center;
      font-size: 18Px;
    }

    .header-nav .header-wrap .right .bar-menu-link {
      margin-left: .2rem;
      cursor: pointer;
    }

    .header-nav .header-wrap .right .bar-menu-link:first-child {
      margin-right: 0;
    }

    .main-content {
      max-width: 1140Px;
      margin: 0 auto;
    }

    @media (min-width: 576Px) {
      .main-content {
        max-width: 540Px;
      }
    }

    @media (min-width: 768Px) {
      .main-content {
        max-width: 720Px;
      }
    }

    @media (min-width: 992Px) {
      .main-content {
        max-width: 960Px;
      }
    }

    @media (min-width: 1200Px) {
      .main-content {
        max-width: 1140Px;
      }
    }

    .main-content .content {
      width: 100%;
      padding: 0 15Px;
      margin-bottom: 15Px;
    }

    .main-content .mg-top-header {
      height: 80Px;
    }

    .card-wrap {
      display: flex;
      flex-wrap: wrap;
    }

    .card-wrap .card-item-wrap {
      padding-left: .107rem;
      padding-right: .107rem;
      margin-bottom: .213rem;
      width: 100%;
    }

    @media (max-width: 576Px) {
      .card-wrap .card-item-wrap {
        width: 100%;
        padding-left: 0;
        padding-right: 0;
      }
    }

    @media (min-width: 768Px) {
      .card-wrap .card-item-wrap {
        width: 50%;
      }
    }

    @media (min-width: 992Px) {
      .card-wrap .card-item-wrap {
        width: 33.33%;
      }
    }

    .card-wrap .card-item-wrap .card {
      background-color: var(--card-bg-color);
      border-radius: 8.025px;
      color: var(--text-def-color);
      box-shadow: var(--card-box-shadow);
      transition: background-color .3s;
    }

    .card-wrap .card-item-wrap .card .card-header {
      padding: 12px 20.025px;
      font-size: 16Px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .card-wrap .card-item-wrap .card .card-header .left {
      display: flex;
      align-items: center;
    }

    .card-wrap .card-item-wrap .card .card-header .right {
      display: flex;
      align-items: center;
    }

    .card-wrap .card-item-wrap .card .card-header .hot-ico {
      height: 18Px;
      margin-right: 3.975px;
    }

    .card-wrap .card-item-wrap .card .card-header .last-update-time {
      font-size: 12Px;
    }

    .card-wrap .card-item-wrap .card .card-body {
      padding: 0 20.025px 15.975px;
      font-size: 16Px;
      display: flex;
      align-items: center;
    }

    .card-wrap .card-item-wrap .card .card-body .hot-body {
      width: 100%;
      height: 200Px;
      display: flex;
      flex-direction: column;
      overflow-y: scroll;
    }

    /* .card-wrap .card-item-wrap .card .card-body .hot-body::-webkit-scrollbar-button { background-color: rgba(0, 0, 0, 0); height: 2.025px; width: 2.025px; }
        .card-wrap .card-item-wrap .card .card-body .hot-body::-webkit-scrollbar-corner { background: rgba(0, 0, 0, 0); border-radius: 0 0 8.025px 0; }
        .card-wrap .card-item-wrap .card .card-body .hot-body::-webkit-scrollbar-thumb { background-color: rgba(130, 130, 130, .5); -webkit-border-radius: 3.975px; border-radius: 3.975px; } */
    .card-wrap .card-item-wrap .card .card-body .hot-body .hot-item {
      font-size: 15Px;
      width: 100%;
      cursor: pointer;
      margin-bottom: 8Px;
      line-height: 21Px;
    }

    .card-wrap .card-item-wrap .card .card-body .hot-body .hot-item .hot-rank {
      width: 18Px;
      border-radius: 4Px;
      background: rgba(124, 124, 124, .3);
      display: inline-block;
      text-align: center;
      font-size: 12Px;
    }

    .card-wrap .card-item-wrap .card .card-body .hot-body .hot-item a {
      color: var(--text-def-color);
    }

    .card-wrap .card-item-wrap .card .card-body .hot-body .hot-item a.visited {
      color: var(--hot-item-a-visited-color);
    }

    .card-wrap .card-item-wrap .card .card-body .hot-body .hot-item a:hover {
      color: var(--hot-item-a-hover-color);
    }

    .main-footer {
      position: relative;
      width: 100%;
      padding: .32rem;
      padding-top: .2rem;
      line-height: .32rem;
      color: var(--text-def-color);
    }

    .main-footer .footer-copyright {
      width: 100%;
      font-size: 12Px;
      text-align: center;
    }

    .main-footer .footer-copyright a {
      color: #1690D0;
    }

    .footer-tools {
      position: fixed;
      bottom: 20.025px;
      right: 15px;
      display: flex;
      flex-direction: column;
      z-index: 5;
    }

    .footer-tools .btn {
      color: var(--footer-text-color);
      background: var(--footer-bg-color);
      font-size: 20Px;
      width: 40Px;
      height: 40Px;
      text-align: center;
      line-height: 40Px;
      padding: unset;
      display: block;
      border: unset;
      transition: .2s;
      border-radius: 50%;
      margin: 4Px;
      cursor: pointer;
    }
  </style>
  <link rel="stylesheet" href="styles/iconfont.css?t=101">
</head>

<body>
  <div id="app" v-cloak>
    <div class="toast-overlay" id="toastOverlay" :class="{ 'show': toastExtend.show }">{{ toastExtend.content }}</div>
    <div class="header-nav">
      <div class="header-wrap">
        <div class="left" contenteditable="true" @input="handleInputHeader">
          <?php echo getAppEnv('MAIN_HEADER_TITLE'); ?>
        </div>
        <div class="right">
          <div class="bar-menu-link" @click="showSiteTip">
            <i class="iconfont icon-info-fill"></i>
          </div>
          <!-- 爬虫项目不推广, 特此隐藏 -->
          <!-- Do not proactively promote crawler projects. -->
          <div class="bar-menu-link" style="display: none;">
            <a href="https://github.com/skadoosh-Q/hot-trending.git" target="_blank">
              <i class="iconfont icon-github"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="main-content">
      <div class="mg-top-header"></div>
      <div class="content">
        <div class="card-wrap">
          <div class="card-item-wrap" v-for="platformHot, platformIdx in hotData" :key="'platform' + platformIdx">
            <div class="card">
              <div class="card-header">
                <div class="left">
                  <img class="hot-ico" :src="getHotIco(platformIdx)" alt="">
                  <div class="title">{{platformHot.title}}</div>
                </div>
                <div class="right">
                  <div class="last-update-time">{{ formatDateToMinutes(platformHot.update_time) }}</div>
                </div>
              </div>
              <div class="card-body">
                <div class="hot-body">
                  <template v-if="platformHot.data && platformHot.data.length > 0">
                    <div class="hot-item" v-for="item, idx in platformHot.data" :key="'hot' + item">
                      <span class="hot-rank">{{ item.index }}</span>
                      <a :class="{'visited': clickHistory[platformIdx] && clickHistory[platformIdx][idx]}" target="_blank" :href="item.url" @click="clickHotHref(platformIdx, idx)">{{ item.title ? item.title : item.desc }}</a>
                    </div>
                  </template>
                  <template v-else>
                    <div class="hot-item">
                      <span class="hot-rank">0</span>
                      <a>钓鱼中 <span class="iconfont icon-diaoyu"></span> ...</a>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="main-footer">
      <?php echo getAppEnv('MAIN_FOOTER_HTML'); ?>
    </div>

    <div class="footer-tools">
      <a class="btn" @click="goToTop">
        <i class="iconfont icon-huidaodingbu"></i>
      </a>
      <a class="btn" v-if="themMode === 'dark'" @click="setTheme('light')">
        <i class="iconfont icon-rijianmoshi"></i>
      </a>
      <a class="btn" v-if="themMode === 'light'" @click="setTheme('dark')">
        <i class="iconfont icon-yejianmoshi"></i>
      </a>
    </div>
  </div>
  <script src="js/vue@2.7.16.min.js"></script>
  <script src="js/axios@1.7.2.min.js"></script>
  <script>
    const hotListData = [{
        "key": "weibo",
        "title": "微博"
      },
      {
        "key": "douyin",
        "title": "抖音"
      },
      {
        "key": "zhihu",
        "title": "知乎热榜"
      },
      {
        "key": "baidu",
        "title": "百度热点"
      },
      {
        "key": "bili_all",
        "title": "哔哩哔哩全站日榜"
      },
      {
        "key": "acfun",
        "title": "ACFun"
      },
      {
        "key": "xiaohongshu",
        "title": "小红书"
      },
      {
        "key": "shenmezhidemai",
        "title": "什么值得买"
      },
      {
        "key": "tieba",
        "title": "百度贴吧"
      },
      {
        "key": "douban",
        "title": "豆瓣"
      },
      {
        "key": "history",
        "title": "历史上的今天"
      },
      {
        "key": "kr36",
        "title": "36氪"
      },
      {
        "key": "GitHub",
        "title": "GitHub"
      },
      {
        "key": "v2ex",
        "title": "V2EX"
      },
      {
        "key": "wuaipojie",
        "title": "吾爱破解"
      },
      {
        "key": "youxiputao",
        "title": "游戏葡萄"
      },
      {
        "key": "shaoshupai",
        "title": "少数派"
      },
      {
        "key": "ithome",
        "title": "IT之家"
      },
      {
        "key": "hupu",
        "title": "虎扑"
      },
      {
        "key": "anquanke",
        "title": "安全客"
      },
      {
        "key": "csdn",
        "title": "CSDN"
      },
    ];
    const hotListDataIndexing = {};
    hotListData.forEach((item, idx) => {
      hotListDataIndexing[item.key] = idx;
    })

    let mediaQueryThem = null;
    var newVue = new Vue({
      el: '#app',
      data: {
        themMode: 'light',
        toastExtend: {
          show: false,
          content: '',
          time: 2000,
          countShow: 0
        },
        tooltipShow: false,
        hotData: hotListData,
        hotDataIco: {
          "weibo": "weibo.png",
          "douyin": "douyin.png",
          "zhihu": "zhihu.png",
          "baidu": "baidu.png",
          "bili_all": "bilibili.png",
          "acfun": "acfun.png",
          "wuaipojie": "wuaipojie.png",
          "xiaohongshu": "xiaohongshu.png",
          "shenmezhidemai": "smzdm.png",
          "douban": "douban.png",
          "kr36": "36kr.png",
          "GitHub": "github.png",
          "v2ex": "",
          "history": "lssdjt.png",
          "youxiputao": "youxiputao.ico",
          "shaoshupai": "sspai.png",
          "ithome": "ithome.png",
          "wxRead": "weread.png",
          "dsb": "dsb.png",
          "hupu": "hupu.png",
          "anquanke": "anquanke.png",
          "tieba": "baidu.png",
          "yichewang": "",
          "csdn": "",
          "dongqiudi": "",
          "360search": "",
          "jinritoutiao": "",
          "qqnews": "",
        },
        clickHistory: {}
      },
      computed: {
        getHotIco() {
          const _hotDataIco = this.hotDataIco;
          const _hotData = this.hotData;
          return (hotDataIdx) => {
            if (_hotData && _hotData[hotDataIdx] && _hotData[hotDataIdx].key) {
              if (!_hotDataIco[_hotData[hotDataIdx].key]) return '';
              return './images/' + _hotDataIco[_hotData[hotDataIdx].key];
            }
            return '';
          }
        }
      },
      created() {
        mediaQueryThem = window.matchMedia("(prefers-color-scheme: light)");
        this.setTheme();
        mediaQueryThem.addEventListener("change", this.setTheme);

        this.getList()
      },
      mounted() {},
      beforeDestroy() {
        mediaQueryThemNaNpxoveListener("change", this.setTheme);
      },
      methods: {
        setTheme(themModeVal = null) {
          document.documentElement.classList['remove'](this.themMode);
          let setThemeVal = 'light';
          if (themModeVal === 'light' || themModeVal === 'dark') {
            setThemeVal = themModeVal;
          } else {
            setThemeVal = mediaQueryThem.matches ? "light" : "dark";
          }
          document.documentElement.classList.add(setThemeVal);
          this.themMode = setThemeVal;
        },
        showSiteTip() {
          this.openToast("<?php echo getAppEnv('MAIN_HEADER_TIP', 'hello~'); ?>", 3000);
        },
        openToast(text, time = 2000) {
          if (text) {
            this.toastExtend.content = text;
          }
          this.toastExtend.show = true;
          this.toastExtend.countShow += 1;
          const nowShowNum = this.toastExtend.countShow;

          setTimeout(() => {
            if (this.toastExtend.countShow === nowShowNum) {
              this.toastExtend.show = false;
              this.toastExtend.content = '';
            }
          }, time);
        },
        handleInputHeader(event) {
          const inputElement = event.target;
          const text = inputElement.textContent;
          if (!text || text.trim() === '') {
            document.title = '\u200b';
            return;
          }
          document.title = text;
        },
        getList() {
          axios.get(window.location.protocol + '//' + window.location.hostname + '/api/getHotData.php')
            .then((res) => {
              const resData = res.data;
              if (resData.code !== 200) {
                this.openToast(resData.msg || '数据获取失败');
                return;
              }

              resData.data && resData.data.forEach(item => {
                if (parseInt(hotListDataIndexing[item.key]) >= 0) {
                  this.$set(this.hotData, parseInt(hotListDataIndexing[item.key]), item);
                } else {
                  this.hotData.push(item);
                }
              });

            }).catch((err) => {
              console.log(err);
              this.openToast('服务状态不可用');
            });
        },
        formatDateToMinutes(dateString) {
          if (!dateString) return '';
          try {
            const dateStr = dateString.replace(/\-/g, "/");
            const date = new Date(dateStr);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${year}-${month}-${day} ${hours}:${minutes}`;
          } catch (error) {
            console.log('formatDateToMinutes Error: ', error);
            return '';
          }

        },
        clickHotHref(levelIdx1, levelIdx2) {
          if (!this.clickHistory[levelIdx1]) {
            this.$set(this.clickHistory, levelIdx1, {});
          }
          if (!this.clickHistory[levelIdx1][levelIdx2]) {
            this.$set(this.clickHistory[levelIdx1], levelIdx2, true);
          }
        },
        goToTop() {
          document.documentElement.scrollTop = 0;
          window.scrollTo(0, 0);
        }
      },
    });
  </script>
</body>

</html>
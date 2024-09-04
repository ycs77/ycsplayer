<br />
<h1 align="center">ycsPlayer</h1>
<p align="center">
<strong>
線上影音點播包廂<br>
跟遠端的朋友們一起同步觀賞影片或聽歌~✨
</strong>
</p>
<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></a>
<a href="https://github.com/ycs77/ycsplayer/actions/workflows/ci.yml?query=branch%3Amain"><img src="https://img.shields.io/github/actions/workflow/status/ycs77/ycsplayer/ci.yml?branch=main&label=tests&style=flat-square" alt="GitHub Tests Action Status"></a>
</p>
<br />
<p align="center">
<img src="docs/screenshot-20231027.png" />
</p>

## 功能

* 房間成員可以**同步觀看和點播影片/聽音樂**
* 線上聊天室
* 支援**播放 YouTube 影片**
* 支援**上傳影片/音樂**
* 支援**播放完畢後自動刪除**功能
* 支援**正常帳密登入**和**無密碼登入**
* 房間管理員可以**邀請/請出成員**

起源是看到 LINE 有群組可以一起看 YT 的功能，但不能看其他網站的，而直播之類的又會 lag，想著想還是開始自幹一個XD~

目的主要是讓朋友們可以一起看影片、聽音樂等。看影片時不用侷限在其他網站，可以自己上傳影片和音樂，也可以直接看 YouTube 的影片。

## 線上 Demo

Demo 連結：https://ycsplayer-demo.fly.dev

點擊右上註冊按鈕，會自動產生一組帳密：

* 帳號：隨機 E-mail
* 密碼：預設為 `password`

Demo 環境資源有限，建立的帳號和房間將會於 2 小時後刪除。功能方面只會開放新增 YouTube 影片，上傳的功能是關閉的。如果想要體驗完整的功能，可以下載原始碼自行架設。

## 大綱

- [功能](#功能)
- [線上 Demo](#線上-demo)
- [大綱](#大綱)
- [依賴](#依賴)
- [本地安裝](#本地安裝)
  - [Laravel Homestead 開發環境相關](#laravel-homestead-開發環境相關)
- [部署專案](#部署專案)
- [依賴軟體/服務](#依賴軟體服務)
  - [設定 Pusher](#設定-pusher)
  - [安裝 FFMpeg](#安裝-ffmpeg)
  - [DigitalOcean Spaces (S3 兼容儲存空間)](#digitalocean-spaces-s3-兼容儲存空間)
    - [上傳 Vite 資產到 DigitalOcean Spaces](#上傳-vite-資產到-digitalocean-spaces)
- [ycsPlayer 相關環境變數](#ycsplayer-相關環境變數)
  - [開放建立房間權限](#開放建立房間權限)
  - [上傳用戶頭像](#上傳用戶頭像)
  - [播放器偵錯模式](#播放器偵錯模式)
  - [寄送 E-mail](#寄送-e-mail)
  - [無密碼登入](#無密碼登入)
- [專案 Artisan 指令](#專案-artisan-指令)
- [問與答](#問與答)
  - [「播放影片的時候會出現不同步」](#播放影片的時候會出現不同步)
  - [「上傳完影片/音樂之後要如何播放?」](#上傳完影片音樂之後要如何播放)
  - [「如何退出房間?」](#如何退出房間)
- [贊助](#贊助)
- [License](#license)

## 依賴

* PHP 8.1
* MySQL 8
* Node.js 20
* Yarn 1
* Mailgun (或是其他送信的 driver 都可以)
* Pusher
* FFMpeg
* Redis - ***選擇性***
* DigitalOcean Spaces (S3 兼容儲存空間) - ***選擇性***

## 本地安裝

需要在 Linux 環境才能裝，我是用 Windows 跑在 Laravel Homestead 裡開發的。

首先先編輯 `.env` 檔：

```bash
cp .env.example .env
```

必須要設定的有 DB、Mail、Pusher，`YCSPLAYER` 開頭的變數可以參考 [ycsPlayer 相關環境變數](#ycsplayer-相關環境變數) 來設定。

broadcast driver 一定要設 `pusher`，關於 Pusher 可以參考[設定 Pusher](#設定-pusher)。

還有轉影片的部分有依賴到 FFMpeg，要確保執行環境裡有存在 (執行 `ffmpeg -version` 確認)，要安裝可以參考[安裝 FFMpeg](#安裝-ffmpeg)。

然後執行指令來安裝依賴套件和編譯：

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
yarn
yarn code-check
yarn build
```

裝完之後預設會是沒有用戶和房間，如果你想要馬上試用的話，可以執行指令來新增測試用戶和房間：

```bash
php artisan db:seed DummySeeder
```

然後就會有兩個用戶和房間，管理員的帳密是：

* E-mail：admin@example.com
* 密碼：password

普通用戶的帳密是：

* E-mail：soyo@example.com
* 密碼：password

如果想要讓上傳任務在背景執行，可以開啟 Redis 的 Queue，`QUEUE_CONNECTION` 要改成 `redis`。為了要讓 Queue 可以持續上傳約 1-2G 的大檔案，可以設定 30 分鐘 (1800秒) 的 timeout (Job 的最長執行時間)，但要記得每次改完程式碼後都必須重啟：

```bash
php artisan queue:work --timeout=1800
```

當然這個時間都可以再修改，只是要保證比 `config/queue.php` 的 `retry_after` 設定還短。

### Laravel Homestead 開發環境相關

在 Homestead 中啟動 Vite dev server：

```bash
yarn dev
```

記得要轉發 Homestead 的 5173 port：

<!-- eslint-disable-next-line yml/indent -->
```yaml
ports:
    - send: 5173 # for vite server
      to: 5173
```

以及在 Homestead 裡要建立 Vite 開發伺服器的 HTTPS 證書的話，可以執行創建指令：

```bash
sudo /vagrant/scripts/create-certificate.sh localhost
sudo chmod -R 644 /etc/ssl/certs/localhost.key
```

然後在 `.env` 裡設定本地開發用證書路徑：

```
VITE_DEV_SERVER_KEY=/etc/ssl/certs/localhost.key
VITE_DEV_SERVER_CERT=/etc/ssl/certs/localhost.crt
```

## 部署專案

首先先編輯 `.env` 檔：

```bash
cp .env.example .env
```

必須要設定的有 DB、Mail、Pusher，如果有用 HTTPS 記得 `APP_URL` 網址要加上，`YCSPLAYER` 開頭的變數可以參考 [ycsPlayer 相關環境變數](#ycsplayer-相關環境變數) 來設定。

broadcast driver 一定要設 `pusher`，關於 Pusher 可以參考[設定 Pusher](#設定-pusher)。

還有轉影片的部分有依賴到 FFMpeg，要確保執行環境裡有安裝過，要安裝可以參考[安裝 FFMpeg](#安裝-ffmpeg)。

然後執行指令來安裝依賴套件和編譯：

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
yarn
yarn build
```

以上就是部署專案的流程，之後更新原始碼之後都要執行以下指令來更新專案：

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
yarn
yarn build
```

如果想要讓上傳任務在背景執行，可以開啟 Redis 的 Queue，`QUEUE_CONNECTION` 要改成 `redis`。為了要讓 Queue 可以持續上傳約 1-2G 的大檔案，可以在建立 Worker 時設定 30 分鐘 (1800秒) 的 timeout (Job 的最長執行時間)，但要記得每次更新完程式碼後都必須重啟。當然這個時間都可以再修改，只是要保證比 `config/queue.php` 的 `retry_after` 設定還短。

如果覺得影片太慢常常 lag，可以試試 [在 DigitalOcean Spaces 儲存影片檔案](#digitalocean-spaces-s3-兼容儲存空間)，還有 CDN 加速來讓讀取速度變快。

## 依賴軟體/服務

### 設定 Pusher

專案中有使用到 Pusher 的 Channels 服務來及時同步房間的影片播放狀態，Pusher 有提供免費額度使用，基本上私人用量應該是不會到需要付費的程度。

註冊完帳號之後，到 [Pusher](https://pusher.com/) 新增 APP 後將 App keys 複製到 `.env`，只需要填 `PUSHER_APP_ID`、`PUSHER_APP_KEY`、`PUSHER_APP_SECRET`、`PUSHER_APP_CLUSTER` 這4個就可以：

```ini
PUSHER_APP_ID=[你的app_id]
PUSHER_APP_KEY=[你的key]
PUSHER_APP_SECRET=[你的secret]
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=[你的cluster]
```

然後因為有使用到 Client event，需要在 Pusher APP 的 App settings 設定開啟 **Enable client events**。

最後在 Pusher APP 的 Webhooks 設定裡加上兩個 `https://[your-domain]/pusher/webhook` 連結：

* Webhook URL: `https://[your-domain]/pusher/webhook`
* Event type: **Channel existence**

* Webhook URL: `https://[your-domain]/pusher/webhook`
* Event type: **Presence**

如果在本地需要測試時，可以使用 ngrok 建立臨時網址來測試，但每次測試都需要更新網址到 Pusher 的後台。

### 安裝 FFMpeg

上傳檔案時有使用到 FFMpeg 來擷取影片縮圖，可以執行 `ffmpeg -version` 確認是否已安裝過，若需要安裝可以執行：

```
sudo apt install ffmpeg
```

### DigitalOcean Spaces (S3 兼容儲存空間)

DigitalOcean Spaces 是一個兼容 AWS S3 API 的服務，可以提供大容量的儲存空間，還有附加 CDN 加速功能，如果想要看影片更順暢的話可以使用此服務。不過這是要費用的，1 個月 5 美金起跳。

可以點下面連結來免費註冊 DigitalOcean，註冊完 2 個月內有 200 美金的免費額度可以玩：

[![DigitalOcean Referral Badge](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%201.svg)](https://www.digitalocean.com/?refcode=83488d5c9afd&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

```ini
FILESYSTEM_DISK=do

DO_ACCESS_KEY_ID=[你的Key]
DO_SECRET_ACCESS_KEY=[你的Secret]
DO_DEFAULT_REGION=[你的region]
DO_BUCKET=[你的bucket]
DO_URL=https://[你的bucket].[你的region].cdn.digitaloceanspaces.com
DO_ENDPOINT=https://[你的region].digitaloceanspaces.com
DO_CDN_ENDPOINT=https://api.digitalocean.com/v2/cdn/endpoints/[你的CDN-ID]
DO_USE_PATH_STYLE_ENDPOINT=false
DO_API_TOKEN=[你的API token]
```

> 配置方式是參考自 [Using Digital Ocean Spaces with Laravel](https://lightit.io/blog/using-digital-ocean-spaces-with-laravel-8/)。

`DO_BUCKET` 是開新的 Bucket 設定的名稱，`DO_DEFAULT_REGION` 是選擇的伺服器地區代號，`DO_ENDPOINT` 是 Endpoint 端點，記得開頭只有加地區代號，還有記得把 CDN 設定打開。

`DO_ACCESS_KEY_ID` 跟 `DO_SECRET_ACCESS_KEY` 到 [Spaces access keys](https://cloud.digitalocean.com/account/api/spaces) 新增，新增完一短一長的就是這兩個 KEY。

再來是是 CDN 的設定，因為要在刪除完檔案之後順便也清掉 Cache 的檔案，需要 CDN endpoint 的 ID，再把 ID 組成網址後就可以打清 CDN Cache 的 API。

`API_TOKEN` 可以到 [Personal access tokens](https://cloud.digitalocean.com/account/api/tokens) 新增，先新增一個臨時用的，取得完之後可以先刪掉。而 ID 取得方式為：

```bash
$ curl -X GET -H "Content-Type: application/json" \
    -H "Authorization: Bearer $API_TOKEN" \
    "https://api.digitalocean.com/v2/cdn/endpoints"
```

然後就會回傳以下 JSON 格式：

```json
{
  "endpoints": [
    {
      "id": "00000000-0000-0000-0000-000000000000",
      "origin": "[你的bucket].[你的region].digitaloceanspaces.com",
      "endpoint": "[你的bucket].[你的region].cdn.digitaloceanspaces.com",
      "created_at": "2023-00-00T00:00:00Z",
      "ttl": 3600
    }
  ]
}
```

找到剛才新增的 endpoint 物件的 ID，填到 `DO_CDN_ENDPOINT` 後面即可。因為使用了 CDN，就可以在 `DO_URL` 配置 CDN 專屬的網址。

然後打清 CDN Cache 的 API 是需要一個 `API_TOKEN` 的，這裡可以新增一個只有清 CDN Cache 權限的 token。

一樣也是到 [Personal access tokens](https://cloud.digitalocean.com/account/api/tokens)，新增一個 key：

- Token Name: ycsPlayer
- Expiration: No expire
- Scopes: Custom Scopes
- Custom Scopes:
  - [x] cdn
    - [x] delete

最後把 token 貼到 `DO_API_TOKEN` 就完成了。

#### 上傳 Vite 資產到 DigitalOcean Spaces

使用到了 [@froxz/vite-plugin-s3](https://github.com/SergkeiM/vite-plugin-s3) 套件來上傳資產，當然預設是不會開啟上傳的。要使用之前需要先配置好上面 DigitalOcean Spaces 的金鑰等，然後把 `VITE_S3_UPLOAD_VITE_ASSETS_ENABLED` 設成 `true`，`ASSET_URL` 反註解掉：

```ini
ASSET_URL="${DO_URL}"

VITE_S3_UPLOAD_VITE_ASSETS_ENABLED=true
```

以及還要到 DigitalOcean Spaces 的 Settings 裡面設定 CORS，否則會因為跨域的原因無法執行 JS。在 CORS Configurations 裡面增加一條規則，`Allowed Methods` 允許 `GET`，`Access Control Max Age` 設定 5，然後儲存。

之後在每次執行 `yarn build` 的時候就會自動上傳到 DigitalOcean Spaces 了。

## ycsPlayer 相關環境變數

這些環境變數是 ycsPlayer 提供的一些功能的開關，可以自行斟酌是否要開啟。

### 開放建立房間權限

`YCSPLAYER_OPEN_ROOM_CREATION` 會決定是否要讓全部使用者都可以建立房間，開啟之後所有使用者不論是不是管理員都可以建立，而關閉之後就僅限管理員才能建立房間。

### 上傳用戶頭像

`YCSPLAYER_UPLOAD_AVATAR` 會決定用戶是否可以上傳頭像圖片。

### 寄送 E-mail

`YCSPLAYER_MAIL` 會決定是否要寄送 E-mail 的功能，開啟後會在註冊成功時寄送認證的 E-mail，以及在「無密碼登入」模式中寄送登入、刪除帳號前的確認 E-mail。

### 無密碼登入

`YCSPLAYER_PASSWORD_LESS` 會決定是否開啟「無密碼登入」的功能，開啟之後登入時不需輸入密碼，取而代之會以收 E-mail 來登入網站。需要注意的是，如果要使用「無密碼登入」請必須開啟寄送 E-mail 的設定。

## 專案 Artisan 指令

| 功能                   | 指令                              | 說明                                                         |
| ---------------------- | --------------------------------- | ------------------------------------------------------------ |
| 附加管理員角色         | php artisan app:admin 1           | 讓 User ID 1 擁有管理員角色。                                |
| 刪除管理員角色         | php artisan app:admin 1 --remove  | 取消 User ID 1 的管理員角色。                          |
| 同步房間權限資料       | php artisan room:sync-permissions | 如果有新增或刪除權限時，可以執行當前指令來同步。             |
| 清除過時的上傳暫存檔案 | php artisan room:queue-file:purge | 清除過時的上傳暫存檔案，設定 Schedule 之後會每天固定清一次。 |

## 問與答

### 「播放影片的時候會出現不同步」

每個設備的網路速度環境都不一樣，一定會出現 Lag 的問題，如果遇到這個情況可以「暫停」再「播放」一次就可以重新同步所有設備的播放狀態。

### 「上傳完影片/音樂之後要如何播放?」

電腦版在「首頁」分頁下面點「新增播放項目」，手機版在下面「播放清單」裡點「新增播放項目」。然後「媒體類型」是影片選影片，是音樂就選音樂，最後在選擇媒體裡就可以找到剛才上傳的影片/音樂了。

### 「如何退出房間?」

在房間成員的卡片裡找到自己點進去，點「退出房間」。如果是管理員還可以將其他成員退出房間，但管理員自己不能退出，需要時可以刪除房間。

## 贊助

如果我製作的專案有幫助到你，可以考慮[贊助我](https://www.patreon.com/ycs77)~ 我會很感謝你~ 而且還可以顯示您的大頭貼在我的主要專案中。

If you think my created projects have helped you, please consider [Becoming a sponsor](https://www.patreon.com/ycs77) to support my work~ and your avatar will be visible on my major projects.

<p align="center">
  <a href="https://www.patreon.com/ycs77">
    <img src="https://cdn.jsdelivr.net/gh/ycs77/static/sponsors.svg"/>
  </a>
</p>

<a href="https://www.patreon.com/ycs77">
  <img src="https://c5.patreon.com/external/logo/become_a_patron_button.png" alt="Become a Patron" />
</a>

## License

[MIT LICENSE](LICENSE)

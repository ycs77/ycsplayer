<br />
<h1 align="center">ycsPlayer</h1>
<p align="center">線上影音點播包廂</p>
<p align="center">
<img src="screenshot.png" width="600" />
</p>
<br />

可以讓同一個房間的成員都可以像卡拉 OK 點歌機一樣選影片或點歌~🥰

## 功能

* 房間成員可以同步觀看影片/聽音樂
* 支援播放 YouTube 影片/音樂
* 支援上傳影片/音樂 (可以傳大檔案)
* 房間成員都可以點播影片/音樂
* 自動播放
* 播放完畢後自動刪除
* 支援**正常帳密登入**和**無密碼登入**
* 房間管理員可以邀請/請出成員

起源是看到 LINE 有群組可以一起看 YT 的功能，但不能看其他網站的，而直播之類的又會 lag，想著想還是開始自幹一個XD

## 大綱

- [功能](#功能)
- [大綱](#大綱)
- [依賴](#依賴)
- [本地安裝](#本地安裝)
  - [Laravel Homestead 開發環境相關](#laravel-homestead-開發環境相關)
- [部署專案](#部署專案)
- [依賴軟體/服務](#依賴軟體服務)
  - [設定 Pusher](#設定-pusher)
  - [安裝 FFMpeg](#安裝-ffmpeg)
- [ycsPlayer 相關環境變數](#ycsplayer-相關環境變數)
  - [開放建立房間權限](#開放建立房間權限)
  - [播放器 Log 紀錄](#播放器-log-紀錄)
  - [無密碼登入](#無密碼登入)
- [專案 Artisan 指令](#專案-artisan-指令)
- [⭐贊助 - Sponsor](#贊助---sponsor)

## 依賴

* PHP 8.1
* MySQL 8
* Node.js 18
* Yarn 1
* Pusher
* Mailgun (或是其他送信的 driver 都可以)

## 本地安裝

需要在 Linux 環境才能裝，我是用 Windows 跑在 Laraevl Homestead 裡開發的。

基本的 PHP 8.1、MySQL 8 有了之後，就可以編輯 `.env` 檔：

```bash
cp .env.example .env
```

必須要設定的有 DB、Mail、Pusher，注意 broadcast driver 一定要設 `pusher`。`YCSPLAYER` 開頭的變數可以參考 [ycsPlayer 相關環境變數](#ycsplayer-相關環境變數) 來設定，關於 Pusher 可以[參考 設定 Pusher](#設定-pusher)：

```ini
YCSPLAYER_OPEN_ROOM_CREATION=false
YCSPLAYER_LOG_ENABLED=false
YCSPLAYER_PASSWORD_LESS=false
```

然後執行指令來安裝依賴套件和編譯：

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
yarn
yarn check
yarn build
```

還有轉影片的部分有依賴到 FFMpeg，要確保執行環境裡有存在 (執行 `ffmpeg -version` 確認)，要安裝可以參考[安裝 FFMpeg](#安裝-ffmpeg)。

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

### Laravel Homestead 開發環境相關

啟動 Vite dev server 記得要轉發 Homestead 的 5173 port：

```bash
yarn dev
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

基本的 PHP 8.1、MySQL 8 有了之後，就可以編輯 `.env` 檔：

```bash
cp .env.example .env
```

必須要設定的有 DB、Mail、Pusher，`APP_URL` 如果有用 HTTPS 記得網址要加上，注意 broadcast driver 一定要設 `pusher`。`YCSPLAYER` 開頭的變數可以參考 [ycsPlayer 相關環境變數](#ycsplayer-相關環境變數) 來設定，關於 Pusher 可以[參考 設定 Pusher](#設定-pusher)：

```ini
APP_URL=https://[your-domain]

YCSPLAYER_OPEN_ROOM_CREATION=false
YCSPLAYER_LOG_ENABLED=false
YCSPLAYER_PASSWORD_LESS=false
```

然後執行指令來安裝依賴套件和編譯：

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
php artisan optimize
php artisan view:cache
yarn
yarn build
```

還有轉影片的部分有依賴到 FFMpeg，要確保執行環境裡有存在 (執行 `ffmpeg -version` 確認)，要安裝可以參考[安裝 FFMpeg](#安裝-ffmpeg)。

以上就是部署專案的流程，之後更新原始碼之後都要執行以下指令來更新專案：

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan config:clear
php artisan route:clear
php artisan migrate --force
php artisan optimize
php artisan view:cache
yarn
yarn build
```

## 依賴軟體/服務

### 設定 Pusher

專案中有使用到 Pusher 的 Channels 服務來及時同步房間的影片播放狀態，Pusher 有提供免費額度使用，基本上私人用量應該是不會到需要付費的程度。

註冊完帳號之後，到 [Pusher](https://pusher.com/) 新增 APP 後將金鑰複製到 `.env`：

```ini
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

然後在 Pusher APP 的 Webhooks 設定裡加上兩個 `https://[your-domain]/pusher/webhook` 連結，**Event type** 選擇 *Channel existence* 和 *Presence*。如果在本地需要測試時，可以使用 ngrok 建立臨時網址來測試，但每次測試都需要更新網址到 Pusher 的後台。

### 安裝 FFMpeg

上傳檔案時有使用到 FFMpeg 來擷取影片縮圖，因此需要安裝：

```
sudo apt install ffmpeg
```

## ycsPlayer 相關環境變數

這些環境變數是 ycsPlayer 提供的一些功能的開關，可以自行斟酌是否要開啟。

### 開放建立房間權限

`YCSPLAYER_OPEN_ROOM_CREATION` 會決定是否要讓全部使用者都可以建立房間，開啟之後所有使用者不論是不是管理員都可以建立，而關閉之後就僅限管理員才能建立房間。

### 播放器 Log 紀錄

`YCSPLAYER_LOG_ENABLED` 會決定是否開啟播放器 Log 紀錄的功能，開啟之後在播放器的播放、暫停、拖曳進度條等操作時會記錄到 Log 中，用於排查錯誤時使用。

### 無密碼登入

`YCSPLAYER_PASSWORD_LESS` 會決定是否開啟「無密碼登入」的功能，開啟之後登入時不需輸入密碼，取而代之會以收 E-mail 來登入網站。

## 專案 Artisan 指令

| 功能             | 指令                              | 說明                                             |
| ---------------- | --------------------------------- | ------------------------------------------------ |
| 新增管理員角色   | php artisan app:admin 1           | 讓 User ID 1 增加管理員角色。                    |
| 刪除管理員角色   | php artisan app:admin 1 --remove  | 刪除 User ID 1 管理員的管理員角色。              |
| 同步房間權限資料 | php artisan room:sync-permissions | 如果有新增或刪除權限時，可以執行當前指令來同步。 |

## ⭐贊助 - Sponsor

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

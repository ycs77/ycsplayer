<br />
<h1 align="center">ycsPlayer</h1>
<p align="center">線上影音點播包廂</p>
<p align="center">
<img src="screenshot.png" width="600" />
</p>
<br />

## 本地安裝

依賴 Laravel Homestead 環境，PHP 8.1，Node.js 18+。

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
yarn
yarn dev
```

記得要轉發 Homestead 的 5173 port。

### 在 Homesetad 裡建立 Vite 開發伺服器的 HTTPS 證書

執行創建指令：

```bash
sudo /vagrant/scripts/create-certificate.sh localhost
sudo chmod -R 644 /etc/ssl/certs/localhost.key
```

然後在 `.env` 裡設定本地開發用證書路徑：

```
VITE_DEV_SERVER_KEY=/etc/ssl/certs/localhost.key
VITE_DEV_SERVER_CERT=/etc/ssl/certs/localhost.crt
```

## Pusher

到 [Pusher](https://pusher.com/) 新增 APP 後將金鑰複製到 `.env`：

```ini
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

然後在 Pusher APP 的 Webhooks 設定裡加上兩個 `https://your-domain.com/pusher/webhook` 連結，**Event type** 選擇 *Channel existence* 和 *Presence*。如果在本地需要測試時，可以使用 ngrok 建立臨時網址來測試，但每次測試都需要更新網址到 Pusher 的後台。

## FFMpeg

因為在上傳檔案時有使用到 FFMpeg 來擷取影片縮圖，因此需要安裝：

```
sudo apt install ffmpeg
```

## 啟動 Server

啟動 Vite dev server：

```bash
yarn dev
```

啟動 Queue server：

```bash
php artisan queue:work
```

## 同步權限

如果有新增或刪除權限時，可以執行當前指令來同步：

```
php artisan room:sync-permissions
```

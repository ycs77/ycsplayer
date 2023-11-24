<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 開放建立房間權限
    |--------------------------------------------------------------------------
    |
    | 這一項設定可以決定是否要讓全部使用者都可以建立房間，開啟之後所有使用者不論
    | 是不是管理員都可以建立，而關閉之後就僅限管理員才能建立房間。
    |
    */

    'open_room_creation' => env('YCSPLAYER_OPEN_ROOM_CREATION', false),

    /*
    |--------------------------------------------------------------------------
    | 上傳用戶頭像
    |--------------------------------------------------------------------------
    |
    | 這一項設定可以決定用戶是否可以上傳頭像圖片。
    |
    */

    'upload_avatar' => env('YCSPLAYER_UPLOAD_AVATAR', true),

    /*
    |--------------------------------------------------------------------------
    | 寄送 E-mail
    |--------------------------------------------------------------------------
    |
    | 這一項設定可以決定是否要寄送 E-mail 的功能，開啟後會在註冊成功時寄送認證的
    | E-mail，以及在「無密碼登入」模式中寄送登入、刪除帳號前的確認 E-mail。
    |
    */

    'mail' => env('YCSPLAYER_MAIL', true),

    /*
    |--------------------------------------------------------------------------
    | 無密碼登入
    |--------------------------------------------------------------------------
    |
    | 這一項設定可以決定是否開啟「無密碼登入」的功能，開啟之後登入時不需輸入密碼，
    | 取而代之會以收 E-mail 來登入網站。需要注意的是，如果要使用「無密碼登入」
    | 請必須開啟寄送 E-mail 的設定。
    |
    */

    'password_less' => env('YCSPLAYER_PASSWORD_LESS', false),

];

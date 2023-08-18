<?php

namespace App\Enums;

enum PlayerType: string
{
    case Video = 'video';
    case Audio = 'audio';
    case YouTube = 'youtube';
}

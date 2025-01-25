<?php

use App\Models\PlaylistItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

function user(?string $name = null, ?string $email = null): ?User
{
    if ($name || $email) {
        return User::query()
            ->when($name, fn ($query) => $query->where('name', $name))
            ->when($email, fn ($query) => $query->where('email', $email))
            ->first();
    }

    return null;
}

function loginUser(string $email = 'admin@example.com'): ?User
{
    if ($user = user(email: $email)) {
        actingAs($user);

        return $user;
    }

    return null;
}

function passwordless()
{
    Config::set('ycsplayer.password_less', true);
}

function room(string $name): ?Room
{
    return Room::where('name', $name)->first();
}

function playlist(Room $room, string $itemTitle): ?PlaylistItem
{
    return $room->playlistItems()
        ->where('title', $itemTitle)
        ->first();
}

function fakeFileFromPath(string $path, ?string $filename = null)
{
    $filename = $filename ?? basename($path);

    $r = fopen(base_path($path), 'rb');
    $contents = fread($r, filesize(base_path($path)));
    fclose($r);

    return UploadedFile::fake()->createWithContent(
        $filename, $contents
    );
}

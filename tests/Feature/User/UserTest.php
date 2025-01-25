<?php

use App\PasswordlessLogin\Notifications\SendPasswordlessDestroyUserLink;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Grosv\LaravelPasswordlessLogin\UserClass;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('should visit user settings page', function () {
    get('/user/settings')
        ->assertSuccessful();
});

test('should save user settings only name & email', function () {
    put('/user/profile-information', [
        'name' => 'New Admin',
        'email' => 'admin@example.com',
        'avatar' => null,
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $user = user(email: 'admin@example.com');

    expect($user->name)->toBe('New Admin');
    expect($user->email)->toBe('admin@example.com');
});

test('should save user settings with password', function () {
    put('/user/profile-information', [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'avatar' => null,
        'current_password' => 'password',
        'password' => 'new_password',
        'password_confirmation' => 'new_password',
    ]);

    $user = user(email: 'admin@example.com');

    expect($user->name)->toBe('Admin');
    expect($user->email)->toBe('admin@example.com');
    expect(Hash::check('new_password', $user->password))->toBeTrue();
});

test('should upload custom user avatar', function () {
    Config::set('ycsplayer.upload_avatar', true);

    Storage::fake();

    $avatar = UploadedFile::fake()
        ->image('avatar.jpg', 500, 500);

    post('/user/avatar', [
        'avatar' => $avatar,
    ]);

    $user = user(email: 'admin@example.com');

    expect($user->avatar)->not->toBeNull();

    Storage::assertExists($user->avatar);
});

test('should remove custom user avatar', function () {
    Config::set('ycsplayer.upload_avatar', true);

    Storage::fake();

    $avatarPath = UploadedFile::fake()
        ->image('avatar.jpg', 500, 500)
        ->store('avatars');

    $user = user(email: 'admin@example.com');

    $user->avatar = $avatarPath;
    $user->save();

    actingAs($user);

    delete('/user/avatar');

    $user->refresh();

    expect($user->avatar)->toBeNull();

    Storage::assertMissing($avatarPath);
});

test('should destroy user with password confirm', function () {
    get('/user/destroy/confirm')
        ->assertRedirect('/user/confirm-password');

    get('/user/confirm-password')
        ->assertSuccessful();

    post('/user/confirm-password', ['password' => 'password'])
        ->assertRedirect('/user/destroy/confirm');

    delete('/user')
        ->assertRedirect('/login');

    assertGuest();

    expect(user(email: 'admin@example.com'))->toBeNull();
});

test('should destroy user with password-less', function () {
    Notification::fake();

    passwordless();

    $user = user(email: 'admin@example.com');

    get('/user/destroy/confirm')
        ->assertSuccessful();

    delete('/user')
        ->assertRedirect('/user/destroy');

    Notification::assertSentTo($user, SendPasswordlessDestroyUserLink::class);

    assertGuest();

    $url = URL::temporarySignedRoute('passwordless-destroy-user.destroy', now()->addMinutes(30), [
        'uid' => $user->id,
        'redirect_to' => null,
        'user_type' => UserClass::toSlug(get_class($user)),
    ]);

    get($url)
        ->assertRedirect('/login');

    expect(user(email: 'admin@example.com'))->toBeNull();
});

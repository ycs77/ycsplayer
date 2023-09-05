<?php

namespace App\Http\Controllers;

use App\ChunkUpload\UploadHandler;
use App\Jobs\AddRoomMediaFile;
use App\Models\QueueRoomFile;
use App\Models\Room;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class RoomUploadMediaController extends Controller
{
    public function __invoke(Request $request, Room $room)
    {
        $this->authorize('uploadMedias', $room);

        $receiver = new FileReceiver('file', $request, UploadHandler::class);

        if (! $receiver->isUploaded()) {
            throw ValidationException::withMessages([
                'file' => '上傳時檔案遺失',
            ]);
        }

        /** @var \App\ChunkUpload\Save */
        $fileReceived = $receiver->receive();

        if ($fileReceived->isFinished()) {
            /** @var \Illuminate\Http\UploadedFile */
            $file = $fileReceived->getFile();

            $extension = $file->getClientOriginalExtension();
            $fileName = str_replace('.'.$extension, '', $file->getClientOriginalName());

            $validator = Validator::make(['file' => $file], [
                'file' => [
                    'required',
                    'file',
                    File::types(['mp4', 'mp3'])
                        ->max(config('media-library.max_file_size')),
                    function (string $attribute, mixed $value, Closure $fail) use ($fileName) {
                        if (strlen($fileName) > 50) {
                            $fail('檔名最多 50 個字');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                if (is_file($file->getPathname())) {
                    unlink($file->getPathname());
                }

                // throw validate exception
                $validator->validate();

                return;
            }

            $queueFile = QueueRoomFile::create([
                'name' => $fileName,
                'path' => $file->store('medias', ['disk' => 'local']),
                'disk' => 'local',
            ]);

            AddRoomMediaFile::dispatch($room, $queueFile);

            if (is_file($file->getPathname())) {
                unlink($file->getPathname());
            }

            if (config('queue.default') !== 'sync') {
                return ['success' => '檔案上傳成功，等待處理媒體檔案...'];
            }

            return ['success' => null];
        }

        /** @var \App\ChunkUpload\UploadHandler */
        $handler = $fileReceived->handler();

        return [
            'done' => $handler->getPercentageDone(),
            'status' => true,
        ];
    }
}

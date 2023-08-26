<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\Support\File;
use Throwable;

class RoomUploadMediaController extends Controller
{
    public function __invoke(Request $request, Room $room)
    {
        $this->authorize('uplaodFiles', $room);

        $receiver = new FileReceiver(
            'file', $request, HandlerFactory::classFromRequest($request)
        );

        if (! $receiver->isUploaded()) {
            throw ValidationException::withMessages([
                'file' => '上傳時檔案遺失',
            ]);
        }

        /** @var \Pion\Laravel\ChunkUpload\Save\AbstractSave */
        $fileReceived = $receiver->receive();

        if ($fileReceived->isFinished()) {
            /** @var \Illuminate\Http\UploadedFile */
            $file = $fileReceived->getFile();

            $extension = $file->getClientOriginalExtension();
            $name = str_replace('.'.$extension, '', $file->getClientOriginalName());
            $fileName = Str::random(20).'.'.$extension;

            try {
                $room
                    ->addMedia($file)
                    ->usingName($name)
                    ->usingFileName($fileName)
                    ->toMediaCollection();
            } catch (FileIsTooBig $e) {
                $fileSize = File::getHumanReadableSize(filesize($file->getPath().'/'.$file->getFilename()));
                $maxFileSize = File::getHumanReadableSize(config('media-library.max_file_size'));

                unlink($file->getPathname());

                throw ValidationException::withMessages([
                    'file' => '檔案大小為 '.$fileSize.' 超過上限大小 '.$maxFileSize,
                ]);
            } catch (Throwable $e) {
                unlink($file->getPathname());

                throw $e;
            }

            return 'uploaded';
        }

        $handler = $fileReceived->handler();

        return [
            'done' => $handler->getPercentageDone(),
            'status' => true,
        ];
    }
}

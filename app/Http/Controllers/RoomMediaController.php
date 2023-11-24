<?php

namespace App\Http\Controllers;

use App\Facades\Flash;
use App\Jobs\RemoveRoomMediaFile;
use App\Models\Room;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RoomMediaController extends Controller
{
    public function destroy(Room $room, Media $media)
    {
        $this->authorize('uploadMedias', $room);

        RemoveRoomMediaFile::dispatch($media);

        Flash::success('檔案刪除成功');
    }
}

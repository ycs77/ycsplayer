<?php

namespace App\Http\Controllers;

use App\Enums\PlayerType;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index()
    {
        return Inertia::render('Room/Index');
    }

    public function show(PlayerType $playerType)
    {
        return Inertia::render('Room/Show', [
            'playerType' => $playerType,
        ]);
    }
}

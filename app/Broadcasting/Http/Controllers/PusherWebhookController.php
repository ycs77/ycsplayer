<?php

namespace App\Broadcasting\Http\Controllers;

use App\Broadcasting\Events\PusherChannelOccupied;
use App\Broadcasting\Events\PusherChannelVacated;
use App\Broadcasting\Events\PusherMemberAdded;
use App\Broadcasting\Events\PusherMemberRemoved;
use App\Broadcasting\Events\PusherWebhookReceived;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
use Illuminate\Http\Request;
use Pusher\Webhook;

class PusherWebhookController
{
    public function __invoke(Request $request)
    {
        $webhook = $this->webhook($request);

        foreach ($webhook->get_events() as $event) {
            if ($event->name === 'channel_occupied') {
                event(new PusherChannelOccupied(
                    $event->channel,
                    $webhook->get_time_ms()
                ));
            } elseif ($event->name === 'channel_vacated') {
                event(new PusherChannelVacated(
                    $event->channel,
                    $webhook->get_time_ms()
                ));
            } elseif ($event->name === 'member_added') {
                event(new PusherMemberAdded(
                    $event->channel,
                    $webhook->get_time_ms(),
                    $event->user_id
                ));
            } elseif ($event->name === 'member_removed') {
                event(new PusherMemberRemoved(
                    $event->channel,
                    $webhook->get_time_ms(),
                    $event->user_id
                ));
            }
        }

        event(new PusherWebhookReceived(
            $webhook->get_time_ms(),
            $webhook->get_events()
        ));
    }

    protected function webhook(Request $request): Webhook
    {
        return app(BroadcastFactory::class)
            ->connection('pusher')
            ->getPusher()
            ->webhook([
                'X-Pusher-Key' => $request->header('X-Pusher-Key'),
                'X-Pusher-Signature' => $request->header('X-Pusher-Signature'),
            ], $request->getContent());
    }
}

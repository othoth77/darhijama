<?php

namespace Modules\Invitations\Listeners;

use App\Events\NotificationRequested;
use App\Events\RsvpSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Invitations\Models\RsvpResponse;
use Mythos\Core\Identity\Models\User;
use Mythos\Core\Notifications\NotificationMessage;

class NotifyAdminsOfRsvp implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(RsvpSubmitted $event): void
    {
        $response = RsvpResponse::query()->find($event->responseId);

        if (! $response) {
            return;
        }

        User::query()
            ->eachById(function (User $user) use ($response): void {
                if (! $user->can('invitations.manage')) {
                    return;
                }

                NotificationRequested::dispatch(
                    $user::class,
                    $user->getKey(),
                    new NotificationMessage(
                        'rsvp_submitted',
                        'Nouvelle réponse RSVP',
                        "{$response->name} a répondu à l’invitation.",
                        [
                            'invitation_id' => $response->invitation_id,
                            'rsvp_response_id' => $response->id,
                            'status' => $response->status->value,
                        ],
                        idempotencyKey: "rsvp:{$response->id}:{$response->submissions_count}:user:{$user->id}",
                    ),
                );
            });
    }
}

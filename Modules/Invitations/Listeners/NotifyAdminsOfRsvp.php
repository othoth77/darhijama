<?php

namespace Modules\Invitations\Listeners;

use App\Events\NotificationRequested;
use App\Events\RsvpSubmitted;
use App\Models\User;
use App\Notifications\NotificationMessage;
use Modules\Invitations\Models\RsvpResponse;

class NotifyAdminsOfRsvp
{
    public function handle(RsvpSubmitted $event): void
    {
        $response = RsvpResponse::query()->find($event->responseId);

        if (! $response) {
            return;
        }

        User::query()->get()
            ->filter(fn (User $user): bool => $user->can('invitations.manage'))
            ->each(function (User $user) use ($response): void {
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

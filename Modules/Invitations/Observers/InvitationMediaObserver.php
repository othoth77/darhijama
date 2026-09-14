<?php

namespace Modules\Invitations\Observers;

use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\InvitationPublicCache;
use Mythos\Core\Media\Models\Media;

class InvitationMediaObserver
{
    public function saved(Media $media): void
    {
        $this->invalidate($media);
    }

    public function deleted(Media $media): void
    {
        $this->invalidate($media);
    }

    private function invalidate(Media $media): void
    {
        if ($media->mediable instanceof Invitation) {
            app(InvitationPublicCache::class)->invalidate($media->mediable);
        }
    }
}

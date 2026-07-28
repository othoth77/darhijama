<?php

namespace Modules\Invitations\Observers;

use Modules\Invitations\Models\ProgramStep;
use Modules\Invitations\Services\InvitationPublicCache;

class ProgramStepObserver
{
    public function saved(ProgramStep $step): void
    {
        app(InvitationPublicCache::class)->invalidate($step->invitation);
    }

    public function deleted(ProgramStep $step): void
    {
        app(InvitationPublicCache::class)->invalidate($step->invitation);
    }
}

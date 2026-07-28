<?php

namespace Applications\MythosShowcase\Http\Controllers;

use Applications\MythosShowcase\Domain\ShowcaseEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder;
use Mythos\Core\Audit\AuditAction;
use Mythos\Core\Audit\Contracts\AuditLogger;
use Mythos\Core\Media\Contracts\MediaManager;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Mythos\Core\Notifications\NotificationMessage;

class ShowcaseController
{
    public function __construct(
        private readonly MediaManager $media,
        private readonly NotificationDispatcher $notifications,
        private readonly AnalyticsRecorder $analytics,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->analytics->recordPageView(
            source: 'mythos-showcase',
            visitorHash: hash('sha256', implode('|', [
                (string) $request->ip(),
                (string) $request->userAgent(),
            ])),
            subjectType: ShowcaseEntry::class,
        );

        return view('mythos-showcase::index', [
            'entries' => ShowcaseEntry::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1000'],
            'media' => ['nullable', 'file'],
        ]);

        $entry = ShowcaseEntry::query()->create([
            'title' => $validated['title'],
            'message' => $validated['message'] ?? null,
        ]);

        $storedMedia = null;

        if ($request->hasFile('media')) {
            $storedMedia = $this->media->uploadFor(
                mediable: $entry,
                file: $request->file('media'),
                type: 'attachment',
                directory: 'mythos-showcase',
            );
        }

        $this->audit->record(
            action: AuditAction::Create,
            entityType: $entry->getMorphClass(),
            entityId: $entry->getKey(),
        );
        $this->notifications->send($entry, new NotificationMessage(
            type: 'mythos-showcase.entry-created',
            title: 'Showcase entry created',
            body: $entry->title,
            data: ['entry_id' => $entry->getKey()],
            idempotencyKey: "mythos-showcase.entry-created.{$entry->getKey()}",
        ));

        $mediaPath = $storedMedia?->getAttribute('path');
        $mediaDisk = $storedMedia?->getAttribute('disk');

        return response()->json([
            'id' => $entry->getKey(),
            'title' => $entry->title,
            'media_url' => ! is_string($mediaPath) || ! is_string($mediaDisk)
                ? null
                : $this->media->url($mediaPath, $mediaDisk),
        ], 201);
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Media\Services;

use App\Application\Media\Contracts\EventPublisher;
use App\Application\Media\Mappers\MediaEventMapper;
use App\Domain\Media\Entities\Media;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

/**
 * Concrete EventPublisher that uses MediaEventMapper and Laravel's event bus.
 *
 * If a third handler repeats the same release→map→dispatch pattern,
 * all handlers share this single collaborator instead of duplicating logic.
 *
 * Listener failures are logged but never propagated — the primary
 * operation (DB + storage commit) must not be rolled back by
 * integration event processing failures. This ensures the platform
 * remains eventually consistent: events will retry (queue) or be
 * reconciled by operational commands (media:audit, media:cleanup).
 */
final class MediaEventPublisher implements EventPublisher
{
    public function __construct(
        private readonly MediaEventMapper $mapper,
        private readonly Dispatcher $events,
    ) {}

    public function publish(array $domainEvents, Media $media): void
    {
        foreach ($domainEvents as $domainEvent) {
            foreach ($this->mapper->map($domainEvent, $media) as $appEvent) {
                try {
                    $this->events->dispatch($appEvent);
                } catch (\Throwable $e) {
                    Log::error('MediaEventPublisher: event dispatch failed', [
                        'domainEvent' => $domainEvent::class,
                        'appEvent' => $appEvent::class,
                        'mediaId' => $media->id(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

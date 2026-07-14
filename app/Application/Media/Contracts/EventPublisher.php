<?php

declare(strict_types=1);

namespace App\Application\Media\Contracts;

use App\Domain\Media\Entities\Media;

/**
 * Publishes domain events from a Media aggregate as application events.
 *
 * Handlers call publish() after releasing events from the aggregate.
 * The publisher delegates translation to a mapper and dispatches
 * the resulting application events to the framework's event bus.
 */
interface EventPublisher
{
    /**
     * Translate recorded domain events into application events and dispatch them.
     *
     * @param  object[]  $domainEvents
     */
    public function publish(array $domainEvents, Media $media): void;
}

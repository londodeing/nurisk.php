<?php

namespace App\Http\Controllers\Api\Bff\Widgets;

class DocumentQueueWidget extends BffWidget
{
    protected function getType(): string
    {
        return 'DocumentQueue';
    }

    public static function make(string $title, array $documents): self
    {
        $widget = new self();
        $widget->setProp('title', $title)
               ->setProp('documents', $documents);
        return $widget;
    }
}

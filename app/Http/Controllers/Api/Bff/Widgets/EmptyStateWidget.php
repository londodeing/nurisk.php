<?php

namespace App\Http\Controllers\Api\Bff\Widgets;

class EmptyStateWidget extends BffWidget
{
    protected function getType(): string
    {
        return 'EmptyState';
    }

    public static function make(string $title, string $message, string $icon = 'inbox'): self
    {
        $widget = new self();
        $widget->setProp('title', $title)
               ->setProp('message', $message)
               ->setProp('icon', $icon);
        return $widget;
    }
}

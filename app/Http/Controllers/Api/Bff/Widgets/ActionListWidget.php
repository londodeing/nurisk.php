<?php

namespace App\Http\Controllers\Api\Bff\Widgets;

class ActionListWidget extends BffWidget
{
    protected function getType(): string
    {
        return 'ActionList';
    }

    public static function make(string $title, array $items): self
    {
        $widget = new self();
        $widget->setProp('title', $title)
               ->setProp('items', $items);
        return $widget;
    }
}

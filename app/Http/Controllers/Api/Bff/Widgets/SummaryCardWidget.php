<?php

namespace App\Http\Controllers\Api\Bff\Widgets;

class SummaryCardWidget extends BffWidget
{
    protected function getType(): string
    {
        return 'SummaryCard';
    }

    public static function make(string $title, string $value, string $icon = '', string $colorHex = '#3B82F6'): self
    {
        $widget = new self();
        $widget->setProp('title', $title)
               ->setProp('value', $value)
               ->setProp('icon', $icon)
               ->setProp('color_hex', $colorHex);
        return $widget;
    }
}

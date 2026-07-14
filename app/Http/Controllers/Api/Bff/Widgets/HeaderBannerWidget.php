<?php

namespace App\Http\Controllers\Api\Bff\Widgets;

class HeaderBannerWidget extends BffWidget
{
    protected function getType(): string
    {
        return 'HeaderBanner';
    }

    public static function make(string $message, string $level = 'info'): self
    {
        $widget = new self();
        $widget->setProp('message', $message)
               ->setProp('level', $level);
        return $widget;
    }
}

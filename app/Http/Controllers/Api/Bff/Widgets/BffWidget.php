<?php

namespace App\Http\Controllers\Api\Bff\Widgets;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

abstract class BffWidget implements Arrayable
{
    protected string $id;
    protected string $type;
    protected array $props = [];
    protected array $actions = [];

    public function __construct()
    {
        $this->id = 'wdg_' . Str::random(8);
        $this->type = $this->getType();
    }

    /**
     * Define the Widget Type that matches Flutter's WidgetResolver
     */
    abstract protected function getType(): string;

    public function setProp(string $key, $value): self
    {
        $this->props[$key] = $value;
        return $this;
    }

    public function setAction(string $key, array $actionDefinition): self
    {
        $this->actions[$key] = $actionDefinition;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'props' => (object) $this->props,
            'actions' => (object) $this->actions,
        ];
    }
}

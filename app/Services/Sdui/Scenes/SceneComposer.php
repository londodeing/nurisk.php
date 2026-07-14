<?php

namespace App\Services\Sdui\Scenes;

interface SceneComposer
{
    /**
     * Compose the scene SDUI array response.
     *
     * @return array
     */
    public function compose(): array;
}

<?php

namespace App\Services\Sdui\Runtime\Components;

use App\Services\Sdui\Runtime\Nodes\ComponentNode;
use App\Services\Sdui\Runtime\Runtime;

class ProfileComponent
{
    public static function build(string $name, string $role, string $avatarUrl = null): ComponentNode
    {
        return Runtime::component('profile_card')
            ->withRenderNode(
                Runtime::render('profile_container', 'container', ['padding' => 16])
                    ->withChild(
                        Runtime::render('profile_row', 'row', ['spacing' => 12])
                            ->withChild(
                                Runtime::render('profile_avatar', 'avatar', ['image' => $avatarUrl ?? 'default_avatar.png'])
                            )
                            ->withChild(
                                Runtime::render('profile_text_col', 'column', ['spacing' => 4])
                                    ->withChild(
                                        Runtime::render('profile_name', 'text', ['text' => $name, 'style' => 'headline'])
                                    )
                                    ->withChild(
                                        Runtime::render('profile_role', 'text', ['text' => $role, 'style' => 'caption'])
                                    )
                            )
                    )
            );
    }
}

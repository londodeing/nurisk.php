<?php
namespace App\Services\Dashboard\Builders;
class NewsBlockBuilder implements BlockBuilderInterface {
    public function build(array $block, \App\Services\Dashboard\DashboardProjectionService $projection): array {
        return [
            'type' => 'List',
            'props' => ['title' => 'Berita Terkini'],
            'children' => []
        ];
    }
}

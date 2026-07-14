<?php
namespace App\Services\Dashboard\Builders;
interface BlockBuilderInterface {
    public function build(array $block, \App\Services\Dashboard\DashboardProjectionService $projection): array;
}

<?php

namespace App\Services\Sdui\Runtime\Screens;

use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use App\Services\Sdui\Runtime\Runtime;
use App\Services\Sdui\Runtime\Sections\IdentitySection;
use App\Services\Sdui\Runtime\Sections\StatusOperasionalSection;
use App\Services\Sdui\Runtime\Sections\CommandCenterSection;
use App\Services\Sdui\Runtime\Sections\MenuSection;

class AccountWorkspaceScreen
{
    public static function build(
        ?array $profil,
        ?array $jabatanAktif,
        array $keahlian,
        array $penugasan,
        ?array $commandCenter,
        ?array $alertInsiden
    ): ScreenNode {
        $screen = Runtime::screen('account_workspace', 'Akun & Pusat Komando');

        // Always build IdentitySection (holds the Guest card if profil is null)
        $screen = $screen->withSection(IdentitySection::build($profil, $jabatanAktif, $keahlian, $penugasan));

        if ($profil) {
            $namaPeran = $profil['nama_peran'] ?? 'publik';
            if ($namaPeran !== 'publik') {
                $screen = $screen->withSection(StatusOperasionalSection::build($penugasan, $profil));
                if ($commandCenter !== null) {
                    $screen = $screen->withSection(CommandCenterSection::build($commandCenter, $alertInsiden, $profil));
                }
            }
            $screen = $screen->withSection(MenuSection::build($profil));
        }

        return $screen;
    }
}

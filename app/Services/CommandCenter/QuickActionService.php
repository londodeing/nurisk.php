<?php

namespace App\Services\CommandCenter;

use App\Models\AuthUser;

class QuickActionService
{
    public function getActions(AuthUser $user, string $context = 'dashboard'): array
    {
        $role = $user->peran?->nama_peran;

        return match ($role) {
            'super_admin', 'pwnu' => $this->forPwnu(),
            'pcnu' => $this->forPcnu(),
            'relawan' => $this->forRelawan(),
            default => [],
        };
    }

    private function forPwnu(): array
    {
        return [
            ['action' => 'approve-surat', 'label' => 'Approve Surat', 'route' => url('/surat'), 'icon' => 'bi-check-circle', 'color' => 'success'],
            ['action' => 'finalisasi-pleno', 'label' => 'Finalisasi Pleno', 'route' => url('/insiden'), 'icon' => 'bi-check2-square', 'color' => 'success'],
            ['action' => 'hubungi-pcnu', 'label' => 'Hubungi PCNU', 'route' => '#', 'icon' => 'bi-telephone', 'color' => 'outline-secondary'],
        ];
    }

    private function forPcnu(): array
    {
        return [
            ['action' => 'buat-sitrep', 'label' => 'Buat Sitrep', 'route' => url('/insiden'), 'icon' => 'bi-file-earmark-text', 'color' => 'primary'],
            ['action' => 'assign-personel', 'label' => 'Assign Personel', 'route' => url('/insiden'), 'icon' => 'bi-person-plus', 'color' => 'info'],
            ['action' => 'aktivasi-posko', 'label' => 'Aktivasi Posko', 'route' => '#', 'icon' => 'bi-geo-alt', 'color' => 'success'],
        ];
    }

    private function forRelawan(): array
    {
        return [
            ['action' => 'checkin', 'label' => 'Check-in', 'route' => '#', 'icon' => 'bi-box-arrow-in-right', 'color' => 'success'],
            ['action' => 'checkout', 'label' => 'Check-out', 'route' => '#', 'icon' => 'bi-box-arrow-right', 'color' => 'danger'],
            ['action' => 'update-progres', 'label' => 'Update Progres', 'route' => '#', 'icon' => 'bi-arrow-up-circle', 'color' => 'primary'],
        ];
    }
}

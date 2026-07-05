<?php

namespace App\Services\Media;

class MediaPolicy
{
    private const ENTITY_RULES = [
        'laporan' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size' => 10 * 1024 * 1024, // 10 MB
            'allow_svg' => false,
        ],
        'aset' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
            'mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
            'max_size' => 10 * 1024 * 1024,
            'allow_svg' => false,
        ],
        'incident' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size' => 10 * 1024 * 1024,
            'allow_svg' => false,
        ],
        'surat' => [
            'extensions' => ['pdf'],
            'mimes' => ['application/pdf'],
            'max_size' => 20 * 1024 * 1024,
            'allow_svg' => false,
        ],
        'volunteer' => [
            'extensions' => ['jpg', 'jpeg', 'png'],
            'mimes' => ['image/jpeg', 'image/png'],
            'max_size' => 5 * 1024 * 1024,
            'allow_svg' => false,
        ],
        'logo' => [
            'extensions' => ['svg', 'png'],
            'mimes' => ['image/svg+xml', 'image/png'],
            'max_size' => 2 * 1024 * 1024,
            'allow_svg' => true,
        ],
    ];

    public function getAllowedMimes(string $entityType): array
    {
        return $this->rule($entityType)['mimes'] ?? [];
    }

    public function getAllowedExtensions(string $entityType): array
    {
        return $this->rule($entityType)['extensions'] ?? [];
    }

    public function getMaxSize(string $entityType): int
    {
        return $this->rule($entityType)['max_size'] ?? 10 * 1024 * 1024;
    }

    public function isMimeAllowed(string $entityType, string $mimeType): bool
    {
        return in_array($mimeType, $this->getAllowedMimes($entityType), true);
    }

    public function isExtensionAllowed(string $entityType, string $extension): bool
    {
        return in_array(strtolower($extension), $this->getAllowedExtensions($entityType), true);
    }

    public function validate(string $entityType, string $mimeType, string $extension, int $size): array
    {
        $errors = [];

        if (!isset(self::ENTITY_RULES[$entityType])) {
            return ["Unknown entity type: $entityType"];
        }

        if (!$this->isMimeAllowed($entityType, $mimeType)) {
            $errors[] = "MIME type $mimeType not allowed for $entityType";
        }

        if (!$this->isExtensionAllowed($entityType, $extension)) {
            $errors[] = "Extension $extension not allowed for $entityType";
        }

        if ($size > $this->getMaxSize($entityType)) {
            $maxMb = $this->getMaxSize($entityType) / 1024 / 1024;
            $errors[] = "File size exceeds {$maxMb}MB limit for $entityType";
        }

        return $errors;
    }

    public function toValidationRules(string $entityType): array
    {
        return [
            'mimes:' . implode(',', $this->getAllowedExtensions($entityType)),
            'max:' . ($this->getMaxSize($entityType) / 1024),
        ];
    }

    private function rule(string $entityType): array
    {
        return self::ENTITY_RULES[$entityType] ?? self::ENTITY_RULES['laporan'];
    }
}

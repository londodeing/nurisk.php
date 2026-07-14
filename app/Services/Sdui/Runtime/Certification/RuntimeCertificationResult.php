<?php

namespace App\Services\Sdui\Runtime\Certification;

use App\Services\Sdui\Runtime\Contracts\RuntimeNode;

final readonly class RuntimeCertificationResult
{
    /**
     * @param RuntimeNode $certifiedRuntime
     * @param string[] $errors
     * @param string[] $warnings
     */
    public function __construct(
        public RuntimeNode $certifiedRuntime,
        public array $errors = [],
        public array $warnings = []
    ) {
    }

    public function isCertified(): bool
    {
        return count($this->errors) === 0;
    }
}

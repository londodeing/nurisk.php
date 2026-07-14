<?php

namespace App\Services\Sdui\Runtime\Certification;

use App\Services\Sdui\Runtime\Contracts\RuntimeNode;

class RuntimeCertificationEngine
{
    public function __construct(
        private StructuralValidator $structuralValidator,
        private SemanticValidator $semanticValidator,
        private RuntimeNormalizer $normalizer
    ) {
    }

    public function certify(RuntimeNode $root): RuntimeCertificationResult
    {
        $errors = [];
        $warnings = [];

        // 1. Structural Validation
        $this->structuralValidator->validate($root, $errors);

        // 2. Semantic Validation
        $this->semanticValidator->validate($root, $errors);

        // Jika ada error fatal, kembalikan sebelum normalisasi
        if (!empty($errors)) {
            return new RuntimeCertificationResult($root, $errors, $warnings);
        }

        // 3. Normalization (Membuat Certified Tree baru)
        $certifiedTree = $this->normalizer->normalize($root);

        return new RuntimeCertificationResult($certifiedTree, $errors, $warnings);
    }
}

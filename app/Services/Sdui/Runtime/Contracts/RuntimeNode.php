<?php

namespace App\Services\Sdui\Runtime\Contracts;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;

/**
 * Interface dasar untuk semua Runtime Nodes.
 * Implementasi wajib Immutable (mengembalikan instance baru saat dimutasi).
 */
interface RuntimeNode
{
    public function getId(): NodeId;
    
    public function getMetadata(): ?NodeMetadata;
    
    public function getVisibility(): NodeVisibility;
    
    public function getState(): NodeState;

    /**
     * Mengembalikan instance baru dari RuntimeNode ini dengan metadata yang diperbarui.
     */
    public function withMetadata(?NodeMetadata $metadata): self;

    /**
     * Mengembalikan instance baru dari RuntimeNode ini dengan visibility yang diperbarui.
     */
    public function withVisibility(NodeVisibility $visibility): self;

    /**
     * Mengembalikan instance baru dari RuntimeNode ini dengan state yang diperbarui.
     */
    public function withState(NodeState $state): self;

    /**
     * Mengembalikan semua anak node jika ada.
     * @return RuntimeNode[]
     */
    public function getChildren(): array;
}

<?php

namespace App\Models\Concerns;

/**
 * Helpers communs aux modèles portant un fichier (mime + size).
 */
trait HasFileMetadata
{
    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function humanSize(): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $size = (float) $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1) . ' ' . $units[$i];
    }
}

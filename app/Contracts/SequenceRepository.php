<?php

namespace App\Contracts;

interface SequenceRepository
{
    /**
     * @param  class-string  $modelClass
     */
    public function generate(
        string $type,
        string $modelClass,
        ?string $dateString = null,
        ?string $modelColumn = 'number',
    ): string;

    public function update(
        string $type,
        string $newNumber,
        ?string $date = null,
    ): void;
}

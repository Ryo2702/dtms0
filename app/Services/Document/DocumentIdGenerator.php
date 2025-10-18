<?php

namespace App\Services\Document;

use Illuminate\Support\Str;

class DocumentIdGenerator
{
    public function generate(): string
    {
        return 'DTN-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}

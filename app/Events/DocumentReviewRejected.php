<?php

namespace App\Events;

use App\Models\DocumentReview;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentReviewRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DocumentReview $review
    ) {}
}

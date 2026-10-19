<?php

namespace App\Jobs;

use App\Services\SellerReviewNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RetrySellerReviewEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public int $applicationId, public int $moderatorId, public string $revision, public bool $correction) {}

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(SellerReviewNotifier $notifier): void
    {
        $notifier->deliver($this->applicationId, $this->moderatorId, $this->revision, $this->correction);
    }
}

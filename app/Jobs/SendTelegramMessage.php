<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;
    public int $timeout = 30;

    public function __construct(public string $message)
    {
    }

    public function handle(TelegramService $telegram): void
    {
        $telegram->send($this->message);
    }
}
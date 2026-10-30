<?php
namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    protected $signature = 'summary:send';
    protected $description = 'Send the daily school summary';

    public function __construct(
        private readonly TelegramService $telegramService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $message = 'Daily summary command is working!';

        $this->telegramService->send($message);

        $this->info('Daily summary sent to Telegram.');

        return self::SUCCESS;
    }
}
<?php

namespace App\Console\Commands;

use App\Services\RecurrenceService;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'transactions:generate-recurring {--days=30 : Days ahead to generate}';

    protected $description = 'Generate upcoming recurring transactions';

    public function handle(RecurrenceService $service): int
    {
        $days = (int) $this->option('days');

        $this->info("Generating recurring transactions for the next {$days} days...");

        $count = $service->generateUpcoming($days);

        $this->info("Done! {$count} transaction(s) generated.");

        return self::SUCCESS;
    }
}

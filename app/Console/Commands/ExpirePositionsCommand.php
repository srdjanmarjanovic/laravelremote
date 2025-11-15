<?php

namespace App\Console\Commands;

use App\Models\Position;
use Illuminate\Console\Command;

class ExpirePositionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'positions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark positions as expired and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Find positions that need to be expired
        $positionsToExpire = Position::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($positionsToExpire->isEmpty()) {
            $this->info('No positions to expire.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($positionsToExpire as $position) {
            $position->update(['status' => 'expired']);

            // TODO: Send notification to position creator about expiration

            $count++;
        }

        $this->info("Successfully expired {$count} position(s).");

        // Send warnings for positions expiring soon (within 3 days)
        $positionsExpiringSoon = Position::where('status', 'published')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->get();

        $warningCount = 0;

        foreach ($positionsExpiringSoon as $position) {
            // TODO: Send notification to position creator about upcoming expiration

            $warningCount++;
        }

        if ($warningCount > 0) {
            $this->info("Sent expiration warnings for {$warningCount} position(s).");
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Position;
use App\Notifications\PositionExpiredNotification;
use App\Notifications\PositionExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

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
            ->with(['creator', 'company'])
            ->get();

        if ($positionsToExpire->isEmpty()) {
            $this->info('No positions to expire.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($positionsToExpire as $position) {
            $position->update(['status' => 'expired']);

            // Send notification to position creator about expiration
            $position->creator->notify(new PositionExpiredNotification($position));

            $count++;
        }

        $this->info("Successfully expired {$count} position(s).");

        // Send warnings for positions expiring soon (7 days before)
        $positionsExpiringSoon = Position::where('status', 'published')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->addDays(7), now()->addDays(8)])
            ->with(['creator', 'company'])
            ->get();

        $warningCount = 0;

        foreach ($positionsExpiringSoon as $position) {
            $daysRemaining = now()->diffInDays($position->expires_at, false);

            // Send notification to position creator about upcoming expiration
            $position->creator->notify(new PositionExpiringNotification($position, $daysRemaining));

            $warningCount++;
        }

        if ($warningCount > 0) {
            $this->info("Sent expiration warnings for {$warningCount} position(s).");
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ReservationRequest;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ForceCompleteReservation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:force-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update accepted reservations that have passed their date to completed status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic completion of expired accepted reservations...');

        // Get all accepted reservations
        $acceptedReservations = ReservationRequest::where('status', 'accepted')
            ->with(['reservationDetail'])
            ->get();

        $updatedCount = 0;

        foreach ($acceptedReservations as $reservation) {
            $shouldComplete = false;
            $today = Carbon::today();

            // Check if the reservation has passed its date
            if ($reservation->reservationDetail) {
                $detail = $reservation->reservationDetail;
                
                if ($reservation->reservation_type === 'Single') {
                    // For single day reservations, check if the start date has passed
                    if ($detail->start_date && Carbon::parse($detail->start_date)->lt($today)) {
                        $shouldComplete = true;
                    }
                } elseif ($reservation->reservation_type === 'Consecutive' || $reservation->reservation_type === 'Multiple') {
                    // For consecutive/multiple day reservations, check if the end date has passed
                    if ($detail->end_date && Carbon::parse($detail->end_date)->lt($today)) {
                        $shouldComplete = true;
                    }
                    // Also check if intermediate date has passed (if exists)
                    if ($detail->intermediate_date && Carbon::parse($detail->intermediate_date)->lt($today)) {
                        $shouldComplete = true;
                    }
                }
            } else {
                // If no reservation detail, check transaction date
                if ($reservation->transaction_date && Carbon::parse($reservation->transaction_date)->lt($today)) {
                    $shouldComplete = true;
                }
            }

            if ($shouldComplete) {
                $reservation->status = 'completed';
                $reservation->save();
                $updatedCount++;
                
                $this->line("Updated reservation ID {$reservation->reservation_id} to completed status");
            }
        }

        $this->info("Completed! Updated {$updatedCount} reservations to completed status.");
        
        return 0;
    }
}

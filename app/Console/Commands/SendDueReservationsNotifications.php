<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Notifications\HostReservationStarting;
use App\Notifications\UserReservationStarting;
use Illuminate\Console\Command;

class SendDueReservationsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ergodnc:send-reservations-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notification that a reservation is for today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Reservation::query()
            ->with('office.user')
            ->where('status', Reservation::STATUS_ACTIVE)
            ->where('start_date', now()->toDateString())
            ->each(function ($reservation) {
                    Notification::send($reservation->user, new UserReservationStarting($reservation));
                    Notification::send($reservation->office->user, new HostReservationStarting($reservation));
            });


        return 0;
    }
}

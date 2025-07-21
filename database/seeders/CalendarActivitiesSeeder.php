<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = date('Y'); // Gets current year
        $month = 7; // July

        // Get the number of days in July
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Array to hold our calendar activities
        $activities = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dayOfWeek = $date->format('l'); // Gets full day name (e.g., Monday)

            $activities[] = [
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all activities in a single query
        DB::table('calendar_activities')->insert($activities);
    }
}

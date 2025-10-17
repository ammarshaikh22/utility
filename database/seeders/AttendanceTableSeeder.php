<?php

namespace Database\Seeders;

/**
 * Seeder for Attendance system - creates realistic attendance records for employees
 */

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        // Initialize Faker for generating fake data
        $faker = \Faker\Factory::create();

        // Get all EMPLOYEE user IDs for this company (via role_user pivot table)
        $userIds = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee')           // Only employees (not admins)
            ->where('users.company_id', $companyId)     // Specific company
            ->pluck('users.id')
            ->toArray();

        // Get ADMIN user ID for this company (to set as 'added_by')
        $adminId = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')              // Only admin role
            ->where('users.company_id', $companyId)     // Specific company
            ->value('users.id');

        // Prepare bulk insert data array
        $data = [];

        // Create 1 attendance record per employee
        foreach ($userIds as $userId) {
            // Random date: this month OR this year (up to now)
            $date = $faker->randomElement([
                $faker->dateTimeThisMonth()->format('Y-m-d'),     // This month
                $faker->dateTimeThisYear('now')->format('Y-m-d')  // This year (up to now)
            ]);
            
            // Random start time: 9AM-1PM
            $start = $date . 'T' . $faker->randomElement(['09:00', '10:00', '11:00', '12:00', '13:00']) . '+00:00';

            // Clock-in with realistic delay: mostly on-time, some late/early
            $clockIn = Carbon::parse($start)
                ->addMinutes($faker->randomElement([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 10, 15, -20, 45, 120])) // 70% on-time, 30% late/early
                ->format('Y-m-d H:i:s');
            
            $clockInIp = $faker->ipv4; // Random IPv4 address for clock-in

            $data[] = [
                'user_id' => $userId,                           // Employee ID
                'company_id' => $companyId,                     // Specific company
                'half_day' => 'no',                             // Default: full day
                'late' => $faker->randomElement(['yes', 'no']), // Random late status
                'clock_in_time' => $clockIn,                    // Calculated clock-in time
                'clock_out_time' => Carbon::parse($clockIn)     // Clock-out: 1-9 hours after clock-in
                    ->addHours($faker->numberBetween(1, 9))
                    ->format('Y-m-d H:i:s'),
                'clock_in_ip' => $clockInIp,                    // IP address at clock-in
                'clock_out_ip' => $clockInIp,                   // Same IP for clock-out (same device)
                'created_at' => $faker->dateTimeThisYear(),     // Record creation timestamp
                'added_by' => $adminId,                         // Admin who added the record
            ];
        }

        // Bulk insert all attendance records at once
        Attendance::insert($data);
    }

}
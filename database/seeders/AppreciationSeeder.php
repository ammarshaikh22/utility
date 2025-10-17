<?php

namespace Database\Seeders;

/**
 * Seeder for Appreciation system - creates Awards and Appreciation records
 */

use App\Models\Appreciation;
use App\Models\Award;
use App\Models\AwardIcon;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppreciationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        // Get all available award icon IDs
        $awardIcons = AwardIcon::all()->pluck('id')->toArray();
        
        // Predefined color palette for award icons
        $iconColors = ['#282E33', '#495E67', '#FF3838', '#3DADDD', '#387B1C', '#7B1C2E'];

        // Predefined list of 16 award titles
        $awardList = [
            'Best Team Player',
            'Most Innovative Project',
            'Best Technical Solution',
            'Best Customer Service',
            'Employee of the Month',
            'Best Mentor',
            'Top Sales Performer',
            'Best Project Manager',
            'Top Code Contributor',
            'Most Improved Employee',
            'Best New Hire',
            'Best Presentation',
            'Best Quality Control',
            'Best Technical Writer',
            'Most Valuable Employee',
            'Star Performer Award'
        ];

        // Prepare bulk insert data for Awards
        $awardInsert = [];

        // Create 16 awards with random icons and colors
        foreach ($awardList as $award) {
            $awardInsert[] = [
                'award_icon_id' => $awardIcons[array_rand($awardIcons)],    // Random award icon
                'color_code' => $iconColors[array_rand($iconColors)],       // Random color from palette
                'title' => $award,                                          // Award title from predefined list
                'company_id' => $companyId,                                 // Specific company
            ];
        }

        // Bulk insert all awards at once
        Award::insert($awardInsert);

        // Get all employee IDs for this company
        $employees = User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();
        
        // Get all newly created award IDs for this company
        $awards = Award::where('company_id', $companyId)->get()->pluck('id')->toArray();

        // Generate random award date (this month OR this year)
        $date = fake()->randomElement([
            fake()->dateTimeThisMonth()->format('Y-m-d'),              // This month
            fake()->dateTimeThisYear()->format('Y-m-d')                // This year
        ]);

        // Prepare bulk insert data for 10 appreciations
        $appreciations = [];

        // Create 10 appreciation records
        for ($i = 0; $i < 10; $i++) {
            $appreciations[] = [
                'award_to' => $employees[array_rand($employees)],          // Random employee receiving award
                'award_id' => $awards[array_rand($awards)],                // Random award from newly created list
                'company_id' => $companyId,                                // Specific company
                'award_date' => $date,                                     // Same random date for all
                'added_by' => $employees[array_rand($employees)],          // Random employee giving award
            ];
        }

        // Bulk insert all appreciations at once
        Appreciation::insert($appreciations);
    }

}
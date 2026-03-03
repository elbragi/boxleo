<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use App\Models\Unit;

class SeedBoxleo2026Calendar extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unit = Unit::first();
        if (!$unit) {
            $this->command->error('No Unit found. Please create a unit/branch first.');
            return;
        }

        $unitId = $unit->id;

        // Grouping events because 'name' has a UNIQUE constraint in this schema
        $events = [
            // January
            ['name' => 'New Year 2026', 'date' => '2026-01-01', 'is_recurring' => 1],
            ['name' => 'Officers Meeting - Jan', 'date' => '2026-01-15', 'is_recurring' => 0],
            ['name' => 'January Birthday Celebrations', 'date' => '2026-01-30', 'is_recurring' => 0],

            // February
            ['name' => 'Officers Meeting - Feb', 'date' => '2026-02-12', 'is_recurring' => 0],
            ['name' => 'Riders Training - Feb 27', 'date' => '2026-02-27', 'is_recurring' => 0],
            ['name' => 'Riders Training - Feb 28', 'date' => '2026-02-28', 'is_recurring' => 0],
            ['name' => 'February Birthday Celebrations', 'date' => '2026-02-27', 'is_recurring' => 0],

            // March
            ['name' => 'Compliance & Policy Refresher - Mar 04', 'date' => '2026-03-04', 'is_recurring' => 0],
            ['name' => 'Riders Training - Mar 06', 'date' => '2026-03-06', 'is_recurring' => 0],
            ['name' => 'Riders Training - Mar 07', 'date' => '2026-03-07', 'is_recurring' => 0],
            ['name' => 'Compliance & Policy Refresher - Mar 11', 'date' => '2026-03-11', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Mar', 'date' => '2026-03-12', 'is_recurring' => 0],
            ['name' => 'Riders Training - Mar 13', 'date' => '2026-03-13', 'is_recurring' => 0],
            ['name' => 'Quarterly Inter country Meeting - Mar', 'date' => '2026-03-13', 'is_recurring' => 0],
            ['name' => 'Riders Training - Mar 14', 'date' => '2026-03-14', 'is_recurring' => 0],
            ['name' => 'Fourth Quarter Managers Meeting', 'date' => '2026-03-20', 'is_recurring' => 0],
            ['name' => 'Quarterly Employee engagement (by CEO) - Mar', 'date' => '2026-03-26', 'is_recurring' => 0],
            ['name' => 'Agents Training - Mar 27', 'date' => '2026-03-27', 'is_recurring' => 0],
            ['name' => 'March Birthday Celebrations', 'date' => '2026-03-27', 'is_recurring' => 0],
            ['name' => 'Agents Training - Mar 28', 'date' => '2026-03-28', 'is_recurring' => 0],
            ['name' => 'Team Building - Mar 28', 'date' => '2026-03-28', 'is_recurring' => 0],

            // April
            ['name' => 'Good Friday 2026', 'date' => '2026-04-03', 'is_recurring' => 1],
            ['name' => 'Easter Monday 2026', 'date' => '2026-04-05', 'is_recurring' => 1],
            ['name' => 'Officers Meeting - Apr', 'date' => '2026-04-09', 'is_recurring' => 0],
            ['name' => 'Agents Training - Apr 10', 'date' => '2026-04-10', 'is_recurring' => 0],
            ['name' => 'Tele sales Agents Training - Apr 10', 'date' => '2026-04-10', 'is_recurring' => 0],
            ['name' => 'Agents Training - Apr 11', 'date' => '2026-04-11', 'is_recurring' => 0],
            ['name' => 'Tele sales Agents Training - Apr 11', 'date' => '2026-04-11', 'is_recurring' => 0],
            ['name' => 'Tele sales Agents Training - Apr 17', 'date' => '2026-04-17', 'is_recurring' => 0],
            ['name' => 'Tele sales Agents Training - Apr 18', 'date' => '2026-04-18', 'is_recurring' => 0],
            ['name' => 'Tele sales Agents Training - Apr 24', 'date' => '2026-04-24', 'is_recurring' => 0],
            ['name' => 'April Birthday Celebrations', 'date' => '2026-04-24', 'is_recurring' => 0],
            ['name' => 'Tele sales Agents Training - Apr 25', 'date' => '2026-04-25', 'is_recurring' => 0],
            ['name' => 'Financial Literacy Training - Apr 29', 'date' => '2026-04-29', 'is_recurring' => 0],

            // May
            ['name' => 'Labour Day 2026', 'date' => '2026-05-01', 'is_recurring' => 1],
            ['name' => 'Financial Literacy Training - May 06', 'date' => '2026-05-06', 'is_recurring' => 0],
            ['name' => 'CSR/Anniversary/ Team Building', 'date' => '2026-05-10', 'is_recurring' => 0],
            ['name' => 'Financial Literacy Training - May 13', 'date' => '2026-05-13', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - May', 'date' => '2026-05-14', 'is_recurring' => 0],
            ['name' => 'Customer Experience Training - May 20', 'date' => '2026-05-20', 'is_recurring' => 0],
            ['name' => 'International HR Day 2026', 'date' => '2026-05-20', 'is_recurring' => 1],
            ['name' => 'Customer Experience Training - May 27', 'date' => '2026-05-27', 'is_recurring' => 0],
            ['name' => 'May Birthday Celebrations', 'date' => '2026-05-29', 'is_recurring' => 0],

            // June
            ['name' => 'Madaraka Day 2026', 'date' => '2026-06-01', 'is_recurring' => 1],
            ['name' => 'Customer Experience Training - Jun 03', 'date' => '2026-06-03', 'is_recurring' => 0],
            ['name' => 'Customer Experience Training - Jun 10', 'date' => '2026-06-10', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Jun', 'date' => '2026-06-11', 'is_recurring' => 0],
            ['name' => 'Quarterly Inter country Meeting - Jun', 'date' => '2026-06-12', 'is_recurring' => 0],
            ['name' => 'Customer Experience Training - Jun 17', 'date' => '2026-06-17', 'is_recurring' => 0],
            ['name' => 'First Quarter Managers Meeting', 'date' => '2026-06-19', 'is_recurring' => 0],
            ['name' => 'Quarterly Employee engagement (by CEO) - Jun', 'date' => '2026-06-24', 'is_recurring' => 0],
            ['name' => 'June Birthday Celebrations', 'date' => '2026-06-26', 'is_recurring' => 0],

            // July
            ['name' => 'System Awareness Training - Jul 01', 'date' => '2026-07-01', 'is_recurring' => 0],
            ['name' => 'System Awareness Training - Jul 08', 'date' => '2026-07-08', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Jul', 'date' => '2026-07-09', 'is_recurring' => 0],
            ['name' => 'System Awareness Training - Jul 15', 'date' => '2026-07-15', 'is_recurring' => 0],
            ['name' => 'July Birthday Celebrations', 'date' => '2026-07-31', 'is_recurring' => 0],

            // August
            ['name' => 'Training by CFO - Aug 05', 'date' => '2026-08-05', 'is_recurring' => 0],
            ['name' => 'Training by CFO - Aug 12', 'date' => '2026-08-12', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Aug', 'date' => '2026-08-13', 'is_recurring' => 0],
            ['name' => 'Training by CFO - Aug 19', 'date' => '2026-08-19', 'is_recurring' => 0],
            ['name' => 'Quarterly Inter country Meeting - Aug', 'date' => '2026-08-21', 'is_recurring' => 0],
            ['name' => 'August Birthday Celebrations', 'date' => '2026-08-28', 'is_recurring' => 0],

            // September
            ['name' => 'Training by COO - Sep 02', 'date' => '2026-09-02', 'is_recurring' => 0],
            ['name' => 'Training by COO - Sep 09', 'date' => '2026-09-09', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Sep', 'date' => '2026-09-10', 'is_recurring' => 0],
            ['name' => 'Training by COO - Sep 16', 'date' => '2026-09-16', 'is_recurring' => 0],
            ['name' => 'Quarterly Employee engagement (by CEO) - Sep', 'date' => '2026-09-16', 'is_recurring' => 0],
            ['name' => 'Second Quarter Managers Meeting', 'date' => '2026-09-18', 'is_recurring' => 0],
            ['name' => 'September Birthday Celebrations', 'date' => '2026-09-25', 'is_recurring' => 0],
            ['name' => 'Team Building - Sep 26', 'date' => '2026-09-26', 'is_recurring' => 0],

            // October
            ['name' => 'Customer Service Week 05', 'date' => '2026-10-05', 'is_recurring' => 0],
            ['name' => 'Customer Service Week 06', 'date' => '2026-10-06', 'is_recurring' => 0],
            ['name' => 'Customer Service Week 07', 'date' => '2026-10-07', 'is_recurring' => 0],
            ['name' => 'Customer Service Week 08', 'date' => '2026-10-08', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Oct', 'date' => '2026-10-08', 'is_recurring' => 0],
            ['name' => 'Customer Service Week 09', 'date' => '2026-10-09', 'is_recurring' => 0],
            ['name' => 'Mazingira Day 2026', 'date' => '2026-10-10', 'is_recurring' => 1],
            ['name' => 'Customer Service Week 10', 'date' => '2026-10-10', 'is_recurring' => 0],
            ['name' => 'Mashujaa Day 2026', 'date' => '2026-10-20', 'is_recurring' => 1],
            ['name' => 'October Birthday Celebrations', 'date' => '2026-10-30', 'is_recurring' => 0],

            // November
            ['name' => 'November Birthday Celebrations', 'date' => '2026-11-27', 'is_recurring' => 0],
            ['name' => 'Appraisals Start 2026', 'date' => '2026-11-05', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Nov', 'date' => '2026-11-12', 'is_recurring' => 0],

            // December
            ['name' => 'Quarterly Inter country Meeting - Dec', 'date' => '2026-12-04', 'is_recurring' => 0],
            ['name' => 'Officers Meeting - Dec', 'date' => '2026-12-09', 'is_recurring' => 0],
            ['name' => 'Third Quarter Managers Meeting', 'date' => '2026-12-11', 'is_recurring' => 0],
            ['name' => 'Jamhuri Day 2026', 'date' => '2026-12-12', 'is_recurring' => 1],
            ['name' => 'Deadline for End of Year Appraisals', 'date' => '2026-12-12', 'is_recurring' => 0],
            ['name' => 'December Birthday Celebrations', 'date' => '2026-12-18', 'is_recurring' => 0],
            ['name' => 'End of Year Party 2026', 'date' => '2026-12-19', 'is_recurring' => 0],
            ['name' => 'Shift 1 Holiday Break - Dec 21', 'date' => '2026-12-21', 'is_recurring' => 0],
            ['name' => 'Shift 1 Holiday Break - Dec 22', 'date' => '2026-12-22', 'is_recurring' => 0],
            ['name' => 'Shift 1 Holiday Break - Dec 23', 'date' => '2026-12-23', 'is_recurring' => 0],
            ['name' => 'Shift 1 Holiday Break - Dec 24', 'date' => '2026-12-24', 'is_recurring' => 0],
            ['name' => 'Shift 1 Holiday Break - Dec 25', 'date' => '2026-12-25', 'is_recurring' => 0],
            ['name' => 'Shift 1 Holiday Break - Dec 26', 'date' => '2026-12-26', 'is_recurring' => 0],
            ['name' => 'Shift 2 Holiday Break - Dec 28', 'date' => '2026-12-28', 'is_recurring' => 0],
            ['name' => 'Shift 2 Holiday Break - Dec 29', 'date' => '2026-12-29', 'is_recurring' => 0],
            ['name' => 'Shift 2 Holiday Break - Dec 30', 'date' => '2026-12-30', 'is_recurring' => 0],
            ['name' => 'Shift 2 Holiday Break - Dec 31', 'date' => '2026-12-31', 'is_recurring' => 0],
            ['name' => 'Shift 2 Holiday Break - Jan 01', 'date' => '2027-01-01', 'is_recurring' => 0],
            ['name' => 'Shift 2 Holiday Break - Jan 02', 'date' => '2027-01-02', 'is_recurring' => 0],
        ];

        foreach ($events as $event) {
            Holiday::updateOrCreate(
                ['name' => $event['name']],
                ['date' => $event['date'], 'unit_id' => $unitId, 'is_recurring' => $event['is_recurring']]
            );
        }
    }
}

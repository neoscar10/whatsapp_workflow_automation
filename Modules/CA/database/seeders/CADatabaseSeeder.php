<?php

namespace Modules\CA\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAComplianceDeadline;

class CADatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // 1. Seed Business Types
        $businessTypes = [
            ['name' => 'Proprietorship', 'slug' => 'proprietorship', 'description' => 'A business owned and run by one person.'],
            ['name' => 'Partnership Firm', 'slug' => 'partnership-firm', 'description' => 'A business owned by two or more people.'],
            ['name' => 'Limited Liability Partnership (LLP)', 'slug' => 'llp', 'description' => 'A partnership where some or all partners have limited liabilities.'],
            ['name' => 'Private Limited Company', 'slug' => 'private-limited', 'description' => 'A privately held business entity.'],
            ['name' => 'Public Limited Company', 'slug' => 'public-limited', 'description' => 'A company whose ownership is distributed amongst general public shareholders.'],
            ['name' => 'One Person Company (OPC)', 'slug' => 'opc', 'description' => 'A company with only one member.'],
            ['name' => 'Trust / Society / NGO', 'slug' => 'trust-ngo', 'description' => 'Non-profit organizations.'],
            ['name' => 'Section 8 Company', 'slug' => 'section-8', 'description' => 'A company established for promoting commerce, art, science, sports, education, etc.'],
            ['name' => 'Hindu Undivided Family (HUF)', 'slug' => 'huf', 'description' => 'A legal entity that consists of all persons lineally descended from a common ancestor.'],
        ];

        foreach ($businessTypes as $bt) {
            CABusinessType::firstOrCreate(['slug' => $bt['slug']], $bt);
        }

    }
}

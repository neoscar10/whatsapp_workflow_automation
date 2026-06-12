<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$businessDetails = [
    'Hindu Undivided Family (HUF)' => [ 
        'icon' => 'family_restroom', 
        'services' => ['PAN/TAN Registration', 'Tax Planning', 'ITR Filing'], 
        'docs' => ['HUF Deed', 'Karta ID', 'Bank Statement'], 
        'time' => '3-5 Business Days',
        'description' => 'A separate tax entity formed automatically by members of a Hindu family, managed by the Karta.',
        'advantages' => ['Separate tax exemption limit', 'Can pool family assets'],
        'ideal_for' => 'Traditional families with joint businesses or ancestral property.'
    ],
    'Limited Liability Partnership (LLP)' => [ 
        'icon' => 'account_balance_wallet', 
        'services' => ['LLP Incorporation', 'RoC Compliance', 'Agreements'], 
        'docs' => ['LLP Agreement', 'DPIN Proofs', 'Office Address'], 
        'time' => '10-14 Business Days',
        'description' => 'An alternative corporate business form that gives the benefits of limited liability of a company and the flexibility of a partnership.',
        'advantages' => ['Limited liability for partners', 'Lower compliance burden than a company', 'No minimum capital requirement'],
        'ideal_for' => 'Professional services firms and small-to-medium enterprises.'
    ],
    'One Person Company (OPC)' => [ 
        'icon' => 'person', 
        'services' => ['OPC Registration', 'Director DIN', 'GST Registration'], 
        'docs' => ['ID Proof', 'Address Proof', 'Nominee Details'], 
        'time' => '7-10 Business Days',
        'description' => 'A company structure tailored for solo entrepreneurs, providing limited liability protection while allowing a single individual to act as both director and shareholder.',
        'advantages' => ['Limited liability protection', 'Separate legal entity status', 'Easier to raise funds than a proprietorship'],
        'ideal_for' => 'Solo founders looking to formalize their business structure.'
    ],
    'Partnership Firm' => [ 
        'icon' => 'groups', 
        'services' => ['Partnership Deed', 'Firm Registration', 'Tax Compliance'], 
        'docs' => ['Deed Copy', 'Partners ID', 'Rent Agreement'], 
        'time' => '8-12 Business Days',
        'description' => 'A business structure where two or more individuals manage and operate a business in accordance with the terms and objectives set out in a Partnership Deed.',
        'advantages' => ['Easy to form', 'Shared responsibility', 'Minimal compliance requirements'],
        'ideal_for' => 'Small businesses and home-based businesses with multiple co-owners.'
    ],
    'Private Limited Company' => [ 
        'icon' => 'business', 
        'services' => ['RoC Filing', 'Corporate Governance', 'Board Resolutions'], 
        'docs' => ['AoA / MoA', 'Director DIN', 'PAN/TAN'], 
        'time' => '7-10 Business Days',
        'description' => 'The most popular corporate entity in India, offering limited liability, separate legal entity status, and the ability to raise equity funding from investors.',
        'advantages' => ['Limited liability for shareholders', 'High credibility with investors and banks', 'Perpetual existence'],
        'ideal_for' => 'Startups and growing businesses seeking external investments.'
    ],
    'Proprietorship' => [ 
        'icon' => 'store', 
        'services' => ['GST Registration', 'Trade License', 'MSME Registration'], 
        'docs' => ['Address Proof', 'Shop Photo', 'PAN Card'], 
        'time' => '5-7 Business Days',
        'description' => 'An unincorporated business with a single owner who pays personal income tax on profits earned from the business. The easiest to set up but offers no liability protection.',
        'advantages' => ['Complete control over operations', 'No separate corporate taxes', 'Minimal compliance burden'],
        'ideal_for' => 'Freelancers, consultants, and very small retail traders.'
    ],
    'Public Limited Company' => [ 
        'icon' => 'location_city', 
        'services' => ['IPO Advisory', 'SEBI Compliance', 'Secretarial Audit'], 
        'docs' => ['AoA / MoA', 'Directors KYC', 'Prospectus'], 
        'time' => '15-30 Business Days',
        'description' => 'A corporate structure whose ownership is distributed amongst general public shareholders via freely traded shares.',
        'advantages' => ['Ability to raise capital from the public', 'High public transparency', 'Increased brand visibility'],
        'ideal_for' => 'Large-scale enterprises planning for rapid expansion or an IPO.'
    ],
    'Section 8 Company' => [ 
        'icon' => 'volunteer_activism', 
        'services' => ['Section 8 Registration', '80G & 12A Exemption', 'Annual Compliance'], 
        'docs' => ['Objective Proofs', 'Founders KYC', 'Utility Bill'], 
        'time' => '20-25 Business Days',
        'description' => 'A Non-Governmental Organization (NGO) registered as a company for promoting commerce, art, science, sports, education, research, social welfare, or charity.',
        'advantages' => ['Tax exemptions for donors (80G)', 'No minimum capital requirement', 'Corporate structure credibility'],
        'ideal_for' => 'Non-profit organizations, charities, and social welfare groups.'
    ],
    'Trust / Society / NGO' => [ 
        'icon' => 'volunteer_activism', 
        'services' => ['Trust Registration', 'FCRA Registration', 'Donation Planning'], 
        'docs' => ['Trust Deed', 'Bylaws', 'Donor Proofs'], 
        'time' => '15-20 Business Days',
        'description' => 'A traditional non-profit setup created through a Trust Deed or Society Registration Act for charitable purposes.',
        'advantages' => ['Easy registration process', 'Flexibility in operations', 'Family members can be trustees'],
        'ideal_for' => 'Educational institutions, hospitals, and traditional charitable foundations.'
    ],
];

foreach ($businessDetails as $name => $data) {
    $type = \Modules\CA\Models\CABusinessType::where('name', $name)->first();
    if ($type) {
        $type->update([
            'icon' => $data['icon'],
            'short_description' => substr($data['description'], 0, 100) . '...',
            'long_description' => $data['description'],
            'estimated_setup_time' => $data['time'],
            'metadata_json' => [
                'services' => $data['services'],
                'docs' => $data['docs'],
                'advantages' => $data['advantages'],
                'ideal_for' => $data['ideal_for'],
            ]
        ]);
        echo "Updated {$name}\n";
    }
}
echo "Done.\n";

<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$data = [
    'Proprietorship' => [
        'icon' => 'person',
        'estimated_setup_time' => '1-3 Days',
        'long_description' => 'A sole proprietorship is the simplest and most common structure chosen to start a business. It is an unincorporated business owned and run by one individual with no distinction between the business and you, the owner.',
        'metadata_json' => [
            'advantages' => ['Complete control over decision making', 'Minimal compliance requirements', 'No corporate tax (taxed at personal income rates)'],
            'services' => ['GST Registration', 'MSME / Udyam Registration', 'Shop & Establishment License'],
            'docs' => ['PAN Card', 'Aadhaar Card', 'Bank Account Statement', 'Utility Bill for Address Proof'],
            'ideal_for' => 'Freelancers, independent contractors, and small retail shop owners starting out.'
        ]
    ],
    'Partnership Firm' => [
        'icon' => 'group',
        'estimated_setup_time' => '3-5 Days',
        'long_description' => 'A partnership firm is a business entity where two or more persons manage and operate a business in accordance with the terms and objectives set out in a Partnership Deed.',
        'metadata_json' => [
            'advantages' => ['Shared responsibility', 'Easy to establish', 'More capital contribution compared to a proprietorship'],
            'services' => ['Partnership Deed Drafting', 'Firm Registration (Optional)', 'GST Registration'],
            'docs' => ['Partnership Deed', 'PAN & Aadhaar of all partners', 'Address Proof of Business'],
            'ideal_for' => 'Small to medium-sized businesses with multiple founders who prefer a simple management structure.'
        ]
    ],
    'Limited Liability Partnership (LLP)' => [
        'icon' => 'handshake',
        'estimated_setup_time' => '10-15 Days',
        'long_description' => 'An LLP is an alternative corporate business form that gives the benefits of limited liability of a company and the flexibility of a partnership.',
        'metadata_json' => [
            'advantages' => ['Limited liability for partners', 'No minimum capital requirement', 'Lower registration cost'],
            'services' => ['LLP Incorporation', 'LLP Agreement Drafting', 'DIR-3 KYC', 'Annual Return Filing (Form 8 & 11)'],
            'docs' => ['PAN & Aadhaar of Partners', 'Utility Bill', 'NOC from Landlord', 'Digital Signature Certificate (DSC)'],
            'ideal_for' => 'Professionals like CAs, Lawyers, and consulting firms, as well as medium-sized businesses looking for limited liability without the full compliance burden of a Private Limited Company.'
        ]
    ],
    'Private Limited Company' => [
        'icon' => 'business',
        'estimated_setup_time' => '10-15 Days',
        'long_description' => 'A Private Limited Company is the most popular corporate entity among small, medium and large businesses in India due to advantages like limited liability and separate legal entity status.',
        'metadata_json' => [
            'advantages' => ['Limited liability protection', 'Separate legal entity', 'Easier to raise funding from investors', 'High credibility'],
            'services' => ['Company Incorporation (SPICe+)', 'Director Identification Number (DIN)', 'Statutory Audit', 'Annual RoC Filings (AOC-4, MGT-7)'],
            'docs' => ['PAN, Aadhaar, Photo of Directors', 'Utility Bill (Registered Office)', 'NOC from owner', 'Digital Signature Certificate (DSC)'],
            'ideal_for' => 'Startups, growing businesses, and enterprises looking to raise venture capital and scale.'
        ]
    ],
    'Public Limited Company' => [
        'icon' => 'domain',
        'estimated_setup_time' => '20-30 Days',
        'long_description' => 'A Public Limited Company is a voluntary association of members which is incorporated under company law. It has a separate legal existence and the liability of its members is limited to shares they hold.',
        'metadata_json' => [
            'advantages' => ['Ability to raise capital from the public', 'Better borrowing capacity', 'Transferability of shares', 'Perpetual succession'],
            'services' => ['Public Company Incorporation', 'SEBI Compliance', 'Statutory Audit', 'Complex RoC Filings'],
            'docs' => ['PAN, Aadhaar, Photo of 3+ Directors', 'Utility Bill', 'NOC', 'DSCs'],
            'ideal_for' => 'Large scale businesses planning to issue shares to the public or list on stock exchanges.'
        ]
    ],
    'One Person Company (OPC)' => [
        'icon' => 'person_check',
        'estimated_setup_time' => '10-15 Days',
        'long_description' => 'An OPC allows a single entrepreneur to operate a corporate entity with limited liability protection, combining the benefits of a proprietorship and a company.',
        'metadata_json' => [
            'advantages' => ['Limited liability', 'Complete control (one member)', 'Separate legal entity'],
            'services' => ['OPC Incorporation', 'Nominee Appointment', 'Statutory Audit', 'Annual RoC Filings'],
            'docs' => ['PAN, Aadhaar of Member & Nominee', 'Utility Bill', 'NOC', 'DSC'],
            'ideal_for' => 'Solo entrepreneurs who want the limited liability and credibility of a corporate structure.'
        ]
    ],
    'Trust / Society / NGO' => [
        'icon' => 'volunteer_activism',
        'estimated_setup_time' => '15-30 Days',
        'long_description' => 'Non-Governmental Organizations (NGOs) are non-profit entities established for charitable, religious, educational, or social welfare purposes.',
        'metadata_json' => [
            'advantages' => ['Tax exemptions under 12A/80G', 'Ability to receive grants', 'Limited liability (if Section 8)'],
            'services' => ['Trust Deed / Society Registration', '12A & 80G Registration', 'FCRA Registration', 'Annual Returns'],
            'docs' => ['PAN & Aadhaar of Trustees/Members', 'Trust Deed / MoA', 'Address Proof'],
            'ideal_for' => 'Charitable organizations, educational institutions, and social welfare groups.'
        ]
    ],
    'Section 8 Company' => [
        'icon' => 'account_balance',
        'estimated_setup_time' => '20-30 Days',
        'long_description' => 'A Section 8 Company is registered for promoting commerce, art, science, sports, education, research, social welfare, religion, charity, protection of environment or any such other object.',
        'metadata_json' => [
            'advantages' => ['No minimum paid-up capital', 'Tax exemptions (12A/80G)', 'High credibility as a corporate structure', 'Exemption from stamp duty for registration'],
            'services' => ['Section 8 License & Incorporation', '12A & 80G Registration', 'Statutory Audit', 'Annual RoC Filings'],
            'docs' => ['PAN, Aadhaar of Directors', 'Utility Bill', 'NOC', 'DSC', 'Projected Income & Expenditure for 3 years'],
            'ideal_for' => 'Non-profit organizations that want a highly credible corporate structure with better governance.'
        ]
    ],
    'Hindu Undivided Family (HUF)' => [
        'icon' => 'family_restroom',
        'estimated_setup_time' => '3-5 Days',
        'long_description' => 'An HUF is a separate entity for taxation purposes. It consists of all persons lineally descended from a common ancestor, including their wives and unmarried daughters.',
        'metadata_json' => [
            'advantages' => ['Separate basic tax exemption limit', 'Ability to split family income', 'Tax saving benefits'],
            'services' => ['HUF PAN Application', 'HUF Deed Drafting', 'Income Tax Return Filing'],
            'docs' => ['HUF Deed', 'PAN of Karta', 'Address Proof', 'Bank Account in the name of HUF'],
            'ideal_for' => 'Hindu families with ancestral property or joint family businesses looking for tax benefits.'
        ]
    ]
];

foreach ($data as $name => $info) {
    $type = \Modules\CA\Models\CABusinessType::where("name", $name)->first();
    if ($type) {
        $type->icon = $info["icon"];
        $type->estimated_setup_time = $info["estimated_setup_time"];
        $type->long_description = $info["long_description"];
        $type->metadata_json = $info["metadata_json"];
        $type->save();
        echo "Updated {$name}\n";
    }
}

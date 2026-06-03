<?php

namespace Database\Seeders;

use App\Models\VerificationTemplate;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class VerificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Default India WABA Verification Template
        $template = VerificationTemplate::create([
            'name' => 'India WhatsApp Business Verification Template',
            'country_code' => 'IN',
            'description' => 'Default templates designed around documents commonly used to establish business legitimacy for WhatsApp Business Platform onboarding and Meta business verification processes in India.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Default Document Types
        $documents = [
            [
                'name' => 'Certificate of Incorporation',
                'description' => 'Proof of legal entity establishment (Private Limited, LLP, OPC, Partnership, etc.)',
                'placeholder' => 'Upload COI document (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 10,
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'GST Registration Certificate',
                'description' => 'Goods and Services Tax registration certificate (Form GST REG-06)',
                'placeholder' => 'Upload GST REG-06 document (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 10,
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'PAN Card (Business Entity)',
                'description' => 'Permanent Account Number card issued to the business entity',
                'placeholder' => 'Upload PAN Card copy (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 5,
                'is_required' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Business Address Proof',
                'description' => 'Official address proof. Accepted: Utility Bill, Internet Bill, Office Rent Agreement, Property Tax Receipt',
                'placeholder' => 'Upload address proof document (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 10,
                'is_required' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Authorized Signatory Government ID',
                'description' => 'Identification card of the authorized signatory. Accepted: Aadhaar Card, Passport, Driving License, Voter ID',
                'placeholder' => 'Upload government ID (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 5,
                'is_required' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Company Bank Account Proof',
                'description' => 'Proof of active bank account. Accepted: Cancelled Cheque, Bank Statement, Bank Account Confirmation Letter',
                'placeholder' => 'Upload bank account proof (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 10,
                'is_required' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Business Website or Digital Presence Verification',
                'description' => 'Proof of website or active digital presence. Accepted: Website Ownership Proof, Business Domain Ownership, Official Business Profile',
                'placeholder' => 'Upload website or domain proof (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 5,
                'is_required' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'WhatsApp Display Name Supporting Evidence',
                'description' => 'Evidence supporting the selected display name. Accepted: Website Screenshot, Trademark Certificate, Branding Materials, Business Registration Showing Trading Name',
                'placeholder' => 'Upload supporting evidence (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 10,
                'is_required' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Optional Trademark Certificate',
                'description' => 'Optional trademark certificate (if any) to support brand name verification',
                'placeholder' => 'Upload Trademark Certificate (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 10,
                'is_required' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Optional Additional Compliance Document',
                'description' => 'Any other optional additional compliance or industry-specific licensing document',
                'placeholder' => 'Upload additional compliance file (PDF, JPG, PNG)',
                'accepted_formats' => 'pdf,jpg,png,jpeg',
                'max_size_mb' => 15,
                'is_required' => false,
                'sort_order' => 10,
            ],
        ];

        foreach ($documents as $doc) {
            DocumentType::create(array_merge($doc, [
                'verification_template_id' => $template->id,
                'is_active' => true,
            ]));
        }
    }
}

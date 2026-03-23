<?php

namespace Database\Seeders;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $user = User::where('company_id', $company->id)->first();

        if (!$company || !$user) {
            $this->command->warn('No company or user found to attach chat data.');
            return;
        }

        // Conversation 1: Active, Unassigned
        $c1 = Conversation::create([
            'company_id' => $company->id,
            'contact_name' => 'Alice Smith',
            'contact_phone' => '+15551234567',
            'contact_location' => 'New York, US',
            'status' => 'open',
            'assignment_status' => 'unassigned',
            'unread_count' => 2,
            'labels' => [['name' => 'VIP', 'class' => 'bg-purple-100 text-purple-700']],
            'last_message_at' => now()->subMinutes(5),
            'last_message_preview' => 'I would like to know more about pricing.',
        ]);

        ConversationMessage::create([
            'conversation_id' => $c1->id,
            'direction' => 'inbound',
            'message_type' => 'text',
            'body' => 'Hello there, I am interested in your services.',
            'sent_at' => now()->subMinutes(10),
            'status' => 'delivered',
        ]);
        
        ConversationMessage::create([
            'conversation_id' => $c1->id,
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Hi Alice! We would love to help. What specific services are you looking at?',
            'sent_at' => now()->subMinutes(8),
            'status' => 'read',
            'sent_by_user_id' => $user->id,
        ]);

        ConversationMessage::create([
            'conversation_id' => $c1->id,
            'direction' => 'inbound',
            'message_type' => 'text',
            'body' => 'I would like to know more about pricing.',
            'sent_at' => now()->subMinutes(5),
            'status' => 'delivered',
        ]);

        // Conversation 2: Assigned, Read
        $c2 = Conversation::create([
            'company_id' => $company->id,
            'contact_name' => 'Bob Johnson',
            'contact_phone' => '+15559876543',
            'status' => 'open',
            'assignment_status' => 'assigned',
            'assigned_user_id' => $user->id,
            'unread_count' => 0,
            'last_message_at' => now()->subHours(2),
            'last_message_preview' => 'Thanks, that clears it up!',
        ]);

        ConversationMessage::create([
            'conversation_id' => $c2->id,
            'direction' => 'inbound',
            'message_type' => 'text',
            'body' => 'Thanks, that clears it up!',
            'sent_at' => now()->subHours(2),
            'status' => 'delivered',
        ]);

        // Conversation 3: Closed
        $c3 = Conversation::create([
            'company_id' => $company->id,
            'contact_name' => 'Charlie Davis',
            'contact_phone' => '+15550001111',
            'status' => 'closed',
            'assignment_status' => 'unassigned',
            'unread_count' => 0,
            'last_message_at' => now()->subDays(1),
            'last_message_preview' => 'Resolved the issue.',
        ]);

        ConversationMessage::create([
            'conversation_id' => $c3->id,
            'direction' => 'system',
            'message_type' => 'system',
            'body' => 'Conversation closed by agent.',
            'sent_at' => now()->subDays(1),
            'status' => 'delivered',
        ]);
    }
}

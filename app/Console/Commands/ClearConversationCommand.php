<?php

namespace App\Console\Commands;

use App\Models\Chat\Conversation;
use App\Models\Campaign\CampaignRecipient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearConversationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:clear-conversation {id : The ID of the conversation to clear} {--force : Force deletion without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear and delete a user conversation and all associated messages/notes from the platform.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $conversationId = $this->argument('id');

        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            $this->error("Conversation #{$conversationId} not found on the platform.");
            return Command::FAILURE;
        }

        $contactName = $conversation->contact_name ?: 'N/A';
        $contactPhone = $conversation->contact_phone ?: 'N/A';
        $companyId = $conversation->company_id;

        if (!$this->option('force')) {
            $confirm = $this->confirm(
                "Are you sure you want to delete Conversation #{$conversationId} ({$contactName} - {$contactPhone}) and all its messages?",
                false
            );

            if (!$confirm) {
                $this->info("Operation cancelled.");
                return Command::SUCCESS;
            }
        }

        try {
            DB::transaction(function () use ($conversation, &$messageCount, &$noteCount) {
                $messageCount = $conversation->messages()->count();
                $noteCount = $conversation->notes()->count();

                // 1. Unlink any campaign recipients referencing this conversation
                CampaignRecipient::where('conversation_id', $conversation->id)
                    ->update([
                        'conversation_id' => null,
                        'conversation_message_id' => null,
                    ]);

                // 2. Delete messages & notes
                $conversation->messages()->delete();
                $conversation->notes()->delete();

                // 3. Delete conversation record
                $conversation->delete();
            });

            $this->info("Successfully cleared Conversation #{$conversationId} from the platform.");
            $this->line(" - Company ID: {$companyId}");
            $this->line(" - Contact Name: {$contactName}");
            $this->line(" - Contact Phone: {$contactPhone}");
            $this->line(" - Messages Deleted: {$messageCount}");
            $this->line(" - Notes Deleted: {$noteCount}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clear conversation: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

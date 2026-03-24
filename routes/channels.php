<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('company.{companyId}.chats', function ($user, $companyId) {
    return (int) $user->company_id === (int) $companyId;
});

Broadcast::channel('company.{companyId}.conversation.{conversationId}', function ($user, $companyId, $conversationId) {
    if ((int) $user->company_id !== (int) $companyId) {
        return false;
    }

    return \App\Models\Chat\Conversation::where('id', $conversationId)
        ->where('company_id', $companyId)
        ->exists();
});

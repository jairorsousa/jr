<?php

use Illuminate\Support\Facades\Broadcast;

// WhatsApp channels are public (no user auth needed for webhook-driven events)
// The instance ID acts as the channel scope
Broadcast::channel('whatsapp.instance.{instanceId}', function () {
    return true;
});

Broadcast::channel('whatsapp.chat.{conversationId}', function () {
    return true;
});

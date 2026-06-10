<?php

namespace Modules\CA\Events;

use Illuminate\Queue\SerializesModels;
use Modules\CA\Models\CAClient;

class ClientUpdated
{
    use SerializesModels;

    public $client;

    public function __construct(CAClient $client)
    {
        $this->client = $client;
    }
}

<?php

namespace Modules\CA\Events;

use Illuminate\Queue\SerializesModels;
use Modules\CA\Models\CAClient;

class CompliancesAssigned
{
    use SerializesModels;

    public $client;
    public $complianceIds;

    public function __construct(CAClient $client, array $complianceIds)
    {
        $this->client = $client;
        $this->complianceIds = $complianceIds;
    }
}

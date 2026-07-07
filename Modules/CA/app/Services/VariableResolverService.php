<?php

namespace Modules\CA\Services;

use App\Models\Company;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientComplianceRequirement;
use Illuminate\Support\Carbon;

class VariableResolverService
{
    /**
     * Resolve all compliance variables for a specific requirement reminder.
     *
     * @return array<string, string>
     */
    public function resolveVariables(
        CAClientComplianceRequirement $requirement,
        CAClient $client,
        Company $firm
    ): array {
        $dueDate = $requirement->next_due_date 
            ? Carbon::parse($requirement->next_due_date) 
            : Carbon::now()->addDays(7);

        $daysRemaining = max(0, Carbon::now()->diffInDays($dueDate, false));

        // Generate a secure upload URL for the client.
        // For fallback, use the workspace domain with tenant context.
        $uploadLink = config('app.url') . "/client-portal/{$client->id}/upload";

        return [
            'client_name'      => $client->client_name,
            'firm_name'        => $firm->name,
            'document_name'    => $requirement->name,
            'document_names'   => $requirement->name,
            'compliance_name'  => $requirement->clientCompliance?->compliance?->name ?? 'Compliance Requirement',
            'due_date'         => $dueDate->format('d M Y'),
            'days_remaining'   => (string) $daysRemaining,
            'upload_link'      => $uploadLink,
            'company_name'     => $client->client_name,
            'ca_name'          => $firm->name,
            'business_type'    => $client->businessType?->name ?? 'Business Entity',
        ];
    }

    /**
     * Resolve variable array into Meta template components format.
     * E.g.:
     * [
     *   [
     *     'type' => 'body',
     *     'parameters' => [
     *       ['type' => 'text', 'text' => 'Client Name'],
     *       ['type' => 'text', 'text' => 'CA Firm'],
     *       ...
     *     ]
     *   ]
     * ]
     */
    public function resolveMetaComponents(array $resolvedVariables, int $parameterCount = 6): array
    {
        // Meta parameters must match the order:
        // 1. client_name
        // 2. firm_name
        // 3. document_name
        // 4. due_date
        // 5. days_remaining
        // 6. upload_link
        $order = [
            $resolvedVariables['client_name'] ?? '',
            $resolvedVariables['firm_name'] ?? '',
            $resolvedVariables['document_name'] ?? '',
            $resolvedVariables['due_date'] ?? '',
            $resolvedVariables['days_remaining'] ?? '0',
            $resolvedVariables['upload_link'] ?? '',
        ];

        $parameters = [];
        for ($i = 0; $i < min($parameterCount, count($order)); $i++) {
            $parameters[] = [
                'type' => 'text',
                'text' => $order[$i],
            ];
        }

        return [
            [
                'type' => 'body',
                'parameters' => $parameters,
            ]
        ];
    }

    /**
     * Resolve variable mappings customized for an automation library template.
     */
    public function resolveMetaComponentsForAutomation(
        \Modules\CA\Models\CAClientAutomation $automation,
        CAClientComplianceRequirement $requirement,
        CAClient $client,
        Company $firm
    ): array {
        $resolvedVars = $this->resolveVariables($requirement, $client, $firm);
        $mappings = $automation->metadata_json['template_variable_mappings'] ?? null;

        if (!$mappings) {
            return $this->resolveMetaComponents($resolvedVars, $automation->whatsappTemplate->parameter_count ?? 6);
        }

        $components = [];

        // Resolve Header variables
        if (!empty($mappings['header'])) {
            $headerParams = [];
            $headerVars = $mappings['header'];
            ksort($headerVars);
            foreach ($headerVars as $varName => $cfg) {
                $val = '';
                $source = $cfg['source'] ?? 'system';
                if ($source === 'system') {
                    $sysKey = $cfg['value'] ?? '';
                    $val = $resolvedVars[$sysKey] ?? '';
                } else {
                    $val = $cfg['value'] ?? '';
                }
                $headerParams[] = [
                    'type' => 'text',
                    'text' => (string)$val,
                ];
            }
            if (!empty($headerParams)) {
                $components[] = [
                    'type' => 'header',
                    'parameters' => $headerParams,
                ];
            }
        }

        // Resolve Body variables
        if (!empty($mappings['body'])) {
            $bodyParams = [];
            $bodyVars = $mappings['body'];
            ksort($bodyVars);
            foreach ($bodyVars as $varName => $cfg) {
                $val = '';
                $source = $cfg['source'] ?? 'system';
                if ($source === 'system') {
                    $sysKey = $cfg['value'] ?? '';
                    $val = $resolvedVars[$sysKey] ?? '';
                } else {
                    $val = $cfg['value'] ?? '';
                }
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => (string)$val,
                ];
            }
            if (!empty($bodyParams)) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ];
            }
        }

        return $components;
    }
}

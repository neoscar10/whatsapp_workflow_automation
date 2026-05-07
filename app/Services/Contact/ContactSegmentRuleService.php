<?php

namespace App\Services\Contact;

use App\Models\Contact\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactSegmentRuleService
{
    /**
     * Get available fields for rule builder.
     */
    public function availableFields(): array
    {
        return [
            'name' => ['label' => 'Name', 'type' => 'text'],
            'phone' => ['label' => 'Phone', 'type' => 'text'],
            'source' => ['label' => 'Source', 'type' => 'select', 'options' => $this->getSources()],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => $this->getStatuses()],
            'has_opted_in' => ['label' => 'Has Opted In', 'type' => 'boolean'],
            'do_not_message' => ['label' => 'Do Not Message', 'type' => 'boolean'],
            'created_at' => ['label' => 'Created Date', 'type' => 'date'],
            'last_interaction_at' => ['label' => 'Last Interaction', 'type' => 'date'],
            'tag_ids' => ['label' => 'Tags', 'type' => 'multiselect_tags'],
            'group_ids' => ['label' => 'Static Groups', 'type' => 'multiselect_groups'],
        ];
    }

    protected function getSources(): array
    {
        return [
            ['value' => 'manual', 'label' => 'Manual'],
            ['value' => 'import', 'label' => 'Import'],
            ['value' => 'inbound_chat', 'label' => 'Inbound Chat'],
            ['value' => 'api', 'label' => 'API'],
        ];
    }

    protected function getStatuses(): array
    {
        return [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
            ['value' => 'blocked', 'label' => 'Blocked'],
            ['value' => 'archived', 'label' => 'Archived'],
        ];
    }

    /**
     * Get available operators for a field type.
     */
    public function availableOperatorsForType(string $type): array
    {
        return match ($type) {
            'text' => [
                ['value' => 'equals', 'label' => 'is'],
                ['value' => 'not_equals', 'label' => 'is not'],
                ['value' => 'contains', 'label' => 'contains'],
                ['value' => 'starts_with', 'label' => 'starts with'],
                ['value' => 'ends_with', 'label' => 'ends with'],
                ['value' => 'is_empty', 'label' => 'is empty'],
                ['value' => 'is_not_empty', 'label' => 'is not empty'],
            ],
            'select', 'boolean' => [
                ['value' => 'equals', 'label' => 'is'],
                ['value' => 'not_equals', 'label' => 'is not'],
            ],
            'date' => [
                ['value' => 'before', 'label' => 'before'],
                ['value' => 'after', 'label' => 'after'],
                ['value' => 'within_last_days', 'label' => 'within last X days'],
                ['value' => 'older_than_days', 'label' => 'older than X days'],
                ['value' => 'is_empty', 'label' => 'is empty'],
                ['value' => 'is_not_empty', 'label' => 'is not empty'],
            ],
            'multiselect_tags' => [
                ['value' => 'has_any', 'label' => 'has any of'],
                ['value' => 'has_all', 'label' => 'has all of'],
                ['value' => 'has_none', 'label' => 'has none of'],
            ],
            'multiselect_groups' => [
                ['value' => 'in_any', 'label' => 'in any of'],
                ['value' => 'not_in_any', 'label' => 'not in any of'],
            ],
            default => [],
        };
    }

    /**
     * Apply rules to a contact query.
     */
    public function applyRulesToQuery(Builder $query, int $companyId, array $rules): Builder
    {
        $match = $rules['match'] ?? 'all';
        $conditions = $rules['conditions'] ?? [];

        if (empty($conditions)) {
            return $query->whereRaw('1=0'); // Match nothing if no conditions
        }

        $query->where('company_id', $companyId);

        $query->where(function ($q) use ($match, $conditions) {
            foreach ($conditions as $condition) {
                $method = $match === 'all' ? 'where' : 'orWhere';
                $this->applyCondition($q, $condition, $method);
            }
        });

        return $query;
    }

    protected function applyCondition(Builder $query, array $condition, string $method): void
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;

        if (!$field || !$operator) return;

        // Relation based fields
        if ($field === 'tag_ids') {
            $query->{$method . 'Has'}('tags', function ($q) use ($operator, $value) {
                if ($operator === 'has_none') {
                    $q->whereIn('contact_tags.id', (array)$value);
                } else {
                    $q->whereIn('contact_tags.id', (array)$value);
                }
            }, $operator === 'has_none' ? '<' : '>=', 1);
            return;
        }

        if ($field === 'group_ids') {
            $query->{$method . 'Has'}('groups', function ($q) use ($value) {
                $q->whereIn('contact_groups.id', (array)$value);
            }, $operator === 'not_in_any' ? '<' : '>=', 1);
            return;
        }

        // Column based fields
        switch ($operator) {
            case 'equals':
                $query->{$method}($field, $value);
                break;
            case 'not_equals':
                $query->{$method}($field, '!=', $value);
                break;
            case 'contains':
                $query->{$method}($field, 'like', '%' . $value . '%');
                break;
            case 'starts_with':
                $query->{$method}($field, 'like', $value . '%');
                break;
            case 'ends_with':
                $query->{$method}($field, 'like', '%' . $value);
                break;
            case 'is_empty':
                $query->{$method . 'Null'}($field);
                break;
            case 'is_not_empty':
                $query->{$method . 'NotNull'}($field);
                break;
            case 'before':
                $query->{$method}($field, '<', $value);
                break;
            case 'after':
                $query->{$method}($field, '>', $value);
                break;
            case 'within_last_days':
                $query->{$method}($field, '>=', Carbon::now()->subDays((int)$value));
                break;
            case 'older_than_days':
                $query->{$method}($field, '<', Carbon::now()->subDays((int)$value));
                break;
        }
    }

    /**
     * Preview matched contacts.
     */
    public function preview(User $actor, array $rules, int $limit = 25): array
    {
        $query = Contact::query();
        $this->applyRulesToQuery($query, $actor->company_id, $rules);

        return [
            'total_count' => (clone $query)->count(),
            'sample' => $query->with(['tags', 'groups'])->limit($limit)->get(),
        ];
    }

    /**
     * Count matched contacts.
     */
    public function count(User $actor, array $rules): int
    {
        $query = Contact::query();
        $this->applyRulesToQuery($query, $actor->company_id, $rules);
        return $query->count();
    }
}

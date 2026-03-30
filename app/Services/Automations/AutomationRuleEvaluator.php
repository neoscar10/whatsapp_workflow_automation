<?php

namespace App\Services\Automations;

class AutomationRuleEvaluator
{
    /**
     * Evaluate a set of rules against a given context.
     */
    public function evaluate(array $rules, array $context, string $mode = 'all'): bool
    {
        if (empty($rules)) {
            return true;
        }

        $results = [];
        foreach ($rules as $rule) {
            $field = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? 'equals';
            $value = $rule['value'] ?? '';

            $actualValue = $this->getValueFromContext($field, $context);
            $results[] = $this->compareValues($actualValue, $operator, $value);
        }

        if ($mode === 'all' || $mode === 'and') {
            return !in_array(false, $results, true);
        }

        return in_array(true, $results, true);
    }

    /**
     * Evaluate multiple rule groups.
     */
    public function evaluateGroups(array $groups, array $context): bool
    {
        if (empty($groups)) {
            return true;
        }

        foreach ($groups as $group) {
            $mode = $group['match_mode'] ?? $group['joiner'] ?? 'all';
            $rules = $group['rules'] ?? [];
            if (!$this->evaluate($rules, $context, $mode)) {
                return false; // Groups are typically joined by AND
            }
        }

        return true;
    }

    /**
     * Extract value from nested context array using dot notation.
     */
    public function getValueFromContext(string $field, array $context)
    {
        $parts = explode('.', $field);
        $current = $context;
        
        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } else {
                return null;
            }
        }
        
        return $current;
    }

    /**
     * Compare two values using the specified operator.
     */
    public function compareValues($actual, string $operator, $expected): bool
    {
        return match($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'contains' => str_contains(strtolower($actual ?? ''), strtolower($expected ?? '')),
            'not_contains' => !str_contains(strtolower($actual ?? ''), strtolower($expected ?? '')),
            'starts_with' => str_starts_with(strtolower($actual ?? ''), strtolower($expected ?? '')),
            'ends_with' => str_ends_with(strtolower($actual ?? ''), strtolower($expected ?? '')),
            'greater_than' => $actual > $expected,
            'less_than' => $actual < $expected,
            'is_set', 'exists' => !is_null($actual),
            'is_empty' => empty($actual),
            'is_not_empty' => !empty($actual),
            default => false,
        };
    }
}

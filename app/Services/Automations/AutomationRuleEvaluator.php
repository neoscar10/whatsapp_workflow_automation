<?php

namespace App\Services\Automations;

class AutomationRuleEvaluator
{
    /**
     * Evaluate a set of rules against a given context.
     */
    public function evaluate(array $rules, array $context, string $mode = 'all'): bool
    {
        return $this->evaluateDetailed($rules, $context, $mode)['match'];
    }

    /**
     * Evaluate multiple rule groups.
     */
    public function evaluateGroups(array $groups, array $context): bool
    {
        return $this->evaluateGroupsDetailed($groups, $context)['match'];
    }

    /**
     * Extract value from nested context array using dot notation.
     */
    public function getValueFromContext(string $field, array $context)
    {
        if (empty($field)) return null;
        
        // Strip 'trigger.' prefix if present for consistency with action nodes
        if (str_starts_with($field, 'trigger.')) {
            $field = substr($field, 8);
        }

        $parts = explode('.', $field);
        $current = $context;
        
        foreach ($parts as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
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
            'greater_than' => (float)$actual > (float)$expected,
            'less_than' => (float)$actual < (float)$expected,
            'is_set', 'exists' => !is_null($actual),
            'is_empty' => empty($actual),
            'is_not_empty' => !empty($actual),
            default => false,
        };
    }

    /**
     * Evaluate a set of rules and return a detailed report for debugging/simulation.
     */
    public function evaluateDetailed(array $rules, array $context, string $mode = 'and'): array
    {
        if (empty($rules)) {
            return [
                'match' => true,
                'combinator' => $mode,
                'rules' => [],
                'summary' => 'No rules defined (True by default)'
            ];
        }

        $ruleResults = [];
        $matchedCount = 0;

        foreach ($rules as $index => $rule) {
            $field = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? 'equals';
            $expected = $rule['value'] ?? '';

            $actual = $this->getValueFromContext($field, $context);
            $match = $this->compareValues($actual, $operator, $expected);
            
            if ($match) $matchedCount++;

            $error = null;
            $resolved = true;
            
            // Check if path was found
            $parts = explode('.', $field);
            $temp = $context;
            foreach ($parts as $part) {
                if (is_array($temp) && array_key_exists($part, $temp)) {
                    $temp = $temp[$part];
                } else {
                    $resolved = false;
                    break;
                }
            }

            if (!$resolved && !in_array($operator, ['is_set', 'exists', 'is_empty'])) {
                $error = "Path '$field' not found in execution context";
            }

            $ruleResults[] = [
                'index' => $index,
                'field' => $field,
                'operator' => $operator,
                'expected' => $expected,
                'actual' => $actual,
                'actual_type' => gettype($actual),
                'expected_type' => gettype($expected),
                'resolved' => $resolved,
                'match' => $match,
                'error' => $error
            ];
        }

        $finalMatch = ($mode === 'all' || $mode === 'and') 
            ? $matchedCount === count($rules)
            : $matchedCount > 0;

        return [
            'match' => $finalMatch,
            'combinator' => $mode,
            'rules' => $ruleResults,
            'summary' => "$matchedCount of " . count($rules) . " rules matched"
        ];
    }

    /**
     * Evaluate multiple rule groups and return a detailed report.
     */
    public function evaluateGroupsDetailed(array $groups, array $context): array
    {
        if (empty($groups)) {
            return [
                'match' => true,
                'groups' => [],
                'summary' => 'No rule groups defined'
            ];
        }

        $groupResults = [];
        $allMatched = true;

        foreach ($groups as $index => $group) {
            $mode = $group['match_mode'] ?? $group['joiner'] ?? 'all';
            $rules = $group['rules'] ?? [];
            
            $report = $this->evaluateDetailed($rules, $context, $mode);
            $groupResults[] = array_merge(['index' => $index], $report);
            
            if (!$report['match']) {
                $allMatched = false;
            }
        }

        return [
            'match' => $allMatched,
            'groups' => $groupResults,
            'summary' => ($allMatched ? 'All groups matched' : 'One or more groups failed')
        ];
    }
}

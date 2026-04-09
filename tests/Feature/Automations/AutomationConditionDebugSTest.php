<?php

namespace Tests\Feature\Automations;

use App\Services\Automations\AutomationRuleEvaluator;
use Tests\TestCase;

class AutomationConditionDebugSTest extends TestCase
{
    protected AutomationRuleEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new AutomationRuleEvaluator();
    }

    public function test_exact_match_success()
    {
        $rules = [
            ['field' => 'payload.contact.phone', 'operator' => 'equals', 'value' => '1234567890']
        ];
        $context = [
            'payload' => [
                'contact' => [
                    'phone' => '1234567890'
                ]
            ]
        ];

        $report = $this->evaluator->evaluateDetailed($rules, $context);

        $this->assertTrue($report['match']);
        $this->assertCount(1, $report['rules']);
        $this->assertEquals('1234567890', $report['rules'][0]['actual']);
        $this->assertTrue($report['rules'][0]['match']);
        $this->assertTrue($report['rules'][0]['resolved']);
    }

    public function test_exact_match_failure()
    {
        $rules = [
            ['field' => 'payload.contact.phone', 'operator' => 'equals', 'value' => '1234567890']
        ];
        $context = [
            'payload' => [
                'contact' => [
                    'phone' => '0987654321'
                ]
            ]
        ];

        $report = $this->evaluator->evaluateDetailed($rules, $context);

        $this->assertFalse($report['match']);
        $this->assertCount(1, $report['rules']);
        $this->assertEquals('0987654321', $report['rules'][0]['actual']);
        $this->assertFalse($report['rules'][0]['match']);
        $this->assertTrue($report['rules'][0]['resolved']);
    }

    public function test_missing_path_failure()
    {
        $rules = [
            ['field' => 'payload.user.email', 'operator' => 'exists', 'value' => true]
        ];
        $context = [
            'payload' => [
                'contact' => [
                    'phone' => '1234567890'
                ]
            ]
        ];

        $report = $this->evaluator->evaluateDetailed($rules, $context);

        $this->assertFalse($report['match']);
        $this->assertFalse($report['rules'][0]['resolved']);
        $this->assertNull($report['rules'][0]['actual']);
        $this->assertEquals("Path 'payload.user.email' not found in execution context", $report['rules'][0]['error']);
    }

    public function test_and_condition_mixed()
    {
        $rules = [
            ['field' => 'payload.type', 'operator' => 'equals', 'value' => 'text'],
            ['field' => 'payload.source', 'operator' => 'equals', 'value' => 'web']
        ];
        $context = [
            'payload' => [
                'type' => 'text',
                'source' => 'api'
            ]
        ];

        $report = $this->evaluator->evaluateDetailed($rules, $context, 'and');

        $this->assertFalse($report['match']);
        $this->assertTrue($report['rules'][0]['match']);
        $this->assertFalse($report['rules'][1]['match']);
        $this->assertEquals('1 of 2 rules matched', $report['summary']);
    }

    public function test_or_condition_mixed()
    {
        $rules = [
            ['field' => 'payload.type', 'operator' => 'equals', 'value' => 'image'],
            ['field' => 'payload.source', 'operator' => 'equals', 'value' => 'web']
        ];
        $context = [
            'payload' => [
                'type' => 'text',
                'source' => 'web'
            ]
        ];

        $report = $this->evaluator->evaluateDetailed($rules, $context, 'or');

        $this->assertTrue($report['match']);
        $this->assertFalse($report['rules'][0]['match']);
        $this->assertTrue($report['rules'][1]['match']);
        $this->assertEquals('1 of 2 rules matched', $report['summary']);
    }

    public function test_group_evaluation_detailed()
    {
        $groups = [
            [
                'match_mode' => 'and',
                'rules' => [
                    ['field' => 'payload.type', 'operator' => 'equals', 'value' => 'text']
                ]
            ],
            [
                'match_mode' => 'or',
                'rules' => [
                    ['field' => 'payload.source', 'operator' => 'equals', 'value' => 'web'],
                    ['field' => 'payload.source', 'operator' => 'equals', 'value' => 'mobile']
                ]
            ]
        ];
        $context = [
            'payload' => [
                'type' => 'text',
                'source' => 'api'
            ]
        ];

        $report = $this->evaluator->evaluateGroupsDetailed($groups, $context);

        $this->assertFalse($report['match']);
        $this->assertTrue($report['groups'][0]['match']);
        $this->assertFalse($report['groups'][1]['match']);
        $this->assertEquals('One or more groups failed', $report['summary']);
    }
}

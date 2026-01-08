<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Transaction\TrasactionService;

class WorkflowSnapshotTest extends TestCase
{
    protected TrasactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrasactionService(
            new \App\Services\Transaction\WorkflowEngineService()
        );
    }

    /** @test */
    public function it_normalizes_workflow_steps()
    {
        $steps = [
            [
                'department_id' => 1,
                'department_name' => 'Dept A',
                'process_time_value' => '2',
                'process_time_unit' => 'days',
            ],
            [
                'department_id' => 2,
                'department_name' => 'Dept B',
                'process_time_value' => '3',
                'process_time_unit' => 'hours',
            ],
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeWorkflowSteps');
        $method->setAccessible(true);

        $normalized = $method->invoke($this->service, $steps);

        // Assert order is set
        $this->assertEquals(1, $normalized[0]['order']);
        $this->assertEquals(2, $normalized[1]['order']);

        // Assert process_time_value is cast to int
        $this->assertIsInt($normalized[0]['process_time_value']);
        $this->assertEquals(2, $normalized[0]['process_time_value']);
        $this->assertIsInt($normalized[1]['process_time_value']);
        $this->assertEquals(3, $normalized[1]['process_time_value']);

        // Assert process_time_unit is preserved
        $this->assertEquals('days', $normalized[0]['process_time_unit']);
        $this->assertEquals('hours', $normalized[1]['process_time_unit']);
    }

    /** @test */
    public function it_checks_first_step_changed()
    {
        $oldSteps = [
            ['department_id' => 1, 'department_name' => 'Dept A'],
            ['department_id' => 2, 'department_name' => 'Dept B'],
        ];

        $newStepsSame = [
            ['department_id' => 1, 'department_name' => 'Dept A'],
            ['department_id' => 3, 'department_name' => 'Dept C'], // Changed second step
        ];

        $newStepsDifferent = [
            ['department_id' => 3, 'department_name' => 'Dept C'], // Changed first step
            ['department_id' => 2, 'department_name' => 'Dept B'],
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('hasFirstStepChanged');
        $method->setAccessible(true);

        // First step unchanged
        $this->assertFalse($method->invoke($this->service, $oldSteps, $newStepsSame));

        // First step changed
        $this->assertTrue($method->invoke($this->service, $oldSteps, $newStepsDifferent));
    }

    /** @test */
    public function it_handles_empty_workflow_steps()
    {
        $steps = [];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeWorkflowSteps');
        $method->setAccessible(true);

        $normalized = $method->invoke($this->service, $steps);

        $this->assertIsArray($normalized);
        $this->assertEmpty($normalized);
    }

    /** @test */
    public function it_validates_json_workflow_snapshot_parsing()
    {
        $workflowConfig = [
            'steps' => [
                [
                    'department_id' => 1,
                    'department_name' => 'Department A',
                    'process_time_value' => 2,
                    'process_time_unit' => 'days',
                ],
            ],
        ];

        // Encode to JSON
        $json = json_encode($workflowConfig);
        $this->assertIsString($json);

        // Decode back
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals($workflowConfig, $decoded);
        $this->assertArrayHasKey('steps', $decoded);
        $this->assertEquals(1, count($decoded['steps']));
    }
}

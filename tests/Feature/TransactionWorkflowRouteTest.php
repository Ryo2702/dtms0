<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\Transaction;
use App\Models\AssignStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionWorkflowRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Department $department1;
    protected Department $department2;
    protected Department $department3;
    protected Workflow $workflow;
    protected AssignStaff $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create departments
        $this->department1 = Department::factory()->create(['name' => 'Department A', 'status' => true]);
        $this->department2 = Department::factory()->create(['name' => 'Department B', 'status' => true]);
        $this->department3 = Department::factory()->create(['name' => 'Department C', 'status' => true]);

        // Create user
        $this->user = User::factory()->create([
            'department_id' => $this->department1->id,
        ]);

        // Create assign staff
        $this->staff = AssignStaff::factory()->create(['status' => true]);

        // Create workflow with default config
        $this->workflow = Workflow::factory()->create([
            'transaction_name' => 'Test Workflow',
            'status' => true,
            'workflow_config' => [
                'steps' => [
                    [
                        'order' => 1,
                        'department_id' => $this->department1->id,
                        'department_name' => 'Department A',
                        'process_time_value' => 2,
                        'process_time_unit' => 'days',
                    ],
                    [
                        'order' => 2,
                        'department_id' => $this->department2->id,
                        'department_name' => 'Department B',
                        'process_time_value' => 3,
                        'process_time_unit' => 'days',
                    ],
                ],
                'transitions' => [],
            ],
        ]);
    }

    /** @test */
    public function it_creates_transaction_with_default_workflow_config()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'workflow_id' => $this->workflow->id,
            'level_of_urgency' => 'normal',
            'assign_staff_id' => $this->staff->id,
            'workflow_snapshot' => '', // Empty means use default
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'workflow_id' => $this->workflow->id,
            'total_workflow_steps' => 2,
            'created_by' => $this->user->id,
        ]);

        $transaction = Transaction::latest()->first();
        $this->assertNotNull($transaction->workflow_snapshot);
        $this->assertEquals(2, count($transaction->workflow_snapshot['steps']));
        $this->assertEquals($this->department1->id, $transaction->workflow_snapshot['steps'][0]['department_id']);
        $this->assertEquals($this->department2->id, $transaction->workflow_snapshot['steps'][1]['department_id']);
    }

    /** @test */
    public function it_creates_transaction_with_custom_workflow_route()
    {
        $this->actingAs($this->user);

        // Custom workflow config with 3 steps instead of default 2
        $customWorkflowConfig = [
            'steps' => [
                [
                    'department_id' => $this->department1->id,
                    'department_name' => 'Department A',
                    'process_time_value' => 1,
                    'process_time_unit' => 'days',
                ],
                [
                    'department_id' => $this->department3->id,
                    'department_name' => 'Department C',
                    'process_time_value' => 2,
                    'process_time_unit' => 'days',
                ],
                [
                    'department_id' => $this->department2->id,
                    'department_name' => 'Department B',
                    'process_time_value' => 3,
                    'process_time_unit' => 'days',
                ],
            ],
            'transitions' => [],
        ];

        $response = $this->post(route('transactions.store'), [
            'workflow_id' => $this->workflow->id,
            'level_of_urgency' => 'urgent',
            'assign_staff_id' => $this->staff->id,
            'workflow_snapshot' => json_encode($customWorkflowConfig),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaction = Transaction::latest()->first();
        
        // Verify custom workflow is stored
        $this->assertNotNull($transaction->workflow_snapshot);
        $this->assertEquals(3, count($transaction->workflow_snapshot['steps']));
        $this->assertEquals(3, $transaction->total_workflow_steps);
        
        // Verify step order and departments
        $this->assertEquals($this->department1->id, $transaction->workflow_snapshot['steps'][0]['department_id']);
        $this->assertEquals($this->department3->id, $transaction->workflow_snapshot['steps'][1]['department_id']);
        $this->assertEquals($this->department2->id, $transaction->workflow_snapshot['steps'][2]['department_id']);
        
        // Verify process times are saved
        $this->assertEquals(1, $transaction->workflow_snapshot['steps'][0]['process_time_value']);
        $this->assertEquals(2, $transaction->workflow_snapshot['steps'][1]['process_time_value']);
        $this->assertEquals(3, $transaction->workflow_snapshot['steps'][2]['process_time_value']);
    }

    /** @test */
    public function it_normalizes_workflow_steps_order()
    {
        $this->actingAs($this->user);

        // Custom config without explicit order
        $customWorkflowConfig = [
            'steps' => [
                [
                    'department_id' => $this->department2->id,
                    'department_name' => 'Department B',
                    'process_time_value' => 2,
                    'process_time_unit' => 'hours',
                ],
                [
                    'department_id' => $this->department1->id,
                    'department_name' => 'Department A',
                    'process_time_value' => 5,
                    'process_time_unit' => 'days',
                ],
            ],
        ];

        $response = $this->post(route('transactions.store'), [
            'workflow_id' => $this->workflow->id,
            'level_of_urgency' => 'normal',
            'assign_staff_id' => $this->staff->id,
            'workflow_snapshot' => json_encode($customWorkflowConfig),
        ]);

        $response->assertRedirect();
        
        $transaction = Transaction::latest()->first();
        
        // Verify steps have been normalized with order field
        $this->assertEquals(1, $transaction->workflow_snapshot['steps'][0]['order']);
        $this->assertEquals(2, $transaction->workflow_snapshot['steps'][1]['order']);
    }

    /** @test */
    public function it_preserves_default_workflow_config_in_database()
    {
        $this->actingAs($this->user);

        // Create transaction with custom route
        $customWorkflowConfig = [
            'steps' => [
                [
                    'department_id' => $this->department3->id,
                    'department_name' => 'Department C',
                    'process_time_value' => 1,
                    'process_time_unit' => 'days',
                ],
            ],
        ];

        $this->post(route('transactions.store'), [
            'workflow_id' => $this->workflow->id,
            'level_of_urgency' => 'normal',
            'assign_staff_id' => $this->staff->id,
            'workflow_snapshot' => json_encode($customWorkflowConfig),
        ]);

        // Verify the original workflow config is unchanged
        $workflow = Workflow::find($this->workflow->id);
        $this->assertEquals(2, count($workflow->workflow_config['steps']));
        $this->assertEquals($this->department1->id, $workflow->workflow_config['steps'][0]['department_id']);
        $this->assertEquals($this->department2->id, $workflow->workflow_config['steps'][1]['department_id']);
    }

    /** @test */
    public function it_validates_workflow_snapshot_json_format()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'workflow_id' => $this->workflow->id,
            'level_of_urgency' => 'normal',
            'assign_staff_id' => $this->staff->id,
            'workflow_snapshot' => 'invalid-json-string',
        ]);

        $response->assertSessionHasErrors('workflow_snapshot');
    }
}

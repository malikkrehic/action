<?php

namespace MK\Action\Tests\Feature;

use MK\Action\ActionRegistry;
use MK\Action\BaseAction;
use MK\Action\Tests\TestCase;
use Spatie\LaravelData\Data;

class ExampleAction extends BaseAction
{
    public static function name(): string
    {
        return 'example-action';
    }

    public static function description(): string
    {
        return 'An example action for testing';
    }

    public static function getDataType(): string
    {
        return ExampleData::class;
    }

    public function handle(Data $data): array
    {
        /** @var ExampleData $data */
        return [
            'message' => "Hello, {$data->name}!",
            'processed_at' => now()->toISOString(),
        ];
    }
}

class ExampleData extends Data
{
    public function __construct(
        #[\Spatie\LaravelData\Attributes\Validation\Required]
        public string $name
    ) {}
}

class ActionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register test action
        $registry = $this->app->make(ActionRegistry::class);
        $registry->register(ExampleAction::class);
    }

    public function test_can_list_actions(): void
    {
        $response = $this->get('/actions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'actions' => [
                    'example-action' => [
                        'name',
                        'description',
                        'data_type',
                    ]
                ],
                'count'
            ])
            ->assertJson([
                'count' => 1,
                'actions' => [
                    'example-action' => [
                        'name' => 'example-action',
                        'description' => 'An example action for testing',
                        'data_type' => ExampleData::class,
                    ]
                ]
            ]);
    }

    public function test_can_execute_action(): void
    {
        $response = $this->postJson('/actions', [
            'action' => 'example-action',
            'data' => [
                'name' => 'John Doe'
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'action',
                'result' => [
                    'message',
                    'processed_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'action' => 'example-action',
                'result' => [
                    'message' => 'Hello, John Doe!'
                ]
            ]);
    }

    public function test_returns_404_for_unknown_action(): void
    {
        $response = $this->postJson('/actions', [
            'action' => 'unknown-action',
            'data' => []
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Action not found',
                'action' => 'unknown-action'
            ])
            ->assertJsonStructure([
                'available_actions'
            ]);
    }

    public function test_returns_400_for_invalid_data(): void
    {
        $response = $this->postJson('/actions', [
            'action' => 'example-action',
            'data' => [] // Missing required 'name' field
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'error',
                'message'
            ]);
    }
}

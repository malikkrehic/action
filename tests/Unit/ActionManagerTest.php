<?php

namespace MK\Action\Tests\Unit;

use MK\Action\ActionManager;
use MK\Action\ActionRegistry;
use MK\Action\BaseAction;
use MK\Action\Tests\TestCase;
use Spatie\LaravelData\Data;
use InvalidArgumentException;

class FluentTestAction extends BaseAction
{
    public static function name(): string
    {
        return 'fluent-test';
    }

    public static function description(): string
    {
        return 'Test action for fluent API';
    }

    public static function getDataType(): string
    {
        return FluentTestData::class;
    }

    public function handle(Data $data): array
    {
        /** @var FluentTestData $data */
        return [
            'message' => "Hello, {$data->name}!",
            'age' => $data->age,
            'processed' => true,
        ];
    }
}

class FluentTestData extends Data
{
    public function __construct(
        public string $name,
        public int $age = 25
    ) {}
}

class ActionManagerTest extends TestCase
{
    private ActionManager $manager;
    private ActionRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ActionRegistry();
        $this->manager = new ActionManager($this->registry);
        
        $this->registry->register(FluentTestAction::class);
    }

    public function test_can_make_action_builder(): void
    {
        $builder = $this->manager->make('fluent-test');
        
        $this->assertInstanceOf(\MK\Action\ActionBuilder::class, $builder);
        $this->assertEquals(FluentTestData::class, $builder->getDataType());
    }

    public function test_can_execute_action_fluently(): void
    {
        $result = $this->manager->make('fluent-test')
            ->with(['name' => 'John', 'age' => 30])
            ->execute();

        $this->assertEquals([
            'message' => 'Hello, John!',
            'age' => 30,
            'processed' => true,
        ], $result);
    }

    public function test_can_execute_action_directly(): void
    {
        $result = $this->manager->execute('fluent-test', [
            'name' => 'Jane',
            'age' => 28
        ]);

        $this->assertEquals([
            'message' => 'Hello, Jane!',
            'age' => 28,
            'processed' => true,
        ], $result);
    }

    public function test_can_check_if_action_exists(): void
    {
        $this->assertTrue($this->manager->has('fluent-test'));
        $this->assertFalse($this->manager->has('non-existent'));
    }

    public function test_can_get_action_metadata(): void
    {
        $metadata = $this->manager->metadata('fluent-test');

        $this->assertEquals([
            'name' => 'fluent-test',
            'description' => 'Test action for fluent API',
            'data_type' => FluentTestData::class,
        ], $metadata);
    }

    public function test_throws_exception_for_unknown_action_in_make(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Action 'unknown' not found");

        $this->manager->make('unknown');
    }

    public function test_throws_exception_for_unknown_action_in_metadata(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Action 'unknown' not found");

        $this->manager->metadata('unknown');
    }
}

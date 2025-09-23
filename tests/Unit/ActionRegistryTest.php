<?php

namespace MK\Action\Tests\Unit;

use MK\Action\ActionRegistry;
use MK\Action\BaseAction;
use MK\Action\Tests\TestCase;
use Spatie\LaravelData\Data;
use InvalidArgumentException;

class TestAction extends BaseAction
{
    public static function name(): string
    {
        return 'test-action';
    }

    public static function getDataType(): string
    {
        return TestData::class;
    }

    public function handle(Data $data): array
    {
        return ['message' => 'Test action executed'];
    }
}

class TestData extends Data
{
    public function __construct(
        public string $message = 'test'
    ) {}
}

class ActionRegistryTest extends TestCase
{
    private ActionRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ActionRegistry();
    }

    public function test_can_register_action(): void
    {
        $this->registry->register(TestAction::class);
        
        $this->assertTrue($this->registry->has('test-action'));
        $this->assertEquals(TestAction::class, $this->registry->get('test-action'));
    }

    public function test_can_get_all_actions(): void
    {
        $this->registry->register(TestAction::class);
        
        $actions = $this->registry->all();
        
        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test-action', $actions);
    }

    public function test_can_get_action_names(): void
    {
        $this->registry->register(TestAction::class);
        
        $names = $this->registry->names();
        
        $this->assertEquals(['test-action'], $names);
    }

    public function test_throws_exception_for_unknown_action(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Action 'unknown' not found");
        
        $this->registry->get('unknown');
    }
}

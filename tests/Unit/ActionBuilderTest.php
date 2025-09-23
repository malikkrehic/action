<?php

namespace MK\Action\Tests\Unit;

use MK\Action\ActionBuilder;
use MK\Action\BaseAction;
use MK\Action\Tests\TestCase;
use Spatie\LaravelData\Data;

class BuilderTestAction extends BaseAction
{
    public static function name(): string
    {
        return 'builder-test';
    }

    public static function getDataType(): string
    {
        return BuilderTestData::class;
    }

    public function handle(Data $data): string
    {
        /** @var BuilderTestData $data */
        return "Processed: {$data->value}";
    }
}

class BuilderTestData extends Data
{
    public function __construct(
        public string $value
    ) {}
}

class ActionBuilderTest extends TestCase
{
    public function test_can_create_builder_with_action_class(): void
    {
        $builder = new ActionBuilder(BuilderTestAction::class);
        
        $this->assertEquals(BuilderTestData::class, $builder->getDataType());
    }

    public function test_can_set_data_and_execute(): void
    {
        $builder = new ActionBuilder(BuilderTestAction::class);
        
        $result = $builder
            ->with(['value' => 'test data'])
            ->execute();

        $this->assertEquals('Processed: test data', $result);
    }

    public function test_can_get_metadata(): void
    {
        $builder = new ActionBuilder(BuilderTestAction::class);
        
        $metadata = $builder->metadata();

        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('data_type', $metadata);
        $this->assertEquals('builder-test', $metadata['name']);
        $this->assertEquals(BuilderTestData::class, $metadata['data_type']);
    }

    public function test_throws_exception_when_executing_without_data(): void
    {
        $builder = new ActionBuilder(BuilderTestAction::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data must be provided before executing action');

        $builder->execute();
    }

    public function test_with_method_returns_fluent_interface(): void
    {
        $builder = new ActionBuilder(BuilderTestAction::class);
        
        $returnedBuilder = $builder->with(['value' => 'test']);

        $this->assertSame($builder, $returnedBuilder);
    }
}

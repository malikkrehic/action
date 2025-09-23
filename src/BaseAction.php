<?php

namespace MK\Action;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\LaravelData\Data;
use BadMethodCallException;

abstract class BaseAction
{
    use AuthorizesRequests;

    /**
     * Set the default validation behavior.
     */
    protected bool $withValidation = true;

    /**
     * Each action must provide its unique name.
     */
    abstract public static function name(): string;

    /**
     * Return the fully qualified name of the expected Data type.
     *
     * @return class-string<Data>
     */
    abstract public static function getDataType(): string;

    /**
     * Get action description for documentation.
     */
    public static function description(): string
    {
        return 'No description provided';
    }

    /**
     * Get action metadata.
     *
     * @return array<string, mixed>
     */
    public static function metadata(): array
    {
        return [
            'name' => static::name(),
            'description' => static::description(),
            'data_type' => static::getDataType(),
        ];
    }

    /**
     * Execute the action with the provided data.
     *
     * @param Data|array<string, mixed> $data
     * @return mixed
     */
    public function __invoke(Data|array $data): mixed
    {
        $expectedType = static::getDataType();
        
        // Convert array to Data object if needed
        if (is_array($data)) {
            $data = $expectedType::from($data);
        }
        
        // Validate if needed
        if ($this->withValidation && is_array($data->toArray())) {
            $data = $expectedType::validateAndCreate($data->toArray());
        }

        if (!method_exists($this, 'handle')) {
            throw new BadMethodCallException("Method handle not implemented for action " . static::name());
        }

        return $this->handle($data);
    }

    /**
     * Handle the action logic.
     * Must be implemented by concrete action classes.
     * 
     * Concrete implementations should type-hint the specific Data class:
     * public function handle(YourSpecificData $data): mixed
     *
     * @param Data $data
     * @return mixed
     */
    abstract public function handle(Data $data): mixed;
}

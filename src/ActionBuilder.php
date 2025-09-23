<?php

namespace MK\Action;

use Spatie\LaravelData\Data;

class ActionBuilder
{
    protected Data|array|null $data = null;

    /**
     * @param class-string<BaseAction> $actionClass
     */
    public function __construct(
        protected string $actionClass
    ) {}

    /**
     * Set the data for the action.
     *
     * @param Data|array<string, mixed> $data
     */
    public function with(Data|array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Execute the action.
     *
     * @return mixed
     */
    public function execute(): mixed
    {
        $action = new $this->actionClass();
        
        if ($this->data === null) {
            throw new \InvalidArgumentException("Data must be provided before executing action");
        }

        return $action($this->data);
    }

    /**
     * Get action metadata.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->actionClass::metadata();
    }

    /**
     * Get the expected data type for this action.
     *
     * @return class-string<Data>
     */
    public function getDataType(): string
    {
        return $this->actionClass::getDataType();
    }
}

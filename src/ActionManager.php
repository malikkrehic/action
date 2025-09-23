<?php

namespace MK\Action;

use InvalidArgumentException;
use Spatie\LaravelData\Data;

class ActionManager
{
    public function __construct(
        protected ActionRegistry $registry
    ) {}

    /**
     * Create a fluent action builder.
     */
    public function make(string $actionName): ActionBuilder
    {
        if (!$this->registry->has($actionName)) {
            throw new InvalidArgumentException("Action '{$actionName}' not found");
        }

        $actionClass = $this->registry->get($actionName);
        
        return new ActionBuilder($actionClass);
    }

    /**
     * Execute an action directly.
     *
     * @param string $actionName
     * @param array<string, mixed>|Data $data
     * @return mixed
     */
    public function execute(string $actionName, array|Data $data): mixed
    {
        return $this->make($actionName)->with($data)->execute();
    }

    /**
     * Get all available actions.
     *
     * @return array<string, class-string<BaseAction>>
     */
    public function all(): array
    {
        return $this->registry->all();
    }

    /**
     * Check if an action exists.
     */
    public function has(string $actionName): bool
    {
        return $this->registry->has($actionName);
    }

    /**
     * Get action metadata.
     *
     * @return array<string, mixed>
     */
    public function metadata(string $actionName): array
    {
        if (!$this->registry->has($actionName)) {
            throw new InvalidArgumentException("Action '{$actionName}' not found");
        }

        $actionClass = $this->registry->get($actionName);
        
        return $actionClass::metadata();
    }
}

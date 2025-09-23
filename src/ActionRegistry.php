<?php

namespace MK\Action;

use InvalidArgumentException;

class ActionRegistry
{
    /**
     * @var array<string, class-string<BaseAction>>
     */
    protected array $actions = [];

    /**
     * Register an action class.
     *
     * @param class-string<BaseAction> $actionClass
     */
    public function register(string $actionClass): void
    {
        if (!is_subclass_of($actionClass, BaseAction::class)) {
            throw new InvalidArgumentException("Class {$actionClass} must extend BaseAction");
        }

        $name = $actionClass::name();
        $this->actions[$name] = $actionClass;
    }

    /**
     * Get all registered actions.
     *
     * @return array<string, class-string<BaseAction>>
     */
    public function all(): array
    {
        return $this->actions;
    }

    /**
     * Get action class by name.
     *
     * @param string $name
     * @return class-string<BaseAction>
     */
    public function get(string $name): string
    {
        if (!isset($this->actions[$name])) {
            throw new InvalidArgumentException("Action '{$name}' not found");
        }

        return $this->actions[$name];
    }

    /**
     * Check if action exists.
     */
    public function has(string $name): bool
    {
        return isset($this->actions[$name]);
    }

    /**
     * Get action names.
     *
     * @return array<string>
     */
    public function names(): array
    {
        return array_keys($this->actions);
    }
}

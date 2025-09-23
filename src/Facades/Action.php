<?php

namespace MK\Action\Facades;

use Illuminate\Support\Facades\Facade;
use MK\Action\ActionBuilder;
use MK\Action\ActionManager;

/**
 * @method static ActionBuilder make(string $actionName)
 * @method static mixed execute(string $actionName, array|\Spatie\LaravelData\Data $data)
 * @method static array all()
 * @method static bool has(string $actionName)
 * @method static array metadata(string $actionName)
 *
 * @see ActionManager
 */
class Action extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ActionManager::class;
    }
}

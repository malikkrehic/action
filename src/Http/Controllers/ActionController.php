<?php

namespace MK\Action\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use MK\Action\ActionRegistry;
use MK\Action\Data\ActionData;
use MK\Action\Data\ActionExecuteResponseData;
use InvalidArgumentException;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Throwable;

class ActionController extends Controller
{
    public function __construct(
        protected ActionRegistry $registry
    ) {}

    /**
     * Get all available actions.
     */
    public function index(): JsonResponse
    {
        $actions = [];
        
        foreach ($this->registry->all() as $name => $actionClass) {
            $actions[$name] = $actionClass::metadata();
        }

        return response()->json([
            'actions' => $actions,
            'count' => count($actions),
        ]);
    }

    /**
     * Execute an action.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $actionData = ActionData::from($request->all());
            
            if (!$this->registry->has($actionData->action)) {
                $data = new ActionExecuteResponseData(
                    success: false,
                    action: $actionData->action,
                    error: 'Action not found',
                    data: [
                        'available_actions' => $this->registry->names()
                    ],
                );
                return $data->toResponse($request)->setStatusCode(404);
            }

            $actionClass = $this->registry->get($actionData->action);
            $action = new $actionClass();
            
            // Create data object for the specific action
            $dataType = $actionClass::getDataType();
            $data = $dataType::from($actionData->data);
            
            $result = $action($data);

            $responseData = new ActionExecuteResponseData(
                success: true,
                action: $actionData->action,
                data: $result,
            );

            return $responseData->toResponse($request)->setStatusCode(200);

        } catch (ValidationException $e) {
            $data = new ActionExecuteResponseData(
                success: false,
                error: 'Validation failed',
                message: $e->getMessage(),
                errors: $e->errors(),
            );
            return $data->toResponse($request)->setStatusCode(400);
            
        } catch (CannotCreateData $e) {
            $data = new ActionExecuteResponseData(
                success: false,
                error: 'Invalid data format',
                message: $e->getMessage(),
            );
            return response()->json($data->toArray(), 400);
            
        } catch (InvalidArgumentException $e) {
            $data = new ActionExecuteResponseData(
                success: false,
                error: 'Invalid action or data',
                message: $e->getMessage(),
            );
            return response()->json($data->toArray(), 400);
            
        } catch (Throwable $e) {
            $data = new ActionExecuteResponseData(
                success: false,
                error: 'Action execution failed',
                message: $e->getMessage(),
            );
            return $data->toResponse($request)->setStatusCode(500);
        }
    }
}

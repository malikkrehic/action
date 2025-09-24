<?php

namespace MK\Action\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use MK\Action\ActionRegistry;
use MK\Action\Data\ActionData;
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
                return response()->json([
                    'error' => 'Action not found',
                    'action' => $actionData->action,
                    'available_actions' => $this->registry->names(),
                ], 404);
            }

            $actionClass = $this->registry->get($actionData->action);
            $action = new $actionClass();
            
            // Create data object for the specific action
            $dataType = $actionClass::getDataType();
            $data = $dataType::from($actionData->data);
            
            $result = $action($data);

            return response()->json([
                'success' => true,
                'action' => $actionData->action,
                'result' => $result,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 400);
            
        } catch (CannotCreateData $e) {
            return response()->json([
                'error' => 'Invalid data format',
                'message' => $e->getMessage(),
            ], 400);
            
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Invalid action or data',
                'message' => $e->getMessage(),
            ], 400);
            
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Action execution failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

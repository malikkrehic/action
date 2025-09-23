<?php

namespace App\Domain\AI\Actions;

use MK\Action\BaseAction;
use App\Domain\AI\Data\CreateChatData;
use App\Domain\AI\Models\AIChatGroup;
use App\Domain\AI\Services\AIModelFactory;
use Illuminate\Support\Facades\Auth;

class CreateChatAction extends BaseAction
{
    public static function name(): string 
    {
        return 'create-chat';
    }

    public static function description(): string
    {
        return 'Creates a new AI chat group for the authenticated user';
    }

    public static function getDataType(): string 
    {
        return CreateChatData::class;
    }

    public function handle(Data $data): array
    {
        try {
            /** @var CreateChatData $data */
            
            $chat = new AIChatGroup();
            $chat->user_id = Auth::id();
            $chat->name = 'New Chat';
            $chat->description = $data->description;
            $chat->model = $data->model;
            $chat->save();

            return [
                'success' => true,
                'chat' => $chat,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

<?php

namespace App\Actions;

use MK\Action\BaseAction;
use App\Data\CreateUserData;
use App\Models\User;
use Spatie\LaravelData\Data;

class CreateUserAction extends BaseAction
{
    public static function name(): string
    {
        return 'create-user';
    }

    public static function description(): string
    {
        return 'Creates a new user in the system with email verification';
    }

    public static function getDataType(): string
    {
        return CreateUserData::class;
    }

    public function handle(Data $data): array
    {
        try {
            /** @var CreateUserData $data */
            
            // You can add authorization here
            // $this->authorize('create', User::class);
            
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => bcrypt($data->password),
                'email_verified_at' => $data->auto_verify ? now() : null,
            ]);

            // You can dispatch events, jobs, etc.
            // event(new UserCreated($user));
            
            return [
                'success' => true,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

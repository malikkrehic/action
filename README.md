# MK Action Package

A powerful Laravel action system for handling business logic with validation and routing.

## Installation

```bash
composer require mk/action
```

## Usage

### Creating an Action

Create a new action by extending the `BaseAction` class:

```php
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
        return 'Creates a new user in the system';
    }

    protected static function getDataType(): string
    {
        return CreateUserData::class;
    }

    protected function handle(Data $data): User
    {
        /** @var CreateUserData $data */
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => bcrypt($data->password),
        ]);
    }
}
```

### Creating Data Objects

Create corresponding data objects using Spatie Laravel Data:

```php
<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CreateUserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
```

### API Endpoints

The package automatically registers two routes:

- `GET /actions` - List all available actions
- `POST /actions` - Execute an action

### Executing Actions

#### Using the Fluent API (Recommended)

```php
use MK\Action\Facades\Action;

// Execute an action fluently
$result = Action::make('create-user')
    ->with([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123'
    ])
    ->execute();

// Or execute directly
$result = Action::execute('create-user', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123'
]);

// Check if action exists
if (Action::has('create-user')) {
    $metadata = Action::metadata('create-user');
}

// Get all actions
$allActions = Action::all();
```

#### Using HTTP Endpoints

##### List all actions:

```bash
curl -X GET /actions
```

Response:

```json
{
  "actions": {
    "create-user": {
      "name": "create-user",
      "description": "Creates a new user in the system",
      "data_type": "App\\Data\\CreateUserData"
    }
  },
  "count": 1
}
```

##### Execute an action:

```bash
curl -X POST /actions \
  -H "Content-Type: application/json" \
  -d '{
    "action": "create-user",
    "data": {
      "name": "John Doe",
      "email": "john@example.com",
      "password": "secret123"
    }
  }'
```

Response:

```json
{
  "success": true,
  "action": "create-user",
  "result": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-09-23T11:00:00.000000Z",
    "updated_at": "2025-09-23T11:00:00.000000Z"
  }
}
```

## Features

- **Auto-discovery**: Actions are automatically discovered and registered
- **Type Safety**: Full PHP 8.3+ type hints and Laravel Data integration
- **Validation**: Built-in validation using Spatie Laravel Data
- **Authorization**: Built-in authorization support via `AuthorizesRequests` trait
- **Error Handling**: Comprehensive error handling with meaningful responses
- **Metadata**: Actions can provide descriptions and metadata

## Requirements

- PHP >= 8.3
- Laravel >= 12.30
- Spatie Laravel Data >= 5.0

## Testing

```bash
composer test
```

## License

MIT

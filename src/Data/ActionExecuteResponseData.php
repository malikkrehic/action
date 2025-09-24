<?php

namespace MK\Action\Data;

use Spatie\LaravelData\Data;

class ActionExecuteResponseData extends Data
{
    /**
     * @param array<int, string>|null $available_actions
     * @param array<string, array<int, string>>|null $errors
     */
    public function __construct(
        public bool $success,
        public ?string $action = null,
        public ?string $error = null,
        public ?string $message = null,
        public ?array $errors = null,
        public ?array $data = null,
    ) {}
}

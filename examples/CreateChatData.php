<?php

namespace App\Domain\AI\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Data;

class CreateChatData extends Data
{
    public function __construct(
        #[Required]
        #[Max(500)]
        public string $description,
        
        #[Required]
        #[In(['gpt-4', 'gpt-3.5-turbo', 'claude-3-sonnet', 'claude-3-haiku'])]
        public string $model,
    ) {}
}

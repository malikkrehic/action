<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class CreateUserData extends Data
{
    public function __construct(
        #[Required]
        #[Min(2)]
        public string $name,
        
        #[Required]
        #[Email]
        #[Unique('users', 'email')]
        public string $email,
        
        #[Required]
        #[Min(8)]
        public string $password,
        
        public bool $auto_verify = false,
    ) {}
}

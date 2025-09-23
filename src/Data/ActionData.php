<?php

namespace MK\Action\Data;

use Spatie\LaravelData\Data;

class ActionData extends Data
{
    public function __construct(
        public string $action,
        public array $data = [],
    ) {}
}

<?php

namespace App\Middleware\Validation;

class GenerateToken extends ValidationMiddleware
{
    protected bool $validateBody = true;
    protected bool $validateQuery = false;

    protected array $queryRules = [];
    protected array $bodyRules = [
        'password' => 'required|is:string',
        'username' => 'required|is:string'
    ];
}

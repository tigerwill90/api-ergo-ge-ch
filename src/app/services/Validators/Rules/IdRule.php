<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class IdRule extends RuleValidator
{
    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::intVal()->intType()->notBlank();
    }
}

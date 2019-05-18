<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class RolesRule extends RuleValidator
{
    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::arrayType()->each(Validator::alpha(':')->noWhitespace()->notEmpty()->length(3, 75))->notEmpty();
    }
}

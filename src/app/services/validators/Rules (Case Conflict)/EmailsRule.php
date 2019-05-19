<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class EmailsRule extends RuleValidator
{
    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::arrayType()->notEmpty()->each(Validator::email()->notBlank());
    }
}

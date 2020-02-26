<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class EventDateRule extends RuleValidator
{

    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::oneOf(
            Validator::arrayType()->each(Validator::date()->notBlank()),
            Validator::nullType()
        );
    }
}

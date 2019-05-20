<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class OfficesNameRule extends RuleValidator
{
    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::arrayType()->each(Validator::alpha('àèáéíóúüÁÉÍÓÚÜñÑ-')->notBlank())->notEmpty();
    }
}

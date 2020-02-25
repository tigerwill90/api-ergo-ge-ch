<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class ImgNameRule extends RuleValidator
{

    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::regex('/^[A-z0-9_-]+\.(png|jpeg|jpg|svg)$/')->notBlank()->length(5, 100);
    }
}

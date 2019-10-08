<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator as V;

class PhonesRule extends RuleValidator
{
    /**
     * Get a validator instance
     * @return V
     */
    public function getValidator(): V
    {
        return V::arrayType()->notEmpty()->each(
            V::key('type', V::in(['Tel.', 'Fax.', 'Pro.'])->notBlank())
            ->key('number', V::alnum('+')->notBlank()->length(8, 45)->stringType())
        );
    }
}

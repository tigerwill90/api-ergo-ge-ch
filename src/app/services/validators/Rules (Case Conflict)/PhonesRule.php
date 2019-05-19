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
            V::key('type', V::in(['tel', 'fax', 'pro'])->notBlank())
            ->key('number', V::phone()->notBlank())
        );
    }
}

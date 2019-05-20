<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator as V;

class ContactsRule extends RuleValidator
{
    /**
     * Get a validator instance
     * @return V
     */
    public function getValidator(): V
    {
        return V::arrayType()
            ->notEmpty()
            ->each(
                V::key('street', V::alnum('èáéíóúüÁÉÍÓÚÜñÑ-')->notBlank()->length(5, 80))
                ->key('city', V::alpha('èáéíóúüÁÉÍÓÚÜñÑ-')->notBlank()->length(2, 45))
                ->key('npa', V::alnum('-')->notBlank()->length(2, 10))
                ->key('cp', V::alnum('-')->notBlank()->length(2, 10), false)
                ->key('phone', V::phone()->notBlank()->length(5, 45), false)
                ->key('fax', V::phone()->notBlank()->length(5, 45), false)
            );
    }
}

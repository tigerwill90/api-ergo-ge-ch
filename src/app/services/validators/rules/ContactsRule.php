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
                V::key('street', V::alnum("àâäèéêëîïôœùûüÿçÀÂÄÈÉÊËÎÏÔŒÙÛÜŸÇ-.,'")->notBlank()->length(5, 80))
                ->key('city', V::alpha("àâäèéêëîïôœùûüÿçÀÂÄÈÉÊËÎÏÔŒÙÛÜŸÇ-/'")->notBlank()->length(2, 45))
                ->key('npa', V::alnum()->notBlank()->length(2, 10))
                ->key('cp', V::alnum('-')->notBlank()->length(2, 10), false)
                ->key('phone', V::alnum('+')->notBlank()->length(8, 45), false)
                ->key('fax', V::alnum('+')->notBlank()->length(8, 45), false)
            );
    }
}

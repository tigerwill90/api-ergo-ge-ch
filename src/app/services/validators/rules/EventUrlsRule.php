<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator as V;

class EventUrlsRule extends RuleValidator
{

    /**
     * Get a validator instance
     * @return V
     */
    public function getValidator(): V
    {
        return V::oneOf(
            V::arrayType()->notEmpty()->each(
                V::key('name',
                    V::alnum("àâäèéêëîïôœùûüÿçÀÂÄÈÉÊËÎÏÔŒÙÛÜŸÇ-'(),&")->notBlank()->length(3, 100)->stringType())
                    ->key('url',
                        V::regex("/(^|[\s.:;?\-\]<\(])(https?:\/\/[-\w;\/?:@&=+$\|\_.!~*\|'()\[\]%#,☺]+[\w\/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])/")->notBlank()->length(5,
                            250))
            ),
            V::nullType()
        );
    }
}

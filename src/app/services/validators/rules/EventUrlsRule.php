<?php

namespace Ergo\Services\Validators\Rules;

use Ergo\Services\Validators\RuleValidator;
use Respect\Validation\Validator;

class EventUrlsRule extends RuleValidator
{

    /**
     * Get a validator instance
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return Validator::oneOf(
            Validator::arrayType()->each(Validator::regex("/(^|[\s.:;?\-\]<\(])(https?:\/\/[-\w;\/?:@&=+$\|\_.!~*\|'()\[\]%#,☺]+[\w\/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])/")->notBlank()->length(5,
                250)),
            Validator::nullType()
        );
    }
}

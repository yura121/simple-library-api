<?php

namespace App\Validator;

use App\AbstractValidator;

class SearchParams extends AbstractValidator
{
    public function filterRawParams()
    {
        $this->setParam(
            self::PARAM__AUTHOR_FULL_NAME,
            $this->filterString($this->getRawParam(self::PARAM__AUTHOR_FULL_NAME))
        );
    }

    public function validate()
    {
        $this->validateRequired(self::PARAM__AUTHOR_FULL_NAME);
        $this->validateMaxLength(self::PARAM__AUTHOR_FULL_NAME, 200);
    }
}

<?php

namespace App\Validator;

use App\AbstractValidator;

class ScannedData extends AbstractValidator
{
    public function filterRawParams()
    {
        $this->setParam(
            self::PARAM__AUTHOR_FULL_NAME,
            $this->filterString($this->getRawParam(self::PARAM__AUTHOR_FULL_NAME))
        );
        $this->setParam(
            self::PARAM__TITLE,
            $this->filterString($this->getRawParam(self::PARAM__TITLE))
        );
        $this->setParam(
            self::PARAM__ISBN,
            $this->filterNumber($this->getRawParam(self::PARAM__ISBN))
        );
        $this->setParam(
            self::PARAM__YEAR,
            $this->filterNumber($this->getRawParam(self::PARAM__YEAR))
        );
    }

    public function validate()
    {
        $this->validateRequired(self::PARAM__AUTHOR_FULL_NAME);
        $this->validateRequired(self::PARAM__TITLE);
        $this->validateRequired(self::PARAM__ISBN);
        $this->validateRequired(self::PARAM__YEAR);

        $this->validateMaxLength(self::PARAM__AUTHOR_FULL_NAME, 200);
        $this->validateMaxLength(self::PARAM__TITLE, 500);
        $this->validateMaxLength(self::PARAM__YEAR, 4);
        $this->validateMaxLength(self::PARAM__ISBN, 13);

        $this->validateMinLength(self::PARAM__YEAR, 4);
        $this->validateMinLength(self::PARAM__ISBN, 10);

        $this->validateIntegerRange(self::PARAM__YEAR, 1900, (int)date('Y'));
    }
}

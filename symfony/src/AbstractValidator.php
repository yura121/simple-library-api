<?php

namespace App;

abstract class AbstractValidator implements ValidatorInterface
{
    const PARAM__AUTHOR_FULL_NAME = 'author_full_name';
    const PARAM__TITLE = 'title';
    const PARAM__ISBN = 'isbn';
    const PARAM__YEAR = 'year';

    protected $errors = [];
    protected $rawParams = [];
    protected $params = [];

    public function __construct(array $rawParams)
    {
        $this->rawParams = $rawParams;
        $this->filterRawParams();
    }

    protected function getRawParam(string $name, $default = '')
    {
        return $this->rawParams[$name] ?? $default;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $name, $default = '')
    {
        return $this->params[$name] ?? $default;
    }

    public function setParam(string $name, $value)
    {
        return $this->params[$name] = $value;
    }

    protected function addError(string $id, string $error)
    {
        if (isset($this->errors[$id])) {
            $this->errors[$id][] = $error;
        } else {
            $this->errors[$id] = [$error];
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        $this->errors = [];
        $this->validate();

        return !count($this->getErrors());
    }

    protected function filterRawParams()
    {

    }

    abstract protected function validate();

    protected function filterString(string $value): ?string
    {
        $value = trim($value);
        // replace many spaces to one
        $value = preg_replace('/\s+/', ' ', $value);
        $value = filter_var($value, FILTER_SANITIZE_STRIPPED);

        return $value;
    }

    protected function filterNumber(string $value): ?string
    {
        $value = trim($value);
        // only digits
        $value = preg_replace('/[^0-9]/', '', $value);

        return $value;
    }

    protected function validateMaxLength(string $name, int $maxLen)
    {
        if (strlen($this->getParam($name)) > $maxLen) {
            $this->addError(
                $name,
                sprintf('The value of parameter "%s" must not exceed %s characters', $name, $maxLen)
            );
        }
    }

    protected function validateMinLength(string $name, int $minLen)
    {
        if (strlen($this->getParam($name)) < $minLen) {
            $this->addError(
                $name,
                sprintf('The value of parameter "%s" must not be less than 3 characters', $name, $minLen)
            );
        }
    }

    protected function validateIntegerRange(string $name, int $minValue, int $maxValue)
    {
        $opt = [
            'options' =>
                [
                    'min_range' => $minValue,
                    'max_range' => $maxValue,
                ],
        ];
        if (filter_var((int)$this->getParam($name), FILTER_VALIDATE_INT, $opt) === false) {
            $this->addError(
                $name,
                sprintf('The value of parameter "%s" is outside the allowed range', $name)
            );
        }
    }

    protected function validateRequired(string $name)
    {
        if (!$this->getParam($name)) {
            $this->addError($name, sprintf('Parameter "%s" is required', $name));
        }
    }
}

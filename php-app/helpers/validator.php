<?php
declare(strict_types=1);

/**
 * Validator helper — rule-based input validation.
 *
 * Usage:
 *   $v = new Validator($_POST);
 *   $v->required('email')->email('email')
 *     ->required('password')->min('password', 8);
 *
 *   if ($v->fails()) {
 *       $errors = $v->errors();   // ['field' => 'First error message', ...]
 *   }
 *
 * Supported rules (chainable):
 *   required(field, message?)
 *   min(field, length, message?)
 *   max(field, length, message?)
 *   between(field, min, max, message?)
 *   email(field, message?)
 *   matches(field, otherField, message?)    — e.g. password confirmation
 *   alpha(field, message?)                  — letters only
 *   alphaNum(field, message?)               — letters + digits only
 *   numeric(field, message?)
 *   regex(field, pattern, message?)
 *   custom(field, callable, message?)       — fn(value): bool
 */
class Validator
{
    /** @var array<string,mixed> */
    private array $data;

    /** @var array<string,string> first error per field */
    private array $errors = [];

    /**
     * @param array<string,mixed> $data  Typically $_POST or a parsed JSON body.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val == null || $val == '') $this->addError($field, $message ?: trans('validation_required', ['field' => ucfirst($field)]));
        return $this;
    }

    public function min(string $field, int $length, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && strlen($val) < $length) $this->addError($field, $message ?: trans('validation_min', ['field' => ucfirst($field), 'min' => $length]));
        return $this;
    }

    public function max(string $field, int $length, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && strlen($val) > $length) $this->addError($field, $message ?: trans('validation_max', ['field' => ucfirst($field), 'max' => $length]));
        return $this;
    }

    public function between(string $field, int $min, int $max, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null) {
            $len = strlen($val);
            if ($len < $min || $len > $max) $this->addError($field, $message ?: trans('validation_between', ['field' => ucfirst($field), 'min' => $min, 'max' => $max]));
        }
        return $this;
    }

    public function email(string $field, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && $val != '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) $this->addError($field, $message ?: trans('validation_email'));
        return $this;
    }

    public function matches(string $field, string $otherField, string $message = ''): static
    {
        $val   = $this->value($field);
        $other = $this->value($otherField);
        if ($val != $other) $this->addError($field, $message ?: trans('validation_matches', ['field' => ucfirst($field), 'other' => $otherField]));
        return $this;
    }

    public function alpha(string $field, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && $val != '' && !ctype_alpha($val)) $this->addError($field, $message ?: trans('validation_alpha', ['field' => ucfirst($field)]));
        return $this;
    }

    public function alphaNum(string $field, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && $val != '' && !ctype_alnum($val)) $this->addError($field, $message ?: trans('validation_alphanum', ['field' => ucfirst($field)]));
        return $this;
    }

    public function numeric(string $field, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && $val != '' && !is_numeric($val)) $this->addError($field, $message ?: trans('validation_numeric', ['field' => ucfirst($field)]));
        return $this;
    }

    public function regex(string $field, string $pattern, string $message = ''): static
    {
        $val = $this->value($field);
        if ($val != null && $val != '' && !preg_match($pattern, $val)) $this->addError($field, $message ?: trans('validation_regex', ['field' => ucfirst($field)]));
        return $this;
    }

    /**
     * Custom rule — pass any callable that receives the field value and returns bool.
     *
     * Example:
     *   $v->custom('email', fn($v) => !db_row('SELECT id FROM users WHERE email=:e', [':e'=>$v]),
     *              'Email is already registered.');
     *
     * @param callable(mixed): bool $fn  Return true = passes, false = fails.
     */
    public function custom(string $field, callable $fn, string $message = ''): static
    {
        $val = $this->value($field);
        if (!$fn($val)) {
            $this->addError($field, $message ?: trans('validation_invalid', ['field' => ucfirst($field)]));
        }
        return $this;
    }

    // -----------------------------------------------------------------------
    // Result
    // -----------------------------------------------------------------------

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Return all errors as an associative array  ['field' => 'message'].
     *
     * @return array<string,string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return errors as a flat list of messages (useful for page-level display).
     *
     * @return list<string>
     */
    public function errorList(): array
    {
        return array_values($this->errors);
    }

    public function value(string $field): ?string
    {
        if (!array_key_exists($field, $this->data)) {
            return null;
        }
        return trim((string) $this->data[$field]);
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) $this->errors[$field] = $message;
    }
}

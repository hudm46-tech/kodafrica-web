<?php
declare(strict_types=1);
namespace App\Core;

final class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        $v = trim((string)($this->data[$field] ?? ''));
        if ($v === '') $this->errors[$field] = "$label is required.";
        return $this;
    }

    public function min(string $field, int $len, string $label): self
    {
        $v = (string)($this->data[$field] ?? '');
        if (strlen($v) < $len) $this->errors[$field] = "$label must be at least $len characters.";
        return $this;
    }

    public function max(string $field, int $len, string $label): self
    {
        $v = (string)($this->data[$field] ?? '');
        if (strlen($v) > $len) $this->errors[$field] = "$label must be at most $len characters.";
        return $this;
    }

    public function email(string $field, string $label): self
    {
        $v = (string)($this->data[$field] ?? '');
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label must be a valid email.";
        }
        return $this;
    }

    public function matches(string $a, string $b, string $label): self
    {
        if (($this->data[$a] ?? null) !== ($this->data[$b] ?? null)) {
            $this->errors[$b] = "$label does not match.";
        }
        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        $v = $this->data[$field] ?? null;
        if ($v !== null && $v !== '' && !is_numeric($v)) {
            $this->errors[$field] = "$label must be a number.";
        }
        return $this;
    }

    public function date(string $field, string $label): self
    {
        $v = (string)($this->data[$field] ?? '');
        if ($v !== '' && !strtotime($v)) {
            $this->errors[$field] = "$label must be a valid date.";
        }
        return $this;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }

    public function first(string $field): ?string { return $this->errors[$field] ?? null; }
}

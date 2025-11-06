<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrictNonNegativeRule implements ValidationRule
{
    public function __construct(
        private readonly string $label,
        private readonly bool $mustBeInteger = false,
        private readonly ?float $minValue = 0.0,
        private readonly bool $minInclusive = true,
        private readonly ?float $maxValue = null,
        private readonly bool $maxInclusive = true,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->mustBeInteger) {
            if (!is_int($value)) {
                if (is_float($value)) {
                    $fail("Il campo {$this->label} deve essere un numero intero.");
                } else {
                    $fail("Il campo {$this->label} deve essere un numero intero (non stringa).");
                }
                return;
            }
        } else {
            if (!is_int($value) && !is_float($value)) {
                $fail("Il campo {$this->label} deve essere un numero (non stringa).");
                return;
            }
        }

        $numericValue = (float) $value;

        if ($this->minValue !== null) {
            $violatesMin = $this->minInclusive
                ? $numericValue < $this->minValue
                : $numericValue <= $this->minValue;

            if ($violatesMin) {
                $fail(
                    "Il campo {$this->label} deve essere " .
                    ($this->minInclusive ? 'maggiore o uguale a ' : 'maggiore di ') .
                    $this->formatNumber($this->minValue) . '.'
                );
                return;
            }
        }

        if ($this->maxValue !== null) {
            $violatesMax = $this->maxInclusive
                ? $numericValue > $this->maxValue
                : $numericValue >= $this->maxValue;

            if ($violatesMax) {
                $fail(
                    "Il campo {$this->label} deve essere " .
                    ($this->maxInclusive ? 'minore o uguale a ' : 'minore di ') .
                    $this->formatNumber($this->maxValue) . '.'
                );
            }
        }
    }

    private function formatNumber(float $number): string
    {
        return fmod($number, 1.0) === 0.0 ? (string) (int) $number : rtrim(rtrim(number_format($number, 6, '.', ''), '0'), '.');
    }
}


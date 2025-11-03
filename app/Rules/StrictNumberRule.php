<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrictNumberRule implements ValidationRule
{
    public function __construct(
        private readonly string $label,
        private readonly bool $mustBeInteger = false,
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
            }

            return;
        }

        if (!is_int($value) && !is_float($value)) {
            $fail("Il campo {$this->label} deve essere un numero (non stringa).");
        }
    }
}

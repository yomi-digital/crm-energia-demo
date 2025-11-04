<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class MaintenanceCostRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $option = strtoupper((string) ($this->data['PREVENTIVI']['opzione_manutenzione_salvata'] ?? ''));
        $hasValue = !is_null($value) && $value !== '';

        if ($option === 'SI') {
            if (!$hasValue) {
                $fail('Il campo costo_annuo_manutenzione_salvato deve essere maggiore di 0 quando l\'opzione manutenzione è si.');
                return;
            }
        } elseif (!$hasValue) {
            return;
        }

        if (!is_int($value) && !is_float($value)) {
            $fail('Il campo costo_annuo_manutenzione_salvato deve essere un numero (non stringa).');
            return;
        }

        $numericValue = (float) $value;

        if ($option === 'SI') {
            if ($numericValue <= 0) {
                $fail('Il campo costo_annuo_manutenzione_salvato deve essere maggiore di 0 quando l\'opzione manutenzione è si.');
            }
        } elseif ($option === 'NO') {
            if ($numericValue !== 0.0) {
                $fail('Il campo costo_annuo_manutenzione_salvato deve essere 0 quando l\'opzione manutenzione è no.');
            }
        }
    }
}

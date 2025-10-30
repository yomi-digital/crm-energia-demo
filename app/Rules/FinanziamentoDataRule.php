<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FinanziamentoDataRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                $fail('Il campo :attribute deve essere un oggetto valido.');
                return;
            }
            $data = $decoded;
        } elseif (is_array($value)) {
            $data = $value;
        } else {
            $fail('Il campo :attribute deve essere un oggetto valido.');
            return;
        }

        if (!array_key_exists('rate_import', $data)) {
            $fail("Il campo :attribute deve contenere la chiave 'rate_import'.");
            return;
        }

        if (!array_key_exists('number_of_rate', $data)) {
            $fail("Il campo :attribute deve contenere la chiave 'number_of_rate'.");
            return;
        }

        if (!is_int($data['rate_import']) && !is_float($data['rate_import'])) {
            $fail("Il valore 'rate_import' nel campo :attribute deve essere un numero (non stringa).");
            return;
        }

        if ((float) $data['rate_import'] <= 0) {
            $fail("Il valore 'rate_import' nel campo :attribute deve essere maggiore di 0.");
            return;
        }

        if (!is_int($data['number_of_rate'])) {
            if (is_float($data['number_of_rate'])) {
                $fail("Il valore 'number_of_rate' nel campo :attribute deve essere un numero intero.");
            } else {
                $fail("Il valore 'number_of_rate' nel campo :attribute deve essere un numero intero (non stringa).");
            }
            return;
        }

        if ($data['number_of_rate'] <= 0) {
            $fail("Il valore 'number_of_rate' nel campo :attribute deve essere maggiore di 0.");
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BonificoDataRule implements ValidationRule
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

        $requiredKeys = ['first_rate', 'second_rate', 'third_rate'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                $fail("Il campo :attribute deve contenere la chiave '{$key}'.");
                return;
            }

            if (!is_int($data[$key]) && !is_float($data[$key])) {
                $fail("Il valore '{$key}' nel campo :attribute deve essere un numero (non stringa).");
                return;
            }

            if ((float) $data[$key] <= 0) {
                $fail("Il valore '{$key}' nel campo :attribute deve essere maggiore di 0.");
                return;
            }
        }

        $first = (float) $data['first_rate'];
        $second = (float) $data['second_rate'];
        $third = (float) $data['third_rate'];

        $total = $first + $second + $third;
        if (abs($total - 100) > 0.0001) {
            $fail('La somma di first_rate, second_rate e third_rate deve essere pari a 100.');
            return;
        }

        if (abs(($first + $second) - 80) > 0.0001) {
            $fail('La somma di first_rate e second_rate deve essere pari a 80.');
            return;
        }
    }
}

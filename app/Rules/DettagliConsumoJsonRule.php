<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DettagliConsumoJsonRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (!is_array($decoded)) {
                $fail('Il campo :attribute deve essere un array valido.');
                return;
            }

            $data = $decoded;
        } elseif (is_array($value)) {
            $data = $value;
        } else {
            $fail('Il campo :attribute deve essere un array valido.');
            return;
        }

        $count = count($data);
        if ($count !== 6 && $count !== 12) {
            $fail('Il campo :attribute deve contenere esattamente 6 (per dati bimestrali) o 12 (per dati mensili) elementi.');
            return;
        }

        foreach ($data as $index => $item) {
            if (!is_array($item)) {
                $fail("L'elemento {$index} del campo :attribute deve essere un oggetto.");
                return;
            }

            if (!isset($item['periodo']) || !is_string($item['periodo'])) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'periodo' di tipo stringa.");
                return;
            }

            if (!array_key_exists('f1_kwh', $item)) {
                $fail("L'elemento {$index} del campo :attribute deve contenere il campo 'f1_kwh'.");
                return;
            }

            if (!is_int($item['f1_kwh']) && !is_float($item['f1_kwh'])) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'f1_kwh' di tipo numerico (non stringa).");
                return;
            }

            if ((float) $item['f1_kwh'] <= 0) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'f1_kwh' maggiore di 0.");
                return;
            }

            if (!array_key_exists('f2_kwh', $item)) {
                $fail("L'elemento {$index} del campo :attribute deve contenere il campo 'f2_kwh'.");
                return;
            }

            if (!is_int($item['f2_kwh']) && !is_float($item['f2_kwh'])) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'f2_kwh' di tipo numerico (non stringa).");
                return;
            }

            if ((float) $item['f2_kwh'] <= 0) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'f2_kwh' maggiore di 0.");
                return;
            }

            if (!array_key_exists('f3_kwh', $item)) {
                $fail("L'elemento {$index} del campo :attribute deve contenere il campo 'f3_kwh'.");
                return;
            }

            if (!is_int($item['f3_kwh']) && !is_float($item['f3_kwh'])) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'f3_kwh' di tipo numerico (non stringa).");
                return;
            }

            if ((float) $item['f3_kwh'] <= 0) {
                $fail("L'elemento {$index} del campo :attribute deve contenere un campo 'f3_kwh' maggiore di 0.");
                return;
            }
        }
    }
}

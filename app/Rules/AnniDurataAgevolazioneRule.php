<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class AnniDurataAgevolazioneRule implements ValidationRule, DataAwareRule
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
        $index = explode('.', $attribute)[1];
        $tipoVoce = $this->data['PREVENTIVI_VOCE_ECONOMICHE'][$index]['tipo_voce_salvata'] ?? null;

        if ($value === null || $value === '') {
            if ($tipoVoce === 'incentivo') {
                $fail('Il campo anni_durata_agevolazione_salvata deve essere minimo 1 se la tipologia di voce è incentivo.');
            }
            return;
        }

        if (!is_int($value)) {
            if (is_float($value)) {
                $fail('Il campo anni_durata_agevolazione_salvata deve essere un numero intero.');
            } else {
                $fail('Il campo anni_durata_agevolazione_salvata deve essere un numero intero (non stringa).');
            }
            return;
        }

        if ($tipoVoce === 'incentivo') {
            if ($value < 1) {
                $fail('Il campo anni_durata_agevolazione_salvata deve essere minimo 1 se la tipologia di voce è incentivo.');
            }
        } else {
            if ($value !== 0) {
                $fail('Il campo anni_durata_agevolazione_salvata deve essere 0 se la tipologia di voce non è incentivo.');
            }
        }
    }
}

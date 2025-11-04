<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

class BusinessPlanAnnoSimulazioneRule implements ValidationRule, DataAwareRule
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
        $businessPlan = $this->data['DETTAGLIO_BUSINESS_PLAN'] ?? [];

        if (count($businessPlan) !== 20) {
            $fail('Il campo DETTAGLIO_BUSINESS_PLAN deve contenere esattamente 20 elementi.');
            return;
        }

        foreach ($businessPlan as $index => $item) {
            $expectedAnno = $index + 1;
            if (!isset($item['anno_simulazione']) || $item['anno_simulazione'] !== $expectedAnno) {
                $fail("L'elemento {$index} di DETTAGLIO_BUSINESS_PLAN: anno_simulazione deve essere {$expectedAnno}.");
                return;
            }
        }
    }
}

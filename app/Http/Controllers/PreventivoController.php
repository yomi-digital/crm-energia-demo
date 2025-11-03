<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Rules\DettagliConsumoJsonRule;
use App\Rules\AnniDurataAgevolazioneRule;
use App\Rules\BusinessPlanAnnoSimulazioneRule;
use App\Rules\BonificoDataRule;
use App\Rules\FinanziamentoDataRule;
use App\Rules\MaintenanceCostRule;
use App\Rules\AssicurazioneCostRule;
use App\Rules\StrictPositiveNumberRule;
use Illuminate\Validation\Rule;
use Closure;
use App\Rules\StrictNumberRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Preventivo;
use App\Models\ConsumoPreventivo;
use App\Models\DettaglioProdottoPreventivo;
use App\Models\PreventivoVoceEconomica;
use App\Models\DettaglioBusinessPlan;
use App\Services\PreventivoPdfService;

class PreventivoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentYear = (int) date('Y');
        $maxYear = $currentYear + 10;

        // Sistemazione di alcuni valori.
        $request->merge([
            'PREVENTIVI.stato' => strtolower((string) $request->input('PREVENTIVI.stato', '')),
            'PREVENTIVI.modalita_pagamento_salvata' => strtolower((string) $request->input('PREVENTIVI.modalita_pagamento_salvata', '')),
            'PREVENTIVI.esposizione_salvata' => strtoupper(str_replace(['-', '_'], ' ', (string) $request->input('PREVENTIVI.esposizione_salvata', ''))),
            'CONSUMI_PREVENTIVO.tipologia_bolletta' => strtolower((string) $request->input('CONSUMI_PREVENTIVO.tipologia_bolletta', '')),
        ]);

        $preventiviVoceEconomiche = $request->input('PREVENTIVI_VOCE_ECONOMICHE');
        if (is_array($preventiviVoceEconomiche)) {
            foreach ($preventiviVoceEconomiche as $key => $voce) {
                if (isset($voce['tipo_voce_salvata']) && is_string($voce['tipo_voce_salvata'])) {
                    $preventiviVoceEconomiche[$key]['tipo_voce_salvata'] = strtolower($voce['tipo_voce_salvata']);
                }
            }

            $request->merge([
                'PREVENTIVI_VOCE_ECONOMICHE' => $preventiviVoceEconomiche,
            ]);
        }

        $validatedData = $request->validate([
            'PREVENTIVI' => 'required|array',
            'PREVENTIVI.fk_cliente' => ['required', new StrictPositiveNumberRule('PREVENTIVI.fk_cliente', true, 1, true)],
            'PREVENTIVI.fk_agente' => ['required', new StrictPositiveNumberRule('PREVENTIVI.fk_agente', true, 1, true)],
            'PREVENTIVI.stato' => 'required|string|in:protocollato',
            'PREVENTIVI.tetto_salvato' => 'required|string|max:255',
            'PREVENTIVI.area_geografica_salvata' => 'required|string|in:sud,nord,centro,isole',
            'PREVENTIVI.esposizione_salvata' => 'required|string|in:nord,nord est,nord ovest,sud,sud est,sud ovest,est,ovest',
            'PREVENTIVI.produzione_annua_stimata' => ['required', new StrictPositiveNumberRule('PREVENTIVI.produzione_annua_stimata', false, 0, true)],
            'PREVENTIVI.risparmio_autoconsumo_annuo' => ['required', new StrictPositiveNumberRule('PREVENTIVI.risparmio_autoconsumo_annuo', false, 0, true)],
            'PREVENTIVI.vendita_eccedenze_rid_annua' => ['required', new StrictPositiveNumberRule('PREVENTIVI.vendita_eccedenze_rid_annua', false, 0, true)],
            'PREVENTIVI.incentivo_cer_annuo' => ['required', new StrictPositiveNumberRule('PREVENTIVI.incentivo_cer_annuo', false, 0, true)],
            'PREVENTIVI.detrazione_fiscale_annua' => ['nullable', new StrictPositiveNumberRule('PREVENTIVI.detrazione_fiscale_annua', false, 0, true)],
            'PREVENTIVI.modalita_pagamento_salvata' => 'required|string|in:bonifico,finanziamento',
            'PREVENTIVI.bonifico_data_json' => [
                'nullable',
                'array',
                new BonificoDataRule(),
                'required_without:PREVENTIVI.finanziamento_data_json',
                'required_if:PREVENTIVI.modalita_pagamento_salvata,bonifico',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    if (!empty($value) && !empty($request->input('PREVENTIVI.finanziamento_data_json'))) {
                        $fail('Non è possibile fornire sia i dati del bonifico che quelli del finanziamento.');
                    }
                },
            ],
            'PREVENTIVI.finanziamento_data_json' => [
                'nullable',
                'array',
                new FinanziamentoDataRule(),
                'required_without:PREVENTIVI.bonifico_data_json',
                'required_if:PREVENTIVI.modalita_pagamento_salvata,finanziamento',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    if (!empty($value) && !empty($request->input('PREVENTIVI.bonifico_data_json'))) {
                        $fail('Non è possibile fornire sia i dati del bonifico che quelli del finanziamento.');
                    }
                },
            ],
            'PREVENTIVI.opzione_manutenzione_salvata' => 'required|string|in:si,no',
            'PREVENTIVI.costo_annuo_manutenzione_salvato' => [
                'nullable',
                'required_if:PREVENTIVI.opzione_manutenzione_salvata,si',
                new MaintenanceCostRule(),
            ],
            'PREVENTIVI.opzione_assicurazione_salvata' => 'required|string|in:si,no',
            'PREVENTIVI.costo_annuo_assicurazione_salvato' => [
                'nullable',
                'required_if:PREVENTIVI.opzione_assicurazione_salvata,si',
                new AssicurazioneCostRule(),
            ],

            'CONSUMI_PREVENTIVO' => 'required|array',
            'CONSUMI_PREVENTIVO.anno_partenza' => [
                'required',
                new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.anno_partenza', true, $currentYear, true, $maxYear, true),
            ],
            'CONSUMI_PREVENTIVO.mese_partenza' => [
                'required',
                new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.mese_partenza', true, 1, true, 12, true),
            ],
            'CONSUMI_PREVENTIVO.costo_kwh_bolletta_medio' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.costo_kwh_bolletta_medio')],
            'CONSUMI_PREVENTIVO.costo_kwh_bolletta_totale' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.costo_kwh_bolletta_totale')],
            'CONSUMI_PREVENTIVO.totale_consumo_annuo' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.totale_consumo_annuo')],
            'CONSUMI_PREVENTIVO.totale_costi_annui' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.totale_costi_annui')],
            'CONSUMI_PREVENTIVO.tipologia_bolletta' => 'required|string|in:mensile,bimestrale',
            'CONSUMI_PREVENTIVO.dettagli_consumo_json' => ['required', 'array', new DettagliConsumoJsonRule()],
            'CONSUMI_PREVENTIVO.consumo_diurno_annuo' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.consumo_diurno_annuo')],
            'CONSUMI_PREVENTIVO.consumo_notturno_annuo' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.consumo_notturno_annuo')],
            'CONSUMI_PREVENTIVO.capacita_batteria_consigliata' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.capacita_batteria_consigliata')],

            'DETTAGLI_PRODOTTO_PREVENTIVO' => 'required|array',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.nome_prodotto_salvato' => 'required|string|max:255',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.categoria_prodotto_salvata' => 'required|string|max:255',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.quantita' => ['required', new StrictPositiveNumberRule('DETTAGLI_PRODOTTO_PREVENTIVO.*.quantita', true, 1, true)],
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.prezzo_unitario_salvato' => ['required', new StrictPositiveNumberRule('DETTAGLI_PRODOTTO_PREVENTIVO.*.prezzo_unitario_salvato', false, 0, true)],
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.capacita_batteria_salvata' => 'nullable|numeric|min:0',

            'PREVENTIVI_VOCE_ECONOMICHE' => 'nullable|array',
            'PREVENTIVI_VOCE_ECONOMICHE.*.nome_voce_salvato' => 'required|string|max:255',
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_voce_salvata' => ['required', 'string', Rule::in(['incentivo', 'sconto', 'costo'])],
            'PREVENTIVI_VOCE_ECONOMICHE.*.valore_applicato' => ['required', new StrictPositiveNumberRule('PREVENTIVI_VOCE_ECONOMICHE.*.valore_applicato', false, 0, true)],
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_valore_salvato' => 'required|string|in:%,€',
            'PREVENTIVI_VOCE_ECONOMICHE.*.anni_durata_agevolazione_salvata' => [
                'nullable',
                new AnniDurataAgevolazioneRule(),
            ],

            'DETTAGLIO_BUSINESS_PLAN' => [
                'nullable',
                'array',
                new BusinessPlanAnnoSimulazioneRule(),
            ],
            'DETTAGLIO_BUSINESS_PLAN.*.anno_simulazione' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.anno_simulazione', true, 1, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_investimento' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_investimento', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_assicurazione' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_assicurazione', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_manutenzione' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_manutenzione', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_risparmio_bolletta' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_risparmio_bolletta', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_vendita_eccedenze' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_vendita_eccedenze', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_incentivo_cer' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_incentivo_cer', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_fondo_perduto' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_fondo_perduto', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_annuo' => ['required', new StrictNumberRule('DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_annuo')],
            'DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_cumulato' => ['required', new StrictNumberRule('DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_cumulato')],
        ], [
            'PREVENTIVI.esposizione_salvata.in' => "Il campo PREVENTIVI.esposizione_salvata deve essere uno tra: nord, nord est, nord ovest, sud, sud est, sud ovest, est, ovest.",
            'PREVENTIVI.area_geografica_salvata.in' => "Il campo PREVENTIVI.area_geografica_salvata deve essere uno tra: sud, nord, centro, isole.",
            'CONSUMI_PREVENTIVO.tipologia_bolletta' => "Il campo CONSUMI_PREVENTIVO.tipologia_bolletta deve essere uno tra: mensile, bimestrale.",
            'PREVENTIVI.modalita_pagamento_salvata.in' => "Il campo PREVENTIVI.modalita_pagamento_salvata deve essere uno tra: bonifico, finanziamento. (lowercase)",
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_voce_salvata.in' => "Il campo PREVENTIVI_VOCE_ECONOMICHE.*.tipo_voce_salvata deve essere uno tra: incentivo, sconto, costo.",
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_valore_salvato.in' => "Il campo PREVENTIVI_VOCE_ECONOMICHE.*.tipo_valore_salvato deve essere uno tra: %, €. % per gli incentivi e € per le altre tipologie di voce.",
        ]);


        //Transaction per aggiungere il preventivo a database
        try {
            $preventivoId = DB::transaction(function () use ($validatedData) {
                // 1. Prepara i dati del preventivo convertendo i JSON in stringhe
                $preventivoData = $validatedData['PREVENTIVI'];
                if (isset($preventivoData['bonifico_data_json']) && is_array($preventivoData['bonifico_data_json'])) {
                    $preventivoData['bonifico_data_json'] = json_encode($preventivoData['bonifico_data_json']);
                }
                if (isset($preventivoData['finanziamento_data_json']) && is_array($preventivoData['finanziamento_data_json'])) {
                    $preventivoData['finanziamento_data_json'] = json_encode($preventivoData['finanziamento_data_json']);
                }

                // 2. Crea il preventivo
                $preventivo = Preventivo::create($preventivoData);
                $preventivoId = $preventivo->id_preventivo;

                // 3. Crea il consumo_preventivo con fk_preventivo e dettagli_consumo_json come JSON string
                $consumoData = $validatedData['CONSUMI_PREVENTIVO'];
                $consumoData['fk_preventivo'] = $preventivoId;
                // Converti anno_partenza e mese_partenza in stringhe come richiesto dalla tabella
                $consumoData['anno_partenza'] = (string) $consumoData['anno_partenza'];
                $consumoData['mese_partenza'] = (string) $consumoData['mese_partenza'];
                if (isset($consumoData['dettagli_consumo_json']) && is_array($consumoData['dettagli_consumo_json'])) {
                    $consumoData['dettagli_consumo_json'] = json_encode($consumoData['dettagli_consumo_json']);
                }
                ConsumoPreventivo::create($consumoData);

                // 4. Crea i dettagli_prodotto_preventivo (array) con fk_preventivo
                foreach ($validatedData['DETTAGLI_PRODOTTO_PREVENTIVO'] as $prodottoData) {
                    $prodottoData['fk_preventivo'] = $preventivoId;
                    DettaglioProdottoPreventivo::create($prodottoData);
                }

                // 5. Crea le preventivo_voci_economiche (array opzionale) con fk_preventivo
                if (!empty($validatedData['PREVENTIVI_VOCE_ECONOMICHE'])) {
                    foreach ($validatedData['PREVENTIVI_VOCE_ECONOMICHE'] as $voceData) {
                        $voceData['fk_preventivo'] = $preventivoId;
                        PreventivoVoceEconomica::create($voceData);
                    }
                }

                // 6. Crea i dettaglio_business_plan (array opzionale) con fk_preventivo
                if (!empty($validatedData['DETTAGLIO_BUSINESS_PLAN'])) {
                    foreach ($validatedData['DETTAGLIO_BUSINESS_PLAN'] as $businessPlanData) {
                        $businessPlanData['fk_preventivo'] = $preventivoId;
                        DettaglioBusinessPlan::create($businessPlanData);
                    }
                }

                // 7. Genera il PDF e salvalo su DigitalOcean Spaces
                $preventivoWithRelations = Preventivo::with([
                    'dettagliProdotti',
                    'vociEconomiche',
                    'dettagliBusinessPlan',
                    'consumi'
                ])->find($preventivoId);

                $pdfService = new PreventivoPdfService();
                $pdfPath = $pdfService->generateAndSavePdf($preventivoWithRelations);

                // 8. Aggiorna il preventivo con il percorso del PDF (file privato)
                // Per ottenere un URL temporaneo, usare: $pdfService->getTemporaryUrl($preventivo)
                $preventivoWithRelations->update(['pdf_url' => $pdfPath]);

                return $preventivoId;
            });

            return response()->json([
                'message' => 'Preventivo creato con successo!',
                'data' => [
                    'id_preventivo' => $preventivoId,
                    'validated_data' => $validatedData,
                ],
            ], 201);
            
        } catch (\Exception $e) {
            // Se la transaction fallisce, Laravel esegue automaticamente il rollback
            // Restituiamo una risposta di errore appropriata
            Log::error('Errore nella creazione del preventivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData,
            ]);

            return response()->json([
                'message' => 'Errore durante la creazione del preventivo.',
                'error' => config('app.debug') ? $e->getMessage() : 'Si è verificato un errore durante il salvataggio dei dati.',
            ], 500);
        }
    }
}

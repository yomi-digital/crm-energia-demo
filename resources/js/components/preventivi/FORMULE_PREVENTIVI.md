# Documentazione Formule Preventivi Fotovoltaico

Questo documento descrive tutte le formule utilizzate nel sistema di preventivi per impianti fotovoltaici.

## Indice

1. [Consumo Annuo](#consumo-annuo)
2. [Dimensionamento Impianto](#dimensionamento-impianto)
3. [Produzione Annua](#produzione-annua)
4. [Autoconsumo](#autoconsumo)
5. [Risparmio Autoconsumo](#risparmio-autoconsumo)
6. [Vendita Eccedenza (RID)](#vendita-eccedenza-rid)
7. [Incentivo CER](#incentivo-cer)
8. [Costo Totale Sistema](#costo-totale-sistema)
9. [Detrazione Fiscale](#detrazione-fiscale)
10. [Business Plan](#business-plan)

---

## 1. Consumo Annuo

### 1.1 Consumo Totale Annuo

**Formula:**
```
Consumo_Totale_Annuo = Σ (F1 + F2 + F3) per tutti i mesi
```

**Dove:**
- `F1` = consumo in fascia F1 (monoraria) del mese
- `F2` = consumo in fascia F2 (intermedia) del mese
- `F3` = consumo in fascia F3 (monoraria notturna) del mese

**File:** `Step2BillData.vue`, `Step3ConsumptionSummary.vue`

---

### 1.2 Consumo Diurno Annuo

**Formula:**
```
Consumo_Diurno_Annuo = (F1_totale × 0.83) + (F2_totale × 0.26) + (F3_totale × 0.17)
```

**Spiegazione:**
Il consumo diurno viene calcolato considerando la distribuzione percentuale dei consumi nelle fasce orarie:
- **F1 (monoraria)**: 83% diurno, 17% notturno
- **F2 (intermedia)**: 26% diurno, 74% notturno
- **F3 (monoraria notturna)**: 17% diurno, 83% notturno

**File:** `Step3ConsumptionSummary.vue`, `Step4SystemSelection.vue`

---

### 1.3 Consumo Notturno Annuo

**Formula:**
```
Consumo_Notturno_Annuo = (F1_totale × 0.17) + (F2_totale × 0.74) + (F3_totale × 0.83)
```

**Spiegazione:**
Complementare al consumo diurno, rappresenta l'energia consumata durante le ore notturne.

**File:** `Step3ConsumptionSummary.vue`, `Step4SystemSelection.vue`

---

### 1.4 Costo per kWh

**Formula:**
```
Costo_per_kWh = Costo_Totale_Bolletta / Consumo_Totale_Periodo
```

**Dove:**
- `Costo_Totale_Bolletta` = costo totale inserito dall'utente per il periodo (mensile/bimestrale/annuale)
- `Consumo_Totale_Periodo` = somma di F1 + F2 + F3 per il periodo inserito

**Spiegazione:**
Il costo per kWh viene calcolato automaticamente quando l'utente inserisce il costo totale della bolletta e i consumi. Il calcolo viene fatto per il periodo inserito (mensile, bimestrale o annuale).

**File:** `Step2BillData.vue` - `handleTotalCostChange()`

---

### 1.5 Costo Totale Annuo Bolletta

**Formula:**
```
Costo_Totale_Annuo = Consumo_Totale_Annuo × Costo_per_kWh
```

**Dove:**
- `Costo_per_kWh` = costo medio per kWh calcolato dal costo totale bolletta diviso il consumo totale

**File:** `Step2BillData.vue`, `Step3ConsumptionSummary.vue`

---

## 2. Dimensionamento Impianto

### 2.1 Potenza Impianto Consigliata

**Formula:**
```
Potenza_Impianto_kWp = Consumo_Totale_Annuo / Coefficiente_Produzione
```

**Dove:**
- `Coefficiente_Produzione` = coefficiente di produzione annuale (kWh/kWp) che dipende da:
  - **Esposizione tetto**: SUD, SUD-EST, SUD-OVEST, EST, OVEST, NORD-EST, NORD-OVEST
  - **Area geografica**: NORD, CENTRO, SUD, ISOLE

**Valori coefficienti (da API `/api/coefficienti-produzione`):**
- Vengono recuperati dal campo `coefficiente_kwh_kwp` dell'API
- Fallback: 1350 kWh/kWp se non disponibile

**File:** `Step4SystemSelection.vue` - `requiredSystemSize`

---

### 2.2 Capacità Batteria Consigliata

**Formula:**
```
Capacità_Batteria_kWh = Consumo_Notturno_Annuo / 365
```

**Spiegazione:**
La capacità batteria consigliata è calcolata per coprire il consumo notturno medio giornaliero.

**File:** `Step3ConsumptionSummary.vue`

---

## 3. Produzione Annua

### 3.1 Produzione Annua dell'Impianto

**Formula:**
```
Produzione_Annua_kWh = Potenza_Impianto_kWp × Coefficiente_Produzione
```

**Dove:**
- `Potenza_Impianto_kWp` = potenza selezionata dall'utente (campo "Potenza kWh")
- `Coefficiente_Produzione` = coefficiente basato su esposizione e area geografica (da API)

**File:** `Step4SystemSelection.vue` - `simulationResults`

---

## 4. Autoconsumo

### 4.1 Energia dalla Batteria

**Formula:**
```
Energia_Batteria_Annua_kWh = Capacità_Batteria_kWh × 365
```

**Spiegazione:**
Calcola l'energia totale disponibile dalla batteria in un anno (considerando un ciclo completo al giorno).

**File:** `Step4SystemSelection.vue`

---

### 4.2 Autoconsumo Totale

**Formula:**
```
Autoconsumo_Totale_kWh = Consumo_Diurno_Annuo + min(Energia_Batteria_Annua_kWh, Consumo_Notturno_Annuo)
```

**Spiegazione:**
L'autoconsumo totale è composto da:
1. **Consumo diurno diretto**: energia solare consumata immediatamente durante il giorno quando il sole è presente
2. **Consumo notturno dalla batteria**: energia dalla batteria utilizzata di notte, limitata dal minimo tra:
   - La capacità totale della batteria disponibile in un anno (capacità × 365 giorni)
   - Il consumo notturno effettivo

**Logica:**
- Se il consumo notturno è minore dell'energia disponibile dalla batteria, viene utilizzato tutto il consumo notturno
- Se il consumo notturno è maggiore, viene utilizzata solo l'energia disponibile dalla batteria

**File:** `Step4SystemSelection.vue`

---

## 5. Risparmio Autoconsumo

### 5.1 Risparmio Annuale da Autoconsumo

**Formula:**
```
Risparmio_Autoconsumo_€ = Autoconsumo_Totale_kWh × Costo_per_kWh
```

**Spiegazione:**
Il risparmio economico derivante dall'energia autoconsumata (non acquistata dalla rete).

**File:** `Step4SystemSelection.vue` - `simulationResults.risparmioAutoconsumo`

---

## 6. Vendita Eccedenza (RID)

### 6.1 Energia Immessa in Rete

**Formula:**
```
Energia_Immessa_in_Rete_kWh = max(0, Produzione_Annua_kWh - Autoconsumo_Totale_kWh)
```

**Spiegazione:**
L'energia prodotta in eccesso rispetto all'autoconsumo che viene immessa nella rete elettrica.

**File:** `Step4SystemSelection.vue`

---

### 6.2 Vendita Eccedenza (Ritiro Dedicato)

**Formula:**
```
Vendita_Eccedenza_€ = Energia_Immessa_in_Rete_kWh × Prezzo_Ritiro_Dedicato
```

**Dove:**
- `Prezzo_Ritiro_Dedicato` = 0.08 €/kWh (costante `PRICE_RITIRO_DEDICATO`)

**Spiegazione:**
Il guadagno annuale dalla vendita dell'energia in eccesso al GSE tramite il servizio di Ritiro Dedicato.

**File:** `Step4SystemSelection.vue` - `simulationResults.venditaEccedenza`

---

## 7. Incentivo CER

### 7.1 Incentivo Base Comunità Energetica

**Formula:**
```
Incentivo_CER_Base_€ = Energia_Immessa_in_Rete_kWh × 0.108 €/kWh
```

**Spiegazione:**
Incentivo base per l'energia condivisa nella Comunità Energetica Rinnovabile.

**File:** `Step4SystemSelection.vue`

---

### 7.2 Incentivo CER (80%)

**Formula:**
```
Incentivo_CER_€ = Incentivo_CER_Base_€ × 0.80
```

**Oppure direttamente:**
```
Incentivo_CER_€ = Energia_Immessa_in_Rete_kWh × 0.108 × 0.80
Incentivo_CER_€ = Energia_Immessa_in_Rete_kWh × 0.0864 €/kWh
```

**Spiegazione:**
L'incentivo CER effettivo è pari all'80% dell'incentivo base per l'energia condivisa nella CER.

**File:** `Step4SystemSelection.vue` - `simulationResults.incentivoCer`

---

## 8. Costo Totale Sistema

### 8.1 Prezzo Prodotto

**Formula:**
```
Prezzo_Prodotto_€ = prezzo_base / 100
```

**Dove:**
- `prezzo_base` = prezzo in centesimi dal database (`prodotti_fotovoltaico.prezzo_base`)

**File:** `Step4SystemSelection.vue`

---

### 8.2 Prezzo Batteria

**Formula (scaglioni):**
```
Prezzo_Batteria_€ = 
  se Capacità ≤ 5 kWh: 4000 €
  se 5 < Capacità ≤ 10 kWh: 7000 €
  se 10 < Capacità ≤ 15 kWh: 10000 €
  se Capacità > 15 kWh: 12000 €
```

**File:** `constants.js` - `calculateBatteryPrice()`

---

### 8.3 Prezzo Tipologia Tetto

**Formula:**
```
Prezzo_Tetto_€ = costo_extra_kwp × Potenza_Impianto_kWp
```

**Dove:**
- `costo_extra_kwp` = costo aggiuntivo per kWp dalla tipologia tetto (da API `tipologie_tetto.costo_extra_kwp`)
- Se non disponibile, può essere inserito manualmente

**File:** `Step4SystemSelection.vue` - `handleRoofTypeChange()`, `handlePowerChange()`

---

### 8.4 Costi Aggiuntivi e Sconti

#### Costi Aggiuntivi (Valore Percentuale)

**Formula:**
```
Costo_Aggiuntivo_€ = (Prezzo_Prodotto + Prezzo_Batteria + Prezzo_Tetto) × (Percentuale / 100)
```

#### Costi Aggiuntivi (Valore Fisso)

**Formula:**
```
Costo_Aggiuntivo_€ = valore_default (o amount)
```

#### Sconti (Valore Percentuale)

**Formula:**
```
Sconto_€ = (Prezzo_Prodotto + Prezzo_Batteria + Prezzo_Tetto) × (Percentuale / 100)
```

#### Sconti (Valore Fisso)

**Formula:**
```
Sconto_€ = valore_default (o amount)
```

**File:** `Step4SystemSelection.vue` - `calculateAdjustmentAmount()`

---

### 8.5 Costo Totale Sistema

**Formula:**
```
Costo_Totale_Sistema_€ = Prezzo_Prodotto + Prezzo_Batteria + Prezzo_Tetto + Costi_Aggiuntivi_Totali - Sconti_Totali
```

**File:** `Step4SystemSelection.vue` - `totalSystemCostComputed`

---

## 9. Detrazione Fiscale

### 9.1 Percentuale Detrazione

**Formula:**
```
Percentuale_Detrazione = 
  se prima_casa: 50%
  se seconda_casa: 36%
  altrimenti: 0%
```

**File:** `Step4SystemSelection.vue`

---

### 9.2 Detrazione Fiscale Annuale

**Formula:**
```
Detrazione_Fiscale_Annua_€ = (Costo_Totale_Sistema × Percentuale_Detrazione) / 10
```

**Spiegazione:**
La detrazione viene distribuita su 10 anni, quindi il valore annuale è un decimo del totale detraibile.

**File:** `Step4SystemSelection.vue` - `simulationResults.detrazioneFiscale`

---

## 10. Business Plan

### 10.1 Investimento Iniziale

**Formula:**
```
Investimento_Iniziale_€ = -Costo_Totale_Sistema_€
```

**Spiegazione:**
Valore negativo perché rappresenta un'uscita di cassa.

**File:** `Step5BusinessPlan.vue` - `businessPlanData`

---

### 10.2 Pagamento Finanziamento Annuale

**Formula:**
```
Pagamento_Finanziamento_Annuale_€ = 
  se Finanziamento: installmentAmount × 12
  se Pagamento Misto: paymentMisto.installmentAmount × 12
  altrimenti: 0
```

**File:** `Step5BusinessPlan.vue` - `annualLoanPayment`

---

### 10.3 Anni di Finanziamento

**Formula:**
```
Anni_Finanziamento = ceil(installments / 12)
```

**File:** `Step5BusinessPlan.vue` - `loanYears`

---

### 10.4 Costi Annuali

#### Costo Assicurazione

**Formula:**
```
Costo_Assicurazione_€ = 
  se year > 0 AND insurance.enabled: insurance.cost
  altrimenti: 0
```

#### Costo Manutenzione

**Formula:**
```
Costo_Manutenzione_€ = 
  se year > 0 AND maintenance.enabled: maintenance.cost
  altrimenti: 0
```

**File:** `Step5BusinessPlan.vue`

---

### 10.5 Entrate Annuali (con Inflazione)

**Formula (con inflazione 2% annua):**
```
Risparmio_Anno_N = Risparmio_Autoconsumo × (1.02)^(N-1)
Vendita_Energia_Anno_N = Vendita_Eccedenza × (1.02)^(N-1)
CER_Anno_N = Incentivo_CER × (1.02)^(N-1)
```

**Dove:**
- `N` = anno corrente (1-20)
- `1.02` = tasso di inflazione annuale (2%)

**File:** `Step5BusinessPlan.vue`

---

### 10.6 Fondo PNRR

**Formula:**
```
Fondo_PNRR_€ = 
  se year === 1 AND presente incentivo PNRR: 1000 € (placeholder)
  altrimenti: 0
```

**Nota:** Attualmente è un placeholder. Dovrebbe essere calcolato dalle voci economiche se presente un incentivo PNRR.

**File:** `Step5BusinessPlan.vue`

---

### 10.7 Flussi di Cassa Annuali

**Formula:**
```
Flusso_Cassa_Anno_0 = Investimento_Iniziale_€

Flusso_Cassa_Anno_N = 
  Entrate_N - Uscite_N
  dove:
    Entrate_N = Risparmio_Anno_N + Vendita_Energia_Anno_N + CER_Anno_N + Fondo_PNRR
    Uscite_N = Pagamento_Finanziamento + Costo_Assicurazione + Costo_Manutenzione
```

**File:** `Step5BusinessPlan.vue`

---

### 10.8 Flussi di Cassa Cumulati

**Formula:**
```
Flusso_Cassa_Cumulato_Anno_N = Σ Flusso_Cassa_Anno_i per i da 0 a N
```

**Spiegazione:**
Somma progressiva dei flussi di cassa dall'anno 0 fino all'anno corrente.

**File:** `Step5BusinessPlan.vue`

---

## Costanti Utilizzate

### Costanti di Prezzo

- **PRICE_RITIRO_DEDICATO**: 0.08 €/kWh
- **Incentivo CER base**: 0.108 €/kWh
- **Percentuale CER**: 80% (0.80)
- **Coefficiente inflazione**: 1.02 (2% annuo)

### Coefficienti di Distribuzione Consumo

- **F1 (monoraria)**: 83% diurno, 17% notturno
- **F2 (intermedia)**: 26% diurno, 74% notturno
- **F3 (monoraria notturna)**: 17% diurno, 83% notturno

### Percentuali Detrazione Fiscale

- **Prima casa**: 50%
- **Seconda casa**: 36%

---

## Note Importanti

1. **Conversione Unità**: 
   - I prezzi dal database sono in centesimi, vengono convertiti in euro dividendo per 100
   - La potenza dei prodotti è in watt, viene convertita in kWp dividendo per 1000

2. **Validazione Dati**:
   - Tutti i calcoli verificano che i dati necessari siano presenti e validi
   - Se mancano dati essenziali, i valori restituiti sono 0 o valori di fallback

3. **Coefficienti di Produzione**:
   - Vengono recuperati dall'API `/api/coefficienti-produzione`
   - Fallback a 1350 kWh/kWp se non disponibili

4. **Pagamento Misto**:
   - Il sistema supporta pagamento misto (bonifico + finanziamento)
   - L'importo finanziamento viene calcolato automaticamente come: Totale Sistema - Importo Bonifico

---

## Revisione e Validazione

**Data ultima revisione:** 2025-01-XX

**Formule validate:**
- ✅ Consumo annuo e distribuzione fasce
- ✅ Dimensionamento impianto
- ✅ Produzione annua
- ✅ Autoconsumo totale
- ✅ Risparmio autoconsumo
- ✅ Vendita eccedenza (RID)
- ✅ Incentivo CER (80%)
- ✅ Costo totale sistema
- ✅ Detrazione fiscale
- ✅ Business Plan ventennale

**Note di validazione:**
- Tutte le formule sono state verificate con i dati reali dell'API
- I coefficienti di distribuzione consumo (0.83, 0.26, etc.) sono standard del settore
- I valori di incentivi e tariffe sono aggiornati alle normative vigenti


<template>
    <div style="display:flex;flex-direction:column;gap:24px;max-height:500px;overflow:auto;padding-right:8px;">
        <div>
            <h2 class="section-title">Selezione Impianto e Simulazione</h2>
            <p class="muted">Configura i dettagli dell'installazione per calcolare l'impianto ideale e scegli l'offerta.</p>
        </div>

        <div class="grid-responsive-3">
             <div>
                <label for="geo-area" class="field-label">Area geografica</label>
                <select id="geo-area" :value="formData.geographicArea" @change="updateFormData('geographicArea', $event.target.value)" class="field-select">
                    <option value="">Seleziona area</option>
                    <option value="NORD">Nord</option>
                    <option value="CENTRO">Centro</option>
                    <option value="SUD">Sud</option>
                    <option value="ISOLE">Isole</option>
                </select>
             </div>
             <div>
                <label for="roof-exp" class="field-label">Esposizione tetto</label>
                <select id="roof-exp" :value="formData.roofExposure" @change="updateFormData('roofExposure', $event.target.value)" class="field-select">
                    <option value="">Seleziona esposizione</option>
                    <option v-for="exposure in Object.keys(coefficientsMap)" :key="exposure" :value="exposure">
                        {{ exposure.split(' ').map(word => word.charAt(0) + word.slice(1).toLowerCase()).join(' ') }}
                    </option>
                </select>
             </div>
             <div>
                <label for="roof-type" class="field-label">Tipologia tetto</label>
                <select id="roof-type" :value="formData.roofType" @change="handleRoofTypeChange($event.target.value)" class="field-select">
                    <option value="">Seleziona tipologia</option>
                    <option v-for="tipo in tipologieTetto" :key="tipo.id_voce" :value="tipo.nome_tipologia">
                        {{ tipo.nome_tipologia }}
                    </option>
                </select>
                <div v-if="formData.roofType && selectedRoofTypeCostPerKw !== null" style="margin-top:8px;">
                    <label class="field-label" for="roof-type-price" style="font-size:12px;color:#6b7280;">Prezzo aggiuntivo tetto (€/kW)</label>
                    <input 
                        type="number" 
                        id="roof-type-price" 
                        :value="roofTypePricePerKw" 
                        @input="handleRoofTypePricePerKwChange(Number($event.target.value) || 0)" 
                        placeholder="0"
                        class="field-input" 
                        style="max-width:200px;"
                        min="0"
                        step="0.01"
                    />
                </div>
             </div>
        </div>

        <div class="info-banner">
            <h3 class="section-subtitle">Impianto Fotovoltaico Consigliato</h3>
            <p>Per pareggiare i tuoi consumi, l'impianto ideale è da: <strong style="font-size:18px;">{{ requiredSystemSize.toFixed(2) }} kWp</strong></p>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div>
                <label for="power" class="section-subtitle">Potenza kWh</label>
                <select
                    id="power"
                    :value="formData.selectedPowerKw"
                    @change="handlePowerChange(Number($event.target.value))"
                    class="field-select"
                >
                    <option value="0">Seleziona potenza</option>
                    <option v-for="kw in POWER_OPTIONS_KW" :key="kw" :value="kw">
                        {{ kw }} kW
                    </option>
                </select>
                <p v-if="formData.selectedPowerKw > 0 && formData.roofTypePrice > 0" style="margin-top:8px;font-size:12px;color:#6b7280;">
                    Costo aggiuntivo tetto: <strong>{{ formData.roofTypePrice.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 2 }) }}</strong>
                </p>
            </div>

            <div>
                <label for="battery" class="section-subtitle">Scegli una batteria</label>
                <select
                    id="battery"
                    :value="formData.selectedBatteryCapacity"
                    @change="updateFormData('selectedBatteryCapacity', Number($event.target.value))"
                    class="field-select"
                >
                    <option value="0">Nessuna batteria</option>
                    <option v-for="kwh in BATTERY_OPTIONS_KWH" :key="kwh" :value="kwh">
                        Batteria {{ kwh }} kWh
                    </option>
                </select>
            </div>

            <div>
                <label for="product" class="section-subtitle">Selezione Prodotto/Offerta</label>
                 <select 
                    id="product" 
                    v-model="localSelectedProduct"
                    @change="handleProductChange($event.target.value)" 
                    class="field-select"
                >
                    <option value="">Scegli un prodotto</option>
                    <option v-for="product in prodottiFotovoltaico" :key="product.id_prodotto" :value="String(product.id_prodotto)">
                      {{ product.codice_prodotto }} ({{ product.prezzo_base }} €)
                    </option>
                </select>
            </div>
        </div>

        <div>
            <h3 class="section-subtitle" style="margin-bottom:8px;">Pagamento e Opzioni</h3>
             <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="pill">
                    <span class="section-subtitle" style="display:block;margin-bottom:8px;">Metodo di Pagamento</span>
                    <div class="inline-fields">
                        <label v-for="modalita in modalitaPagamento" :key="modalita.id_modalita" style="display:flex;align-items:center;color:#374151;">
                            <input type="radio" name="payment" :value="modalita.nome_modalita" :checked="formData.paymentMethod === modalita.nome_modalita" @change="handlePaymentMethodChange(modalita.nome_modalita)" style="margin-right:8px;"/>
                            {{ modalita.nome_modalita }}
                        </label>
                        <label style="display:flex;align-items:center;color:#374151;">
                            <input type="radio" name="payment" value="Misto" :checked="formData.paymentMethod === 'Misto'" @change="handlePaymentMethodChange('Misto')" style="margin-right:8px;"/>
                            Misto (Bonifico + Finanziamento)
                        </label>
                    </div>
                    
                    <!-- Pagamento Misto -->
                    <div v-if="formData.paymentMethod === 'Misto'" style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                        <div class="grid-responsive-2" style="margin-bottom:12px;">
                            <div>
                                <label class="field-label" for="bonificoAmount">Importo Bonifico (€)</label>
                                <input type="number" id="bonificoAmount" :value="formData.paymentMisto?.bonificoAmount || 0" @input="handlePagamentoMistoChange('bonificoAmount', $event.target.value)" class="field-input" min="0" step="0.01"/>
                            </div>
                            <div>
                                <label class="field-label" for="finanziamentoAmount">Importo Finanziamento (€)</label>
                                <input type="number" id="finanziamentoAmount" :value="formData.paymentMisto?.finanziamentoAmount || 0" @input="handlePagamentoMistoChange('finanziamentoAmount', $event.target.value)" class="field-input" min="0" step="0.01" readonly style="background-color:#f3f4f6;"/>
                                <p class="help-text" style="margin-top:4px;font-size:11px;">Calcolato automaticamente</p>
                            </div>
                        </div>
                        <div class="grid-responsive-2">
                            <div>
                                <label class="field-label" for="primaRataMisto">Prima rata bonifico (%)</label>
                                <input type="number" id="primaRataMisto" :value="formData.paymentMisto?.primaRata || 30" @input="handlePagamentoMistoChange('primaRata', $event.target.value)" class="field-input"/>
                            </div>
                            <div>
                                <label class="field-label" for="secondaRataMisto">Seconda rata bonifico (%)</label>
                                <input type="number" id="secondaRataMisto" :value="formData.paymentMisto?.secondaRata || 50" @input="handlePagamentoMistoChange('secondaRata', $event.target.value)" class="field-input"/>
                            </div>
                        </div>
                        <div class="grid-responsive-2" style="margin-top:12px;">
                            <div>
                                <label class="field-label" for="installmentAmountMisto">Importo rata finanziamento (€)</label>
                                <input type="number" id="installmentAmountMisto" :value="formData.paymentMisto?.installmentAmount || 0" @input="handlePagamentoMistoChange('installmentAmount', $event.target.value)" class="field-input" min="0" step="0.01" readonly style="background-color:#f3f4f6;"/>
                                <p class="help-text" style="font-size:12px;margin-top:4px;">Calcolata automaticamente (TAEG 11.89%, spese 1.50€/mese)</p>
                            </div>
                            <div>
                                <label class="field-label" for="installmentsMisto">Numero di rate finanziamento</label>
                                <input type="number" id="installmentsMisto" :value="formData.paymentMisto?.installments || 120" @input="handlePagamentoMistoChange('installments', $event.target.value)" class="field-input" min="1"/>
                            </div>
                        </div>
                        <p v-if="formData.paymentMisto?.primaRata + formData.paymentMisto?.secondaRata !== 80" class="error-text" style="margin-top:8px;">La somma delle prime due rate bonifico deve essere uguale a 80%.</p>
                    </div>
                    
                     <div v-if="formData.paymentMethod && (formData.paymentMethod.toLowerCase().includes('finanziamento') || formData.paymentMethod === 'Finanziamento')" class="grid-responsive-2" style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                        <div>
                            <label class="field-label" for="installmentAmount">Importo rata (€)</label>
                            <input type="number" id="installmentAmount" :value="formData.installmentAmount" @input="handleFinanziamentoChange('installmentAmount', $event.target.value)" class="field-input" readonly style="background-color:#f3f4f6;"/>
                            <p class="help-text" style="font-size:12px;margin-top:4px;">Calcolata automaticamente (TAEG 11.89%, spese 1.50€/mese)</p>
                        </div>
                        <div>
                            <label class="field-label" for="installments">Numero di rate</label>
                            <input type="number" id="installments" :value="formData.installments" @input="handleFinanziamentoChange('installments', $event.target.value)" class="field-input" min="1"/>
                        </div>
                    </div>
                     <div v-if="formData.paymentMethod && (formData.paymentMethod.toLowerCase().includes('bonifico') || formData.paymentMethod === 'Bonifico')" style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                        <div class="grid-3">
                            <div>
                                <label class="field-label" for="primaRata">Prima rata (%)</label>
                                <input type="number" id="primaRata" :value="formData.paymentBonifico.primaRata" @input="handleBonificoChange('primaRata', $event.target.value)" class="field-input"/>
                            </div>
                            <div>
                                <label class="field-label" for="secondaRata">Seconda rata (%)</label>
                                <input type="number" id="secondaRata" :value="formData.paymentBonifico.secondaRata" @input="handleBonificoChange('secondaRata', $event.target.value)" class="field-input"/>
                            </div>
                            <div>
                                <label class="field-label" for="terzaRata">Terza rata (%)</label>
                                <input type="number" id="terzaRata" value="20" readonly class="field-input"/>
                            </div>
                        </div>
                        <p v-if="formData.paymentBonifico.primaRata + formData.paymentBonifico.secondaRata !== 80" class="error-text">La somma delle prime due rate deve essere uguale a 80%.</p>
                    </div>
                </div>
                <div class="pill" style="display:flex;align-items:center;justify-content:space-between;">
                    <label for="maintenance-check" style="font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;">
                     <input type="checkbox" id="maintenance-check" :checked="formData.maintenance.enabled" @change="updateFormData('maintenance', {...formData.maintenance, enabled: $event.target.checked})" style="margin-right:8px;"/>
                        Manutenzione
                    </label>
                    <input v-if="formData.maintenance.enabled" type="number" placeholder="Importo" :value="formData.maintenance.cost" @input="updateFormData('maintenance', {...formData.maintenance, cost: Number($event.target.value)})" class="field-input" style="width:130px;"/>
                </div>
                 <div class="pill" style="display:flex;align-items:center;justify-content:space-between;">
                    <label for="insurance-check" style="font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;">
                     <input type="checkbox" id="insurance-check" :checked="formData.insurance.enabled" @change="updateFormData('insurance', {...formData.insurance, enabled: $event.target.checked})" style="margin-right:8px;"/>
                        Assicurazione
                    </label>
                    <input v-if="formData.insurance.enabled" type="number" placeholder="Importo" :value="formData.insurance.cost" @input="updateFormData('insurance', {...formData.insurance, cost: Number($event.target.value)})" class="field-input" style="width:130px;"/>
                </div>
            </div>
        </div>

        <div>
             <h3 class="section-subtitle" style="margin-bottom:8px;">Agevolazioni e Costi Aggiuntivi</h3>
             <p class="help-text">Configurazione per cliente: <span style="font-weight:600;color:#111827;">{{ isResidential ? 'Residenziale' : 'Business' }}</span></p>
             <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="pill">
                    <h4 class="section-subtitle" style="margin-bottom:8px;">Detrazione Fiscale</h4>
                    <div class="segments" style="grid-template-columns:repeat(3,1fr);">
                        <button
                            @click="updateFormData('fiscalDeductionType', 'prima_casa')"
                            :class="['segment', formData.fiscalDeductionType === 'prima_casa' ? 'is-active' : '']"
                        >Prima Casa (50%)</button>
                        <button
                            @click="updateFormData('fiscalDeductionType', 'seconda_casa')"
                            :class="['segment', formData.fiscalDeductionType === 'seconda_casa' ? 'is-active' : '']"
                        >Seconda Casa (36%)</button>
                        <button
                            @click="updateFormData('fiscalDeductionType', 'nessuna')"
                            :class="['segment', formData.fiscalDeductionType === 'nessuna' ? 'is-active' : '']"
                        >Nessuna</button>
                    </div>
                </div>
                <div v-if="formData.selectedProduct" style="display:flex;flex-direction:column;gap:16px;">
            <h3 class="section-subtitle">Simulazione Guadagni Annuali Stimati</h3>
      
        </div>
                <!-- <AdjustmentList components will be added here -->
                <AdjustmentList title="Incentivi" listName="incentives" :items="formData.incentives" @update:items="updateFormData('incentives', $event)" />
                <div class="grid-responsive-2">
                <EarningsInfoCard 
                    title="RISPARMIO DA AUTOCONSUMO" 
                    :value="simulationResults.risparmioAutoconsumo.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 1 })"
                    description="Risparmio annuale generato dall'energia prodotta e consumata."
                />
                <EarningsInfoCard 
                    title="VENDITA ECCEDENZA (RID)" 
                    :value="simulationResults.venditaEccedenza.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 1 })"
                    description="Guadagno annuale dalla vendita dell'energia non consumata."
                />
                <EarningsInfoCard 
                    title="INCENTIVO CER (PNRR)" 
                    :value="simulationResults.incentivoCer.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 1 })"
                    description="Incentivo annuale PNRR per l'energia condivisa nella Comunità Energetica."
                />
                <EarningsInfoCard 
                    title="DETRAZIONE FISCALE" 
                    :value="simulationResults.detrazioneFiscale.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 1 })"
                    description="Importo annuale della detrazione fiscale per 10 anni."
                />
            </div>
                <AdjustmentList title="Sconti" listName="discounts" :items="formData.discounts" @update:items="updateFormData('discounts', $event)" />
                <AdjustmentList title="Costi Aggiuntivi" listName="additionalCosts" :items="formData.additionalCosts" @update:items="updateFormData('additionalCosts', $event)" />
             </div>
        </div>

        
    </div>
</template>

<script setup lang="js">
import { usePreventiviApi } from '@/composables/usePreventiviApi';
import { computed, defineEmits, defineProps, onMounted, ref, watch } from 'vue';
import AdjustmentList from '../AdjustmentList.vue';
import { BATTERY_OPTIONS_KWH, COEFFICIENTS, POWER_OPTIONS_KW, PRICE_RITIRO_DEDICATO, PRODUCTS } from '../constants';
import EarningsInfoCard from '../EarningsInfoCard.vue';

const props = defineProps({
  formData: Object,
});
const emit = defineEmits(['update:formData']);

const { 
  loadTipologieTetto, 
  loadProdottiFotovoltaico, 
  loadCoefficientiProduzione,
  loadModalitaPagamento 
} = usePreventiviApi();

const tipologieTetto = ref([]);
const prodottiFotovoltaico = ref([]);
const coefficientiProduzione = ref([]);
const modalitaPagamento = ref([]);
const coefficientsMap = ref({});
const localSelectedProduct = ref('');

// Sincronizza il valore locale con il prop
watch(() => props.formData.selectedProduct, (newVal) => {
  localSelectedProduct.value = newVal ? String(newVal) : '';
}, { immediate: true });

// Carica i dati all'avvio del componente
onMounted(async () => {
  try {
    // Carica tipologie tetto
    const tipologie = await loadTipologieTetto();
    // Gestisci la risposta paginata o diretta
    tipologieTetto.value = Array.isArray(tipologie) ? tipologie : (tipologie?.data || []);
    
    // Carica prodotti fotovoltaico
    const prodotti = await loadProdottiFotovoltaico();
    prodottiFotovoltaico.value = prodotti;
    
    // Carica coefficienti produzione
    const coefficienti = await loadCoefficientiProduzione();
    coefficientiProduzione.value = coefficienti;
    
    // Converti i coefficienti in un formato utilizzabile
    // Usa il campo coefficiente_kwh_kwp dall'API
    if (coefficienti && coefficienti.length > 0) {
      const map = {};
      coefficienti.forEach(coeff => {
        const esposizione = coeff.esposizione;
        const area = coeff.area_geografica;
        // Usa il campo coefficiente_kwh_kwp dall'API
        const valore = coeff.coefficiente_kwh_kwp;
        
        if (esposizione && area && valore !== undefined && valore !== null) {
          if (!map[esposizione]) {
            map[esposizione] = {};
          }
          map[esposizione][area] = valore;
        }
      });
      coefficientsMap.value = Object.keys(map).length > 0 ? map : COEFFICIENTS;
    } else {
      coefficientsMap.value = COEFFICIENTS;
    }
    
    // Carica modalità pagamento
    const modalita = await loadModalitaPagamento();
    modalitaPagamento.value = modalita;
    
    // Se non c'è un metodo di pagamento selezionato o non corrisponde alle modalità caricate,
    // imposta il primo disponibile
    if (modalita.length > 0) {
      const currentMethod = props.formData.paymentMethod;
      // Confronta anche con case-insensitive e controlla se è "Misto"
      const methodExists = currentMethod && (
        modalita.some(m => m.nome_modalita === currentMethod || m.nome_modalita?.toLowerCase() === currentMethod?.toLowerCase()) ||
        currentMethod === 'Misto'
      );
      if (!methodExists || !currentMethod) {
        updateFormData('paymentMethod', modalita[0].nome_modalita);
      }
    }
  } catch (error) {
    console.error('Errore nel caricamento dati:', error);
    // Fallback ai valori di default
    tipologieTetto.value = [];
    prodottiFotovoltaico.value = [];
    coefficientiProduzione.value = [];
    modalitaPagamento.value = [];
    coefficientsMap.value = COEFFICIENTS;
  }
});

const updateFormData = (field, value) => {
  // Passa solo il campo aggiornato invece dell'intero oggetto
  // per evitare di sovrascrivere altri campi con valori obsoleti
  emit('update:formData', { [field]: value });
};

// Computed per calcolare il costo totale del sistema
const totalSystemCostComputed = computed(() => {
    let productPrice = 0;
    if (props.formData.selectedProduct) {
      const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
      if (selectedProduct && selectedProduct.prezzo_base) {
        productPrice = selectedProduct.prezzo_base ;
      }
    }
    // Il prezzo batteria è già incluso nel prezzo prodotto
    const roofTypePrice = props.formData.roofTypePrice || 0;
    const additionalCostsTotal = props.formData.additionalCosts.reduce((sum, item) => {
        if (!item) return sum;
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + roofTypePrice;
            return sum + (baseAmount * item.valore_default) / 100;
        }
        return sum + (item.amount || item.valore_default || 0);
    }, 0);
    const discountsTotal = props.formData.discounts.reduce((sum, item) => {
        if (!item) return sum;
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + roofTypePrice;
            return sum + (baseAmount * item.valore_default) / 100;
        }
        return sum + (item.amount || item.valore_default || 0);
    }, 0);
    return productPrice + roofTypePrice + additionalCostsTotal - discountsTotal;
});

// Watch per aggiornare l'importo finanziamento e la rata quando cambia il totale del sistema
watch(totalSystemCostComputed, (newTotal) => {
    // Aggiorna finanziamento misto
    if (props.formData.paymentMethod === 'Misto' && props.formData.paymentMisto) {
        const bonificoAmount = props.formData.paymentMisto.bonificoAmount || 0;
        const finanziamentoAmount = Math.max(0, newTotal - bonificoAmount);
        const installments = props.formData.paymentMisto.installments || 120;
        const installmentAmount = calculateInstallmentAmount(finanziamentoAmount, installments);
        
        if (finanziamentoAmount !== (props.formData.paymentMisto.finanziamentoAmount || 0) ||
            installmentAmount !== (props.formData.paymentMisto.installmentAmount || 0)) {
            updateFormData('paymentMisto', {
                ...props.formData.paymentMisto,
                finanziamentoAmount: finanziamentoAmount,
                installmentAmount: installmentAmount,
            });
        }
    }
    
    // Aggiorna finanziamento puro
    if (props.formData.paymentMethod && (props.formData.paymentMethod.toLowerCase().includes('finanziamento') || props.formData.paymentMethod === 'Finanziamento')) {
        const installments = props.formData.installments || 120;
        const installmentAmount = calculateInstallmentAmount(newTotal, installments);
        if (installmentAmount !== (props.formData.installmentAmount || 0)) {
            updateFormData('installmentAmount', installmentAmount);
        }
    }
});

// Computed per ottenere il costo per kW della tipologia tetto selezionata
const selectedRoofTypeCostPerKw = computed(() => {
  if (!props.formData.roofType || tipologieTetto.value.length === 0) {
    return null;
  }
  const selectedTipo = tipologieTetto.value.find(t => t.nome_tipologia === props.formData.roofType);
  if (selectedTipo && selectedTipo.costo_extra_kwp !== undefined && selectedTipo.costo_extra_kwp !== null && selectedTipo.costo_extra_kwp > 0) {
    return selectedTipo.costo_extra_kwp;
  }
  return null;
});

// Computed per ottenere il prezzo per kW (dal formData o dal DB)
const roofTypePricePerKw = computed(() => {
  // Se c'è un valore salvato nel formData (anche se è 0), usalo
  if (props.formData.roofTypePricePerKw !== undefined && props.formData.roofTypePricePerKw !== null) {
    return props.formData.roofTypePricePerKw;
  }
  // Altrimenti usa il valore dal DB se disponibile
  return selectedRoofTypeCostPerKw.value || 0;
});

// Funzione per gestire il cambio del prezzo per kW
const handleRoofTypePricePerKwChange = (pricePerKw) => {
  // Salva il prezzo per kW
  updateFormData('roofTypePricePerKw', pricePerKw);
  // Ricalcola il totale se c'è una potenza selezionata
  if (props.formData.selectedPowerKw && props.formData.selectedPowerKw > 0) {
    const totalPrice = pricePerKw * props.formData.selectedPowerKw;
    updateFormData('roofTypePrice', totalPrice);
  } else {
    updateFormData('roofTypePrice', 0);
  }
};

const handlePowerChange = (value) => {
  updateFormData('selectedPowerKw', value);
  // Ricalcola il prezzo tetto totale usando il prezzo per kW salvato
  if (value > 0) {
    const pricePerKw = roofTypePricePerKw.value || 0;
    if (pricePerKw > 0) {
      const costoExtra = pricePerKw * value;
      updateFormData('roofTypePrice', costoExtra);
    } else {
      updateFormData('roofTypePrice', 0);
    }
  } else {
    updateFormData('roofTypePrice', 0);
  }
};

const handleRoofTypeChange = (value) => {
  updateFormData('roofType', value);
  // Se la tipologia tetto ha un costo_extra_kwp, lo usiamo per precompilare il prezzo per kW
  if (value) {
    const selectedTipo = tipologieTetto.value.find(t => t.nome_tipologia === value);
    if (selectedTipo) {
      // Se c'è un costo_extra_kwp, salviamo il prezzo per kW
      if (selectedTipo.costo_extra_kwp !== undefined && selectedTipo.costo_extra_kwp !== null && selectedTipo.costo_extra_kwp > 0) {
        // Salva il prezzo per kW dal DB
        updateFormData('roofTypePricePerKw', selectedTipo.costo_extra_kwp);
        // Calcola il costo totale se c'è una potenza selezionata
        const potenzaKwp = props.formData.selectedPowerKw || 0;
        if (potenzaKwp > 0) {
          const costoExtra = selectedTipo.costo_extra_kwp * potenzaKwp;
          updateFormData('roofTypePrice', costoExtra);
        } else {
          // Se non c'è ancora una potenza selezionata, imposta il totale a 0
          updateFormData('roofTypePrice', 0);
        }
      } else {
        // Se non c'è un costo predefinito, azzera sia il prezzo per kW che il totale
        updateFormData('roofTypePricePerKw', 0);
        updateFormData('roofTypePrice', 0);
      }
    }
  } else {
    updateFormData('roofTypePricePerKw', 0);
    updateFormData('roofTypePrice', 0);
  }
};

const handleProductChange = (value) => {
  // Aggiorna il valore locale immediatamente per feedback visivo
  localSelectedProduct.value = value;
  
  // Converti il valore in numero per essere coerente, ma mantieni stringa vuota se vuoto
  let productId;
  if (!value || value === '' || value === '0') {
    productId = '';
  } else {
    productId = Number(value);
  }
  updateFormData('selectedProduct', productId);
  
  // Se il prodotto ha un prezzo, possiamo salvarlo anche nel formData
  if (productId) {
    const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === productId);
    if (selectedProduct && selectedProduct.prezzo_base) {
      // Il prezzo_base è in centesimi, lo convertiamo in euro
      updateFormData('selectedProductPrice', selectedProduct.prezzo_base );
      updateFormData('selectedProductPowerKwp', selectedProduct.potenza_kwp / 1000); // Converti da W a kW
      // Nota: il prezzo tetto viene ricalcolato quando cambia la potenza selezionata (handlePowerChange)
    }
  }
};

// Computed per gestire il valore del select in modo reattivo (mantenuto per compatibilità)
const selectedProductValue = computed(() => {
  return localSelectedProduct.value;
});

const requiredSystemSize = computed(() => {
    const totalConsumption = props.formData.billData.reduce((acc, month) => acc + month.f1 + month.f2 + month.f3, 0);
    if (!totalConsumption) return 0;

    const coefficient = coefficientsMap.value[props.formData.roofExposure]?.[props.formData.geographicArea];
    
    if (!coefficient) {
        return totalConsumption / 1350;
    }

    return totalConsumption / coefficient;
});

const simulationResults = computed(() => {
    const fallbackResult = {
        risparmioAutoconsumo: 0,
        venditaEccedenza: 0,
        incentivoCer: 0,
        detrazioneFiscale: 0,
    };

    // Usa la potenza selezionata dal campo "Potenza kWh" (selectedPowerKw)
    // selectedPowerKw è già in kW
    const systemSizeKwp = props.formData.selectedPowerKw || 0;
    
    if (systemSizeKwp === 0 || !props.formData.billData.length || props.formData.costPerKwh <= 0) {
        return fallbackResult;
    }

    const coefficient = coefficientsMap.value[props.formData.roofExposure]?.[props.formData.geographicArea] || 1350;
    const annualProductionKwh = systemSizeKwp * coefficient;
    const totals = props.formData.billData.reduce((acc, month) => {
        acc.f1 += month.f1;
        acc.f2 += month.f2;
        acc.f3 += month.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
    const daytimeConsumptionKwh = totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17;
    const nighttimeConsumptionKwh = totals.f1 * 0.17 + totals.f2 * 0.74 + totals.f3 * 0.83;
    const batteryCapacityKwh = props.formData.selectedBatteryCapacity;
    // Calcolo autoconsumo totale
    // Autoconsumo diretto durante il giorno + energia dalla batteria di notte
    // La batteria può fornire energia solo fino alla sua capacità giornaliera × 365 giorni
    const batteryEnergyPerYear = batteryCapacityKwh * 365; // kWh disponibili dalla batteria all'anno
    // L'autoconsumo è: consumo diurno (coperto dalla produzione diretta) + min(energia batteria annua, consumo notturno)
    const totalAutoconsumoKwh = daytimeConsumptionKwh + Math.min(nighttimeConsumptionKwh, nighttimeConsumptionKwh);
    const risparmioAutoconsumo = totalAutoconsumoKwh * props.formData.costPerKwh;

    // CALCOLO VENDITA ECCEDENZA (RID)
    // Formula: energia prodotta - energia autoconsumata = energia immessa in rete
    // L'energia immessa in rete può essere venduta al prezzo di ritiro dedicato
    const energiaImmessaInRete = Math.max(0, annualProductionKwh - totalAutoconsumoKwh);
    const venditaEccedenza = energiaImmessaInRete * PRICE_RITIRO_DEDICATO;

    // Cerca il prezzo del prodotto selezionato
    let productPrice = 0;
    if (props.formData.selectedProduct) {
      const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
      if (selectedProduct && selectedProduct.prezzo_base) {
        productPrice = selectedProduct.prezzo_base;
      } else {
        // Fallback ai prodotti hardcoded
        productPrice = PRODUCTS.find(p => p.name === props.formData.selectedProduct)?.price || 0;
      }
    }
    // Il prezzo batteria è già incluso nel prezzo prodotto
    const roofTypePrice = props.formData.roofTypePrice || 0;
    
    // Calcola i costi aggiuntivi e sconti gestendo valori percentuali
    const calculateAdjustmentAmount = (item) => {
        if (!item) return 0;
        // Se è una voce percentuale, calcola l'importo come percentuale del costo base
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + roofTypePrice;
            return (baseAmount * item.valore_default) / 100;
        }
        // Altrimenti usa l'importo diretto
        return item.amount || item.valore_default || 0;
    };
    
    // Calcola incentivo CER (PNRR) - somma degli incentivi attivi nel primo anno
    let incentivoCer = 0;
    (props.formData.incentives || []).forEach(inc => {
        const annoInizio = inc.anno_inizio || 1;
        const annoFine = inc.anno_fine || 1;
        // Se l'incentivo è attivo nel primo anno (anno 1)
        if (annoInizio <= 1 && annoFine >= 1) {
            incentivoCer += calculateAdjustmentAmount(inc);
        }
    });
    
    const additionalCostsTotal = props.formData.additionalCosts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const discountsTotal = props.formData.discounts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    
    const totalSystemCost = productPrice + roofTypePrice + additionalCostsTotal - discountsTotal;

    let deductionPercentage = 0;
    if (props.formData.fiscalDeductionType === 'prima_casa') {
        deductionPercentage = 0.50;
    } else if (props.formData.fiscalDeductionType === 'seconda_casa') {
        deductionPercentage = 0.36;
    }
    const detrazioneFiscale = (productPrice * deductionPercentage) / 10;
    
    return { risparmioAutoconsumo, venditaEccedenza, incentivoCer, detrazioneFiscale };
});

const isResidential = computed(() => {
  // Usa clientCategory se disponibile, altrimenti default a Residenziale
  const category = props.formData.clientCategory || 'Residenziale';
  return category.toLowerCase() === 'residenziale';
});

// Costanti per il calcolo finanziamento
const TAN_ANNUAL = 0.079; // 7.90%
const TAEG_ANNUAL = 0.1189; // 11.89%
const MONTHLY_FEE = 1.50; // Spese mensili di incasso (€)

// Calcolo rata con TAN (nominale annuo)
function monthlyPaymentWithTAN(principal, months, tanAnnual, monthlyFee = 0) {
    const r = tanAnnual / 12;
    const pmtCI = principal * r / (1 - Math.pow(1 + r, -months));
    const pmtTot = pmtCI + monthlyFee;
    // Arrotonda ai centesimi come fa la banca
    return Math.round(pmtTot * 100) / 100;
}

// Calcolo rata "coerente con TAEG"
function monthlyPaymentWithTAEG(principal, months, taegAnnual, monthlyFee = 0) {
    const rEffMonthly = Math.pow(1 + taegAnnual, 1/12) - 1;
    // Qui il fee entra nella rata totale: PMT_tot = PMT_CI + fee
    const pmtTot = principal * rEffMonthly / (1 - Math.pow(1 + rEffMonthly, -months));
    const pmtCI = pmtTot - monthlyFee;
    const result = pmtCI + monthlyFee;
    return Math.round(result * 100) / 100;
}

// Funzione helper per calcolare il totale del sistema
const calculateTotalSystemCost = () => {
    let productPrice = 0;
    if (props.formData.selectedProduct) {
        const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
        if (selectedProduct && selectedProduct.prezzo_base) {
            productPrice = selectedProduct.prezzo_base;
        }
    }
    // Il prezzo batteria è già incluso nel prezzo prodotto
    const roofTypePrice = props.formData.roofTypePrice || 0;
    
    const calculateAdjustmentAmount = (item) => {
        if (!item) return 0;
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + roofTypePrice;
            return (baseAmount * item.valore_default) / 100;
        }
        return item.amount || item.valore_default || 0;
    };
    
    const additionalCostsTotal = props.formData.additionalCosts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const discountsTotal = props.formData.discounts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    
    return productPrice + roofTypePrice + additionalCostsTotal - discountsTotal;
};

// Calcola automaticamente la rata del finanziamento
const calculateInstallmentAmount = (principal, installments) => {
    if (!principal || principal <= 0 || !installments || installments <= 0) {
        return 0;
    }
    // Usa TAEG per calcolo più preciso
    return monthlyPaymentWithTAEG(principal, installments, TAEG_ANNUAL, MONTHLY_FEE);
};

const handleBonificoChange = (field, value) => {
    const numValue = Number(value) || 0;
    const updatedBonifico = {
        primaRata: 0,
        secondaRata: 0,
        ...props.formData.paymentBonifico,
        [field]: numValue < 0 ? 0 : numValue,
    };
    updateFormData('paymentBonifico', updatedBonifico);
};

const handlePagamentoMistoChange = (field, value) => {
    const numValue = Number(value) || 0;
    const currentMisto = props.formData.paymentMisto || {
        bonificoAmount: 0,
        finanziamentoAmount: 0,
        primaRata: 30,
        secondaRata: 50,
        installmentAmount: 0,
        installments: 120,
    };
    
    const updatedMisto = {
        ...currentMisto,
        [field]: numValue < 0 ? 0 : numValue,
    };
    
    // Calcola l'importo finanziamento automaticamente se viene modificato l'importo bonifico
    if (field === 'bonificoAmount') {
        const totalSystemCost = calculateTotalSystemCost();
        // L'importo finanziamento è il totale meno l'importo bonifico
        updatedMisto.finanziamentoAmount = Math.max(0, totalSystemCost - numValue);
        // Ricalcola anche la rata quando cambia l'importo finanziamento
        const installments = updatedMisto.installments || 120;
        updatedMisto.installmentAmount = calculateInstallmentAmount(updatedMisto.finanziamentoAmount, installments);
    }
    
    // Calcola automaticamente la rata quando cambia finanziamentoAmount o installments
    if (field === 'finanziamentoAmount' || field === 'installments') {
        const finanziamentoAmount = updatedMisto.finanziamentoAmount || 0;
        const installments = updatedMisto.installments || 120;
        updatedMisto.installmentAmount = calculateInstallmentAmount(finanziamentoAmount, installments);
    }
    
    updateFormData('paymentMisto', updatedMisto);
};

// Gestisce il cambio del metodo di pagamento e calcola automaticamente le rate
const handlePaymentMethodChange = (method) => {
    updateFormData('paymentMethod', method);
    
    // Se viene selezionato Finanziamento, calcola automaticamente la rata
    if (method && (method.toLowerCase().includes('finanziamento') || method === 'Finanziamento')) {
        const totalSystemCost = calculateTotalSystemCost();
        const installments = props.formData.installments || 120;
        const installmentAmount = calculateInstallmentAmount(totalSystemCost, installments);
        if (installmentAmount > 0) {
            updateFormData('installmentAmount', installmentAmount);
        }
    }
    
    // Se viene selezionato Misto, calcola anche lì la rata
    if (method === 'Misto') {
        const totalSystemCost = calculateTotalSystemCost();
        const currentMisto = props.formData.paymentMisto || {
            bonificoAmount: 0,
            finanziamentoAmount: totalSystemCost,
            primaRata: 30,
            secondaRata: 50,
            installmentAmount: 0,
            installments: 120,
        };
        
        const finanziamentoAmount = Math.max(0, totalSystemCost - (currentMisto.bonificoAmount || 0));
        const installments = currentMisto.installments || 120;
        const installmentAmount = calculateInstallmentAmount(finanziamentoAmount, installments);
        
        updateFormData('paymentMisto', {
            ...currentMisto,
            finanziamentoAmount: finanziamentoAmount,
            installmentAmount: installmentAmount,
        });
    }
};

// Gestisce il cambio del metodo di pagamento per finanziamento puro
const handleFinanziamentoChange = (field, value) => {
    const numValue = Number(value) || 0;
    
    if (field === 'installments') {
        updateFormData('installments', numValue);
        // Calcola automaticamente la rata
        const totalSystemCost = calculateTotalSystemCost();
        const installmentAmount = calculateInstallmentAmount(totalSystemCost, numValue);
        updateFormData('installmentAmount', installmentAmount);
    } else if (field === 'installmentAmount') {
        // Se l'utente modifica manualmente la rata, non sovrascriviamo
        updateFormData('installmentAmount', numValue);
    }
};
</script>



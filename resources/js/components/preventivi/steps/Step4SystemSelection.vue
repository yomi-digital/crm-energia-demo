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
                <select id="roof-type" :value="formData.roofType" @change="updateFormData('roofType', $event.target.value)" class="field-select">
                    <option value="">Seleziona tipologia</option>
                    <option v-for="tipo in tipologieTetto" :key="tipo.id_voce" :value="tipo.nome_tipologia">
                        {{ tipo.nome_tipologia }}
                    </option>
                </select>
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
                    @change="updateFormData('selectedPowerKw', Number($event.target.value))"
                    class="field-select"
                >
                    <option value="0">Seleziona potenza</option>
                    <option v-for="kw in POWER_OPTIONS_KW" :key="kw" :value="kw">
                        {{ kw }} kW
                    </option>
                </select>
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
                      {{ product.codice_prodotto }}
                    </option>
                </select>
            </div>
        </div>

        <div v-if="formData.selectedProduct" style="display:flex;flex-direction:column;gap:16px;">
            <h3 class="section-subtitle">Simulazione Guadagni Annuali Stimati</h3>
            <div class="grid-responsive-2">
                <EarningsInfoCard 
                    title="RISPARMIO DA AUTOCONSUMO" 
                    :value="simulationResults.risparmioAutoconsumo.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })"
                    description="Risparmio annuale generato dall'energia prodotta e consumata."
                />
                <EarningsInfoCard 
                    title="VENDITA ECCEDENZA (RID)" 
                    :value="simulationResults.venditaEccedenza.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })"
                    description="Guadagno annuale dalla vendita dell'energia non consumata."
                />
                <EarningsInfoCard 
                    title="INCENTIVO CER" 
                    :value="simulationResults.incentivoCer.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })"
                    description="Incentivo annuale per l'energia condivisa nella Comunità Energetica."
                />
                <EarningsInfoCard 
                    title="DETRAZIONE FISCALE" 
                    :value="simulationResults.detrazioneFiscale.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })"
                    description="Importo annuale della detrazione fiscale per 10 anni."
                />
            </div>
        </div>

        <div>
            <h3 class="section-subtitle" style="margin-bottom:8px;">Pagamento e Opzioni</h3>
             <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="pill">
                    <span class="section-subtitle" style="display:block;margin-bottom:8px;">Metodo di Pagamento</span>
                    <div class="inline-fields">
                        <label v-for="modalita in modalitaPagamento" :key="modalita.id_modalita" style="display:flex;align-items:center;color:#374151;">
                            <input type="radio" name="payment" :value="modalita.nome_modalita" :checked="formData.paymentMethod === modalita.nome_modalita" @change="updateFormData('paymentMethod', modalita.nome_modalita)" style="margin-right:8px;"/>
                            {{ modalita.nome_modalita }}
                        </label>
                    </div>
                     <div v-if="formData.paymentMethod && (formData.paymentMethod.toLowerCase().includes('finanziamento') || formData.paymentMethod === 'Finanziamento')" class="grid-responsive-2" style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                        <div>
                            <label class="field-label" for="installmentAmount">Importo rata (€)</label>
                            <input type="number" id="installmentAmount" :value="formData.installmentAmount" @input="updateFormData('installmentAmount', Number($event.target.value))" class="field-input"/>
                        </div>
                        <div>
                            <label class="field-label" for="installments">Numero di rate</label>
                            <input type="number" id="installments" :value="formData.installments" @input="updateFormData('installments', Number($event.target.value))" class="field-input"/>
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
                <!-- <AdjustmentList components will be added here -->
                <AdjustmentList title="Incentivi" listName="incentives" :items="formData.incentives" @update:items="updateFormData('incentives', $event)" />
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
import { BATTERY_OPTIONS_KWH, calculateBatteryPrice, COEFFICIENTS, POWER_OPTIONS_KW, PRICE_RITIRO_DEDICATO, PRODUCTS } from '../constants';
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
    tipologieTetto.value = tipologie;
    
    // Carica prodotti fotovoltaico
    const prodotti = await loadProdottiFotovoltaico();
    prodottiFotovoltaico.value = prodotti;
    
    // Carica coefficienti produzione
    const coefficienti = await loadCoefficientiProduzione();
    coefficientiProduzione.value = coefficienti;
    
    // Converti i coefficienti in un formato utilizzabile
    if (coefficienti && coefficienti.length > 0) {
      const map = {};
      coefficienti.forEach(coeff => {
        const esposizione = coeff.esposizione;
        const area = coeff.area_geografica;
        const valore = coeff.coefficiente_kwh_kwp;
        
        if (esposizione && area && valore) {
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
      const methodExists = modalita.some(m => m.nome_modalita === currentMethod);
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
  console.log('updateFormData chiamato:', { field, value });
  emit('update:formData', { [field]: value });
};

const handleProductChange = (value) => {
  console.log('handleProductChange chiamato con valore:', value, typeof value);
  // Aggiorna il valore locale immediatamente per feedback visivo
  localSelectedProduct.value = value;
  
  // Converti il valore in numero per essere coerente, ma mantieni stringa vuota se vuoto
  let productId;
  if (!value || value === '' || value === '0') {
    productId = '';
  } else {
    productId = Number(value);
  }
  console.log('ProductId convertito:', productId, typeof productId);
  updateFormData('selectedProduct', productId);
  
  // Se il prodotto ha un prezzo, possiamo salvarlo anche nel formData
  if (productId) {
    const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === productId);
    if (selectedProduct && selectedProduct.prezzo_base) {
      // Il prezzo_base è in centesimi, lo convertiamo in euro
      updateFormData('selectedProductPrice', selectedProduct.prezzo_base / 100);
      updateFormData('selectedProductPowerKwp', selectedProduct.potenza_kwp / 1000); // Converti da W a kW
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

    // Cerca il prodotto selezionato per ottenere la potenza
    let systemSizeKwp = 0;
    if (props.formData.selectedProduct) {
      const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
      if (selectedProduct && selectedProduct.potenza_kwp) {
        // La potenza è in watt, convertiamo in kWp (dividi per 1000)
        systemSizeKwp = selectedProduct.potenza_kwp / 1000;
      } else {
        // Fallback: prova a estrarre dal nome del prodotto (per compatibilità con prodotti hardcoded)
        const kwpMatch = props.formData.selectedProduct.match(/(\d+(\.\d+)?)kWp/);
        systemSizeKwp = kwpMatch ? parseFloat(kwpMatch[1]) : 0;
      }
    }
    
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
    const totalAnnualConsumptionKwh = daytimeConsumptionKwh + nighttimeConsumptionKwh;

    const batteryCapacityKwh = props.formData.selectedBatteryCapacity;
    const totalAutoconsumoKwh = daytimeConsumptionKwh + (batteryCapacityKwh * 365);
    const risparmioAutoconsumo = totalAutoconsumoKwh * props.formData.costPerKwh;

    const dato1Kwh = Math.max(0, annualProductionKwh - totalAnnualConsumptionKwh);
    const dato2Kwh = Math.max(0, nighttimeConsumptionKwh - (batteryCapacityKwh * 365));
    const venditaEccedenza = (dato1Kwh + dato2Kwh) * PRICE_RITIRO_DEDICATO;

    const incentiveRatio = PRICE_RITIRO_DEDICATO > 0 ? 0.108 / PRICE_RITIRO_DEDICATO : 0;
    const incentivoCer = venditaEccedenza * incentiveRatio;

    // Cerca il prezzo del prodotto selezionato
    let productPrice = 0;
    if (props.formData.selectedProduct) {
      const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
      if (selectedProduct && selectedProduct.prezzo_base) {
        // Il prezzo_base è in centesimi, lo convertiamo in euro
        productPrice = selectedProduct.prezzo_base / 100;
      } else {
        // Fallback ai prodotti hardcoded
        productPrice = PRODUCTS.find(p => p.name === props.formData.selectedProduct)?.price || 0;
      }
    }
    const batteryPrice = calculateBatteryPrice(props.formData.selectedBatteryCapacity);
    
    // Calcola i costi aggiuntivi e sconti gestendo valori percentuali
    const calculateAdjustmentAmount = (item) => {
        if (!item) return 0;
        // Se è una voce percentuale, calcola l'importo come percentuale del costo base
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + batteryPrice;
            return (baseAmount * item.valore_default) / 100;
        }
        // Altrimenti usa l'importo diretto
        return item.amount || item.valore_default || 0;
    };
    
    const additionalCostsTotal = props.formData.additionalCosts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const discountsTotal = props.formData.discounts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    
    const totalSystemCost = productPrice + batteryPrice + additionalCostsTotal - discountsTotal;

    let deductionPercentage = 0;
    if (props.formData.fiscalDeductionType === 'prima_casa') {
        deductionPercentage = 0.50;
    } else if (props.formData.fiscalDeductionType === 'seconda_casa') {
        deductionPercentage = 0.36;
    }
    const detrazioneFiscale = (totalSystemCost * deductionPercentage) / 10;
    
    return { risparmioAutoconsumo, venditaEccedenza, incentivoCer, detrazioneFiscale };
});

const isResidential = computed(() => {
  // Usa clientCategory se disponibile, altrimenti default a Residenziale
  const category = props.formData.clientCategory || 'Residenziale';
  return category.toLowerCase() === 'residenziale';
});

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
</script>

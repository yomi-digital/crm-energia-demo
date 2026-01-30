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
                <select id="roof-exp" :value="formData.roofExposure" @change="updateFormData('roofExposure', $event.target.value)" class="field-select" :disabled="loadingCoefficients">
                    <option value="">{{ loadingCoefficients ? 'Caricamento in corso...' : 'Seleziona esposizione' }}</option>
                    <option v-for="exposure in Object.keys(coefficientsMap)" :key="exposure" :value="exposure">
                        {{ exposure.split(' ').map(word => word.charAt(0) + word.slice(1).toLowerCase()).join(' ') }}
                    </option>
                </select>
             </div>
             <div>
                <label for="roof-type" class="field-label">Tipologia tetto <span style="color:red;">*</span></label>
                <select id="roof-type" :value="formData.roofType" @change="handleRoofTypeChange($event.target.value)" class="field-select" required>
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
                <div style="position: relative;">
                    <div 
                        ref="productSelectTrigger"
                        @click="toggleProductDropdown"
                        tabindex="0"
                        class="field-select"
                        style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; padding-right: 30px; min-height: 38px;"
                    >
                        <span v-if="!localSelectedProduct" style="color: #9ca3af;">Scegli un prodotto</span>
                        <span v-else style="display: flex; flex-direction: column; gap: 2px;">
                            <span>{{ selectedProductDisplayText }}</span>
                            <span v-if="selectedProductDescription && selectedProductDescription.length > 1" style="font-size: 11px; color: #6b7280;">{{ selectedProductDescription }}</span>
                        </span>
                        <svg 
                            width="12" 
                            height="12" 
                            viewBox="0 0 12 12" 
                            fill="none" 
                            xmlns="http://www.w3.org/2000/svg"
                            :style="{ transform: isProductDropdownOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }"
                        >
                            <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <Teleport to="body">
                        <div 
                            v-if="isProductDropdownOpen"
                            data-product-dropdown
                            @mousedown.prevent
                            :style="{
                                position: 'fixed',
                                top: dropdownPosition.top + 'px',
                                left: dropdownPosition.left + 'px',
                                width: dropdownPosition.width + 'px',
                                background: 'white',
                                border: '1px solid #e5e7eb',
                                borderRadius: '4px',
                                marginTop: '4px',
                                maxHeight: '300px',
                                overflowY: 'auto',
                                zIndex: 9999,
                                boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                            }"
                        >
                        <div 
                            @click="handleProductChange('')"
                            :style="{ 
                                padding: '10px 12px', 
                                cursor: 'pointer', 
                                backgroundColor: !localSelectedProduct ? '#f3f4f6' : 'transparent',
                                borderBottom: '1px solid #e5e7eb'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#f9fafb'"
                            @mouseleave="$event.target.style.backgroundColor = !localSelectedProduct ? '#f3f4f6' : 'transparent'"
                        >
                            <span>Scegli un prodotto</span>
                        </div>
                        
                        <template v-for="(products, groupName) in groupedProducts" :key="groupName">
                            <div 
                                @click.stop="toggleGroup(groupName)"
                                style="padding: 8px 12px; font-weight: bold; background-color: #e5e7eb; color: #374151; font-size: 12px; text-transform: uppercase; cursor: pointer; display: flex; justify-content: space-between; align-items: center;"
                            >
                                <span>{{ groupName }} ({{ products.length }})</span>
                                <svg 
                                    width="10" 
                                    height="10" 
                                    viewBox="0 0 12 12" 
                                    fill="none" 
                                    xmlns="http://www.w3.org/2000/svg"
                                    :style="{ transform: expandedGroups[groupName] ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }"
                                >
                                    <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div v-show="expandedGroups[groupName]">
                                <div 
                                    v-for="product in products" 
                                    :key="product.id_prodotto + '-' + groupName"
                                    @click="handleProductChange(String(product.id_prodotto))"
                                    :style="{ 
                                        padding: '10px 12px', 
                                        cursor: 'pointer', 
                                        backgroundColor: localSelectedProduct === String(product.id_prodotto) ? '#f3f4f6' : 'transparent',
                                        borderBottom: '1px solid #e5e7eb'
                                    }"
                                    @mouseenter="$event.target.style.backgroundColor = '#f9fafb'"
                                    @mouseleave="$event.target.style.backgroundColor = localSelectedProduct === String(product.id_prodotto) ? '#f3f4f6' : 'transparent'"
                                >
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span style="font-weight: 500;">{{ product.codice_prodotto }} ({{ product.prezzo_base }} €)</span>
                                        <span v-if="product.descrizione && product.descrizione.length > 1" style="font-size: 12px; color: #6b7280; line-height: 1.4;">{{ product.descrizione }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        </div>
                    </Teleport>
                </div>
                <div class="pill" style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;">
                    <label for="iva-check" style="font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;">
                     <input type="checkbox" id="iva-check" :checked="formData.iva === 1" @change="updateFormData('iva', $event.target.checked ? 1 : 0)" style="margin-right:8px;"/>
                        IVA inclusa
                    </label>
                </div>
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
                                <p class="help-text" style="font-size:12px;margin-top:4px;">Calcolata automaticamente (TAEG 9.30%, spese 1.50€/mese)</p>
                            </div>
                            <div>
                                <label class="field-label" for="installmentsMisto">Numero di rate finanziamento</label>
                                <input type="number" step="12" id="installmentsMisto" :value="formData.paymentMisto?.installments || 120" @input="handlePagamentoMistoChange('installments', $event.target.value)" class="field-input" min="12"/>
                            </div>
                        </div>
                        <p v-if="formData.paymentMisto?.primaRata + formData.paymentMisto?.secondaRata !== 80" class="error-text" style="margin-top:8px;">La somma delle prime due rate bonifico deve essere uguale a 80%.</p>
                    </div>
                    
                     <div v-if="formData.paymentMethod && (formData.paymentMethod.toLowerCase().includes('finanziamento') || formData.paymentMethod === 'Finanziamento')" class="grid-responsive-2" style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                        <div>
                            <label class="field-label" for="installmentAmount">Importo rata (€)</label>
                            <input type="number" id="installmentAmount" :value="formData.installmentAmount" @input="handleFinanziamentoChange('installmentAmount', $event.target.value)" class="field-input" readonly style="background-color:#f3f4f6;"/>
                            <p class="help-text" style="font-size:12px;margin-top:4px;">Calcolata automaticamente (TAEG 9.30%, spese 1.50€/mese)</p>
                        </div>
                        <div>
                            <label class="field-label" for="installments">Numero di rate</label>
                            <input type="number" step="12"  id="installments" :value="formData.installments" @input="handleFinanziamentoChange('installments', $event.target.value)" class="field-input" min="12"/>
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
                        Manutenzione (costo annuale)
                    </label>
                    <input v-if="formData.maintenance.enabled" type="number" placeholder="Importo" :value="formData.maintenance.cost" @input="updateFormData('maintenance', {...formData.maintenance, cost: Number($event.target.value)})" class="field-input" style="width:90px;"/>
                </div>
                 <div class="pill" style="display:flex;align-items:center;justify-content:space-between;">
                    <label for="insurance-check" style="font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;">
                     <input type="checkbox" id="insurance-check" :checked="formData.insurance.enabled" @change="updateFormData('insurance', {...formData.insurance, enabled: $event.target.checked})" style="margin-right:8px;"/>
                        Assicurazione (costo annuale)
                    </label>
                    <input v-if="formData.insurance.enabled" type="number" placeholder="Importo" :value="formData.insurance.cost" @input="updateFormData('insurance', {...formData.insurance, cost: Number($event.target.value)})" class="field-input" style="width:90px;"/>
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
                <div class="pill" style="display:flex;align-items:center;justify-content:space-between;">
                    <label for="enable-cer-check" style="font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;">
                        <input type="checkbox" id="enable-cer-check" :checked="formData.enableCer" @change="updateFormData('enableCer', $event.target.checked)" style="margin-right:8px;"/>
                        Abilita Incentivo CER
                    </label>
                    <span class="help-text" style="font-size:12px;color:#6b7280;">Calcolo: eccedenza (kWh) × 0.108 €/kWh</span>
                </div>
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
                    title="INCENTIVO CER" 
                    :value="simulationResults.incentivoCer.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 1 })"
                    description="Incentivo annuale CER per l'energia condivisa nella Comunità Energetica (eccedenza × 0.108 €/kWh)."
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
import { computed, defineEmits, defineProps, onMounted, onUnmounted, ref, watch } from 'vue';
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
const loadingCoefficients = ref(false);
const isProductDropdownOpen = ref(false);
const productSelectTrigger = ref(null);
const dropdownPosition = ref({ top: 0, left: 0, width: 0 });
const expandedGroups = ref({});

// Sincronizza il valore locale con il prop
watch(() => props.formData.selectedProduct, (newVal) => {
  localSelectedProduct.value = newVal ? String(newVal) : '';
}, { immediate: true });

// Watch per impostare il primo tetto come default quando vengono caricate le tipologie
watch(tipologieTetto, (newTipologie) => {
  if (newTipologie && newTipologie.length > 0) {
    const currentRoofType = props.formData.roofType;
    // Verifica se il valore attuale esiste nelle tipologie caricate
    const roofTypeExists = currentRoofType && newTipologie.some(t => t.nome_tipologia === currentRoofType);
    
    // Se roofType non è valorizzato, è vuoto, o non esiste nelle tipologie caricate, imposta il primo
    if (!currentRoofType || currentRoofType === '' || !roofTypeExists) {
      const primoTetto = newTipologie[0];
      updateFormData('roofType', primoTetto.nome_tipologia);
      // Se ha un costo_extra_kwp, impostalo anche
      if (primoTetto.costo_extra_kwp !== undefined && primoTetto.costo_extra_kwp !== null && primoTetto.costo_extra_kwp > 0) {
        updateFormData('roofTypePricePerKw', primoTetto.costo_extra_kwp);
      }
    }
  }
}, { immediate: true });

// Computed per raggruppare i prodotti per listino
const groupedProducts = computed(() => {
  const groups = {};
  const products = prodottiFotovoltaico.value || [];
  console.log(props.formData.clientCategory)
  // Determina la categoria cliente corrente (default: Business se vuoto)
  const category = props.formData.clientCategory || '';
  const normalizedCategory = category.trim().toLowerCase() || 'business';
  const isRes = normalizedCategory === 'residenziale';
  const targetTypeNormalized = isRes ? 'residenziale' : 'business';

  products.forEach(product => {
    // Se il prodotto non ha listini associati, mettilo in "Senza listino"
    if (!product.listini || product.listini.length === 0) {
      if (!groups['Senza listino']) {
        groups['Senza listino'] = [];
      }
      groups['Senza listino'].push(product);
    } else {
      // Altrimenti aggiungilo a ogni listino di cui fa parte
      product.listini.forEach(listino => {
        // Filtra per tipo cliente se specificato nel listino (confronto normalizzato)
        if (listino.tipo_cliente) {
          const listinoTypeNormalized = listino.tipo_cliente.trim().toLowerCase();
          if (listinoTypeNormalized !== targetTypeNormalized) {
            return; // Salta questo listino se non corrisponde al tipo cliente
          }
        }

        if (!groups[listino.nome]) {
          groups[listino.nome] = [];
        }
        groups[listino.nome].push(product);
      });
    }
  });

  return groups;
});

// Computed per il testo di visualizzazione del prodotto selezionato
const selectedProductDisplayText = computed(() => {
  if (!localSelectedProduct.value) return '';
  const product = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(localSelectedProduct.value));
  if (!product) return '';
  return `${product.codice_prodotto} (${product.prezzo_base} €)`;
});

// Computed per la descrizione del prodotto selezionato
const selectedProductDescription = computed(() => {
  if (!localSelectedProduct.value) return '';
  const product = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(localSelectedProduct.value));
  return product?.descrizione || '';
});

// Funzione per aggiornare la posizione del dropdown
const updateDropdownPosition = () => {
  if (!productSelectTrigger.value || !isProductDropdownOpen.value) {
    return;
  }
  const rect = productSelectTrigger.value.getBoundingClientRect();
  dropdownPosition.value = {
    top: rect.bottom + 4,
    left: rect.left,
    width: rect.width
  };
};

const toggleGroup = (groupName) => {
    expandedGroups.value[groupName] = !expandedGroups.value[groupName];
};

// Funzione per aprire/chiudere il dropdown
const toggleProductDropdown = () => {
  isProductDropdownOpen.value = !isProductDropdownOpen.value;
  if (isProductDropdownOpen.value) {
    // Aggiorna la posizione quando si apre
    setTimeout(() => updateDropdownPosition(), 0);
    
    // Espandi il gruppo del prodotto selezionato
    if (localSelectedProduct.value) {
        for (const [groupName, products] of Object.entries(groupedProducts.value)) {
            if (products.some(p => String(p.id_prodotto) === localSelectedProduct.value)) {
                expandedGroups.value[groupName] = true;
            }
        }
    }
  }
};

// Gestione click fuori dal dropdown
const handleClickOutside = (event) => {
  if (isProductDropdownOpen.value && productSelectTrigger.value && !productSelectTrigger.value.contains(event.target)) {
    // Controlla se il click è sul dropdown stesso
    const dropdown = document.querySelector('[data-product-dropdown]');
    if (!dropdown || !dropdown.contains(event.target)) {
      isProductDropdownOpen.value = false;
    }
  }
};

// Gestione scroll e resize per aggiornare la posizione
const handleScrollOrResize = () => {
  if (isProductDropdownOpen.value) {
    updateDropdownPosition();
  }
};

// Aggiungi listener quando il dropdown è aperto
watch(isProductDropdownOpen, (isOpen) => {
  if (isOpen) {
    updateDropdownPosition();
    document.addEventListener('click', handleClickOutside);
    // Usa capture: true per intercettare tutti gli eventi di scroll, anche quelli sui container
    document.addEventListener('scroll', handleScrollOrResize, true);
    window.addEventListener('resize', handleScrollOrResize);
  } else {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('scroll', handleScrollOrResize, true);
    window.removeEventListener('resize', handleScrollOrResize);
  }
});

// Rimuovi listener al dismount
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('scroll', handleScrollOrResize, true);
  window.removeEventListener('resize', handleScrollOrResize);
});

// Watch per aggiornare coefficientsMap quando cambia nel formData
watch(() => props.formData.coefficientsMap, (newMap) => {
  if (newMap && Object.keys(newMap).length > 0) {
    coefficientsMap.value = newMap;
    loadingCoefficients.value = false;
  }
}, { immediate: true });

// Carica i dati all'avvio del componente
onMounted(async () => {
  try {
    // Usa i coefficienti già caricati dallo step 1 se disponibili
    if (props.formData.coefficientsMap && Object.keys(props.formData.coefficientsMap).length > 0) {
      coefficientsMap.value = props.formData.coefficientsMap;
    } else {
      // Altrimenti caricali qui (fallback)
      loadingCoefficients.value = true;
      try {
        const coefficienti = await loadCoefficientiProduzione();
        coefficientiProduzione.value = coefficienti;
        
        // Converti i coefficienti in un formato utilizzabile
        if (coefficienti && coefficienti.length > 0) {
          const map = {};
          coefficienti.forEach(coeff => {
            const esposizione = coeff.esposizione;
            const area = coeff.area_geografica;
            const valore = coeff.coefficiente_kwh_kwp;
            
            if (esposizione && area && valore !== undefined && valore !== null) {
              if (!map[esposizione]) {
                map[esposizione] = {};
              }
              map[esposizione][area] = valore;
            }
          });
          coefficientsMap.value = Object.keys(map).length > 0 ? map : COEFFICIENTS;
          
          // Salva i coefficienti nel formData per uso futuro
          updateFormData('coefficientsMap', coefficientsMap.value);
        } else {
          coefficientsMap.value = COEFFICIENTS;
        }
      } finally {
        loadingCoefficients.value = false;
      }
    }
    
    // Carica tipologie tetto
    const tipologie = await loadTipologieTetto();
    console.log(tipologie);
    // Gestisci la risposta paginata o diretta
    tipologieTetto.value = Array.isArray(tipologie) ? tipologie : (tipologie?.data || []);
    
    // Imposta il primo tetto come default se non è già valorizzato o se il valore attuale non esiste
    if (tipologieTetto.value.length > 0) {
      const currentRoofType = props.formData.roofType;
      const roofTypeExists = currentRoofType && tipologieTetto.value.some(t => t.nome_tipologia === currentRoofType);
      
      if (!currentRoofType || currentRoofType === '' || !roofTypeExists) {
        const primoTetto = tipologieTetto.value[0];
        updateFormData('roofType', primoTetto.nome_tipologia);
        // Se ha un costo_extra_kwp, impostalo anche
        if (primoTetto.costo_extra_kwp !== undefined && primoTetto.costo_extra_kwp !== null && primoTetto.costo_extra_kwp > 0) {
          updateFormData('roofTypePricePerKw', primoTetto.costo_extra_kwp);
        }
      }
    }
    
    // Carica prodotti fotovoltaico
    const prodotti = await loadProdottiFotovoltaico();
    prodottiFotovoltaico.value = prodotti;
    
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
    loadingCoefficients.value = false;
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
  // Chiudi il dropdown
  isProductDropdownOpen.value = false;
  
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
    const totalAutoconsumoKwh = daytimeConsumptionKwh + Math.min(batteryEnergyPerYear, nighttimeConsumptionKwh);
    const risparmioAutoconsumo = totalAutoconsumoKwh * props.formData.costPerKwh;

    // CALCOLO VENDITA ECCEDENZA (RID)
    // Formula: energia prodotta - energia autoconsumata = energia immessa in rete
    // L'energia immessa in rete può essere venduta al prezzo di ritiro dedicato
    const energiaImmessaInRete = Math.max(0, annualProductionKwh - totalAutoconsumoKwh);
    const venditaEccedenza = energiaImmessaInRete * PRICE_RITIRO_DEDICATO;

    // CALCOLO INCENTIVO CER - eccedenza × 0.108 €/kWh (solo se abilitato)
    const incentivoCer = props.formData.enableCer ? energiaImmessaInRete * 0.108 : 0;

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
    
    const additionalCostsTotal = props.formData.additionalCosts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const discountsTotal = props.formData.discounts.reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    
    const totalSystemCost = productPrice + roofTypePrice + additionalCostsTotal - discountsTotal;

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
  console.log(props.formData)
  const category = props.formData.clientCategory || 'Business';
  return category.toLowerCase() === 'residenziale';
});

// Costanti per il calcolo finanziamento
const TAN_ANNUAL = 0.079; // 7.90%
const TAEG_ANNUAL = 0.093; // 9.30%
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



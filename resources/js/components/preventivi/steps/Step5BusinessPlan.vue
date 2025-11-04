<template>
    <div>
      <h2 class="section-title">Business Plan (Opzionale)</h2>
      <p class="muted">Scegli se desideri generare e includere il business plan ventennale nel preventivo finale.</p>
      
      <fieldset style="display:flex;flex-direction:column;gap:8px;">
        <legend class="sr-only">Opzioni Business Plan</legend>
        <div 
          @click="updateFormData('generateBusinessPlan', true)"
          :class="['pill', formData.generateBusinessPlan ? '' : '']"
          style="display:flex;align-items:center;cursor:pointer;"
        >
          <input type="radio" name="business-plan" :checked="formData.generateBusinessPlan" readonly/>
          <label style="margin-left:12px;display:block;font-size:14px;font-weight:600;color:#1f2937;">
            Sì, genera il Business Plan
            <span class="help-text">Verrà mostrata una tabella con la proiezione a 20 anni dei costi e dei benefici.</span>
          </label>
        </div>
        <div 
          @click="updateFormData('generateBusinessPlan', false)"
          :class="['pill', !formData.generateBusinessPlan ? '' : '']"
          style="display:flex;align-items:center;cursor:pointer;"
        >
          <input type="radio" name="business-plan" :checked="!formData.generateBusinessPlan" readonly/>
          <label style="margin-left:12px;display:block;font-size:14px;font-weight:600;color:#1f2937;">
            No, non generare il Business Plan
             <span class="help-text">Si procederà direttamente alla generazione del preventivo standard.</span>
          </label>
        </div>
      </fieldset>

      <div v-if="formData.generateBusinessPlan" style="margin-top:16px;">
        <h3 class="section-subtitle">Anteprima Business Plan</h3>
        <div class="table-container" style="overflow:auto;max-height:320px;position:relative;">
            <table class="table" style="min-width:1200px;">
                <thead>
                    <tr>
                        <th>Anno</th>
                        <th v-if="hasLoanPayment" class="text-right">Rata mutuo</th>
                        <th v-if="hasInsurance" class="text-right">Costo assicuraz.</th>
                        <th v-if="hasMaintenance" class="text-right">Costo manutenzione</th>
                        <th v-if="hasAnnualSavings" class="text-right">Risparmio bolletta</th>
                        <th v-if="hasEnergySale" class="text-right">Vendita energia</th>
                        <th v-if="hasCer80" class="text-right">CER 80%</th>
                        <th v-if="hasFpPnrr" class="text-right">F.P. PNRR</th>
                        <th class="text-right">Flussi di cassa</th>
                        <th class="text-right">Flussi cumulati</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in businessPlanData" :key="row.year">
                        <td style="text-align:center;font-weight:600;">{{ row.year }}</td>
                        <td v-if="hasLoanPayment" class="text-right">{{ formatCurrency(row.loanPayment) }}</td>
                        <td v-if="hasInsurance" class="text-right">{{ formatCurrency(row.insuranceCost) }}</td>
                        <td v-if="hasMaintenance" class="text-right">{{ formatCurrency(row.maintenanceCost) }}</td>
                        <td v-if="hasAnnualSavings" class="text-right" style="color:#059669;">{{ formatCurrency(row.annualSavings) }}</td>
                        <td v-if="hasEnergySale" class="text-right" style="color:#059669;">{{ formatCurrency(row.energySale) }}</td>
                        <td v-if="hasCer80" class="text-right" style="color:#059669;">{{ formatCurrency(row.cer80) }}</td>
                        <td v-if="hasFpPnrr" class="text-right" style="color:#059669;">{{ formatCurrency(row.fpPnrr) }}</td>
                        <td class="text-right" :style="{fontWeight:'700', color: row.cashFlow >= 0 ? '#047857' : '#dc2626'}">{{ formatCurrency(row.cashFlow) }}</td>
                        <td class="text-right" :style="{fontWeight:'700', color: row.cumulativeCashFlow >= 0 ? '#1f2937' : '#b91c1c'}">{{ formatCurrency(row.cumulativeCashFlow) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
    </div>
</template>

<script setup lang="js">
import { usePreventiviApi } from '@/composables/usePreventiviApi';
import { computed, defineEmits, defineProps, onMounted, ref } from 'vue';
import { calculateBatteryPrice, PRICE_RITIRO_DEDICATO } from '../constants';

const props = defineProps({
  formData: Object,
});
const emit = defineEmits(['update:formData']);

const { loadProdottiFotovoltaico, loadCoefficientiProduzione } = usePreventiviApi();

const prodottiFotovoltaico = ref([]);
const coefficientsMap = ref({});

// Computed per calcolare i dati di simulazione (stessa logica di Step4SystemSelection)
const simulationData = computed(() => {
    const systemSizeKwp = props.formData.selectedPowerKw || 0;
    if (systemSizeKwp === 0 || !props.formData.billData || props.formData.billData.length === 0 || props.formData.costPerKwh <= 0) {
        return {
            risparmioAutoconsumo: 0,
            venditaEccedenza: 0,
            incentivoCer: 0,
        };
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
    const batteryCapacityKwh = props.formData.selectedBatteryCapacity || 0;
    const batteryEnergyPerYear = batteryCapacityKwh * 365;
    const totalAutoconsumoKwh = daytimeConsumptionKwh + Math.min(batteryEnergyPerYear, nighttimeConsumptionKwh);
    const risparmioAutoconsumo = totalAutoconsumoKwh * props.formData.costPerKwh;

    const energiaImmessaInRete = Math.max(0, annualProductionKwh - totalAutoconsumoKwh);
    const venditaEccedenza = energiaImmessaInRete * PRICE_RITIRO_DEDICATO;
    const incentivoCer = energiaImmessaInRete * 0.108 * 0.80;

    return {
        risparmioAutoconsumo,
        venditaEccedenza,
        incentivoCer,
    };
});

// Calcola il costo totale del sistema
const totalSystemCost = computed(() => {
    let productPrice = 0;
    if (props.formData.selectedProduct && prodottiFotovoltaico.value.length > 0) {
        const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
        if (selectedProduct && selectedProduct.prezzo_base) {
            productPrice = selectedProduct.prezzo_base;
        }
    }
    const batteryPrice = calculateBatteryPrice(props.formData.selectedBatteryCapacity || 0);
    const roofTypePrice = props.formData.roofTypePrice || 0;
    
    const calculateAdjustmentAmount = (item) => {
        if (!item) return 0;
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + batteryPrice + roofTypePrice;
            return (baseAmount * item.valore_default) / 100;
        }
        return item.amount || item.valore_default || 0;
    };
    
    const additionalCostsTotal = (props.formData.additionalCosts || []).reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const discountsTotal = (props.formData.discounts || []).reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    
    return productPrice + batteryPrice + roofTypePrice + additionalCostsTotal - discountsTotal;
});

// Calcola il pagamento finanziamento annuo
const annualLoanPayment = computed(() => {
    const paymentMethod = props.formData.paymentMethod || '';
    const isFinanziamento = paymentMethod.toLowerCase().includes('finanziamento');
    const isMisto = paymentMethod === 'Misto';
    
    if (isFinanziamento && !isMisto) {
        return (props.formData.installmentAmount || 0) * 12;
    }
    if (isMisto && props.formData.paymentMisto) {
        return (props.formData.paymentMisto.installmentAmount || 0) * 12;
    }
    return 0;
});

const loanYears = computed(() => {
    const paymentMethod = props.formData.paymentMethod || '';
    const isFinanziamento = paymentMethod.toLowerCase().includes('finanziamento');
    const isMisto = paymentMethod === 'Misto';
    
    if (isFinanziamento && !isMisto) {
        return Math.ceil((props.formData.installments || 0) / 12);
    }
    if (isMisto && props.formData.paymentMisto) {
        return Math.ceil((props.formData.paymentMisto.installments || 0) / 12);
    }
    return 0;
});

// Computed per verificare quali colonne mostrare
const hasLoanPayment = computed(() => annualLoanPayment.value > 0);
const hasInsurance = computed(() => props.formData.insurance?.enabled && props.formData.insurance?.cost > 0);
const hasMaintenance = computed(() => props.formData.maintenance?.enabled && props.formData.maintenance?.cost > 0);
const hasAnnualSavings = computed(() => simulationData.value.risparmioAutoconsumo > 0);
const hasEnergySale = computed(() => simulationData.value.venditaEccedenza > 0);
const hasCer80 = computed(() => simulationData.value.incentivoCer > 0);
const hasFpPnrr = computed(() => {
    // F.P. PNRR è presente solo se c'è un incentivo nelle voci economiche
    const hasIncentive = (props.formData.incentives || []).some(inc => 
        inc.nome_voce?.toLowerCase().includes('pnrr') || 
        inc.description?.toLowerCase().includes('pnrr')
    );
    return hasIncentive;
});

const businessPlanData = computed(() => {
    const data = [];
    let cumulativeCashFlow = 0;
    const initialInvestment = -totalSystemCost.value;

    for (let year = 0; year <= 20; year++) {
        const loanPayment = year > 0 && year <= loanYears.value ? annualLoanPayment.value : 0;
        const insuranceCost = year > 0 && props.formData.insurance?.enabled ? (props.formData.insurance.cost || 0) : 0;
        const maintenanceCost = year > 0 && props.formData.maintenance?.enabled ? (props.formData.maintenance.cost || 0) : 0;
        
        // Applica inflazione del 2% annuo ai risparmi
        const risparmioAnnuale = year > 0 ? simulationData.value.risparmioAutoconsumo * Math.pow(1.02, year - 1) : 0;
        const venditaEnergia = year > 0 ? simulationData.value.venditaEccedenza * Math.pow(1.02, year - 1) : 0;
        const cer80 = year > 0 ? simulationData.value.incentivoCer * Math.pow(1.02, year - 1) : 0;
        
        // F.P. PNRR solo nel primo anno se presente
        const fpPnrr = year === 1 && hasFpPnrr.value ? 1000 : 0; // Placeholder, dovrebbe essere calcolato dalle voci economiche

        let cashFlow = 0;
        if (year === 0) {
            cashFlow = initialInvestment;
        } else {
            const cashIn = risparmioAnnuale + venditaEnergia + cer80 + fpPnrr;
            const cashOut = loanPayment + insuranceCost + maintenanceCost;
            cashFlow = cashIn - cashOut;
        }

        cumulativeCashFlow += cashFlow;
        
        data.push({
            year,
            loanPayment,
            insuranceCost,
            maintenanceCost,
            annualSavings: risparmioAnnuale,
            energySale: venditaEnergia,
            cer80,
            fpPnrr,
            cashFlow,
            cumulativeCashFlow,
        });
    }
    return data;
});

const updateFormData = (field, value) => {
  emit('update:formData', { [field]: value });
};

const formatCurrency = (value) => {
    if (value === 0) return '€0';
    return value.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

// Carica dati all'avvio
onMounted(async () => {
    try {
        const prodotti = await loadProdottiFotovoltaico();
        prodottiFotovoltaico.value = Array.isArray(prodotti) ? prodotti : (prodotti?.data || []);
        
        const coefficienti = await loadCoefficientiProduzione();
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
            coefficientsMap.value = Object.keys(map).length > 0 ? map : {};
        }
    } catch (error) {
        console.error('Errore nel caricamento dati:', error);
    }
});

</script>

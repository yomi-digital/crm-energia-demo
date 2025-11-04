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
                        <th class="text-right">Rata mutuo</th>
                        <th class="text-right">Costo assicuraz.</th>
                        <th class="text-right">Costo manutenzione</th>
                        <th class="text-right">Risparmio bolletta</th>
                        <th class="text-right">Vendita energia</th>
                        <th class="text-right">CER 80%</th>
                        <th class="text-right">F.P. PNRR</th>
                        <th class="text-right">Flussi di cassa</th>
                        <th class="text-right">Flussi cumulati</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in businessPlanData" :key="row.year">
                        <td style="text-align:center;font-weight:600;">{{ row.year }}</td>
                        <td class="text-right">{{ formatCurrency(row.loanPayment) }}</td>
                        <td class="text-right">{{ formatCurrency(row.insuranceCost) }}</td>
                        <td class="text-right">{{ formatCurrency(row.maintenanceCost) }}</td>
                        <td class="text-right" style="color:#059669;">{{ formatCurrency(row.annualSavings) }}</td>
                        <td class="text-right" style="color:#059669;">{{ formatCurrency(row.energySale) }}</td>
                        <td class="text-right" style="color:#059669;">{{ formatCurrency(row.cer80) }}</td>
                        <td class="text-right" style="color:#059669;">{{ formatCurrency(row.fpPnrr) }}</td>
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
import { computed, defineProps, defineEmits } from 'vue';

const props = defineProps({
  formData: Object,
});
const emit = defineEmits(['update:formData']);

const updateFormData = (field, value) => {
  const updatedFormData = { ...props.formData, [field]: value };
  emit('update:formData', updatedFormData);
};

const annualSavings = computed(() => {
    if (!props.formData.billData || props.formData.billData.length === 0) return 0;
    const totals = props.formData.billData.reduce((acc, month) => {
        acc.f1 += month.f1;
        acc.f2 += month.f2;
        acc.f3 += month.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
    const daytimeConsumption = totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17;
    return daytimeConsumption * props.formData.costPerKwh;
});

const businessPlanData = computed(() => {
    const data = [];
    let cumulativeCashFlow = 0;

    const annualLoanPayment = props.formData.paymentMethod === 'Finanziamento' ? props.formData.installmentAmount * 12 : 0;
    const loanYears = props.formData.paymentMethod === 'Finanziamento' ? Math.ceil(props.formData.installments / 12) : 0;
    
    // Placeholder for initial investment - this should be calculated from product/battery/costs
    const initialInvestment = -18000;

    for (let year = 0; year <= 20; year++) {
        const loanPayment = year > 0 && year <= loanYears ? annualLoanPayment : 0;
        const insuranceCost = year > 0 && props.formData.insurance.enabled ? props.formData.insurance.cost : 0;
        const maintenanceCost = year > 0 && props.formData.maintenance.enabled ? props.formData.maintenance.cost : 0;
        
        const currentAnnualSavings = annualSavings.value * Math.pow(1.02, year - 1);
        const energySale = (currentAnnualSavings * 0.15); // Placeholder
        const cer80 = 800; // Placeholder
        const fpPnrr = year === 1 ? 1000 : 0; // Placeholder

        let cashFlow = 0;
        if (year === 0) {
            cashFlow = initialInvestment;
        } else {
            const cashIn = currentAnnualSavings + energySale + cer80 + fpPnrr;
            const cashOut = loanPayment + insuranceCost + maintenanceCost;
            cashFlow = cashIn - cashOut;
        }

        cumulativeCashFlow += cashFlow;
        
        data.push({
            year,
            loanPayment,
            insuranceCost,
            maintenanceCost,
            annualSavings: year > 0 ? currentAnnualSavings : 0,
            energySale: year > 0 ? energySale : 0,
            cer80: year > 0 ? cer80 : 0,
            fpPnrr,
            cashFlow,
            cumulativeCashFlow,
        });
    }
    return data;
});

const formatCurrency = (value) => value.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 0 });

</script>

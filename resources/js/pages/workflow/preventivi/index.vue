<template>
  <div class="p-4 font-sans">
    <Stepper
      :steps="STEPS"
      :current-step="currentStep"
      :is-next-disabled="isNextDisabled"
      @step-click="handleStepClick"
      @next="handleNext"
      @back="handleBack"
    >
      <component :is="currentStepComponent" :form-data="formData" @update:form-data="updateFormData" :bill-entry-mode="billEntryMode" @update:bill-entry-mode="updateBillEntryMode" />
    </Stepper>
  </div>
</template>

<script setup lang="js">
import { computed, ref } from 'vue';
import { STEPS } from '../../../components/preventivi/constants';
import Stepper from '../../../components/preventivi/Stepper.vue';
import Step1ClientSelection from '../../../components/preventivi/steps/Step1ClientSelection.vue';
import Step2BillData from '../../../components/preventivi/steps/Step2BillData.vue';
import Step3ConsumptionSummary from '../../../components/preventivi/steps/Step3ConsumptionSummary.vue';
import Step4SystemSelection from '../../../components/preventivi/steps/Step4SystemSelection.vue';
import Step5BusinessPlan from '../../../components/preventivi/steps/Step5BusinessPlan.vue';
import Step6GenerateQuote from '../../../components/preventivi/steps/Step6GenerateQuote.vue';

definePage({
  meta: {
    action: 'access',
    subject: 'preventivi',
  },
})

const currentStep = ref(0);
const billEntryMode = ref(null);

const formData = ref({
    client: '',
    clientCategory: null,
    clientData: null,
    costPerKwh: 0,
    totalBillCost: 0,
    billData: [],
    geographicArea: 'CENTRO',
    roofExposure: 'SUD',
    roofType: 'A falda',
    roofTypePrice: 0,
    selectedProduct: '',
    selectedBatteryCapacity: 0,
    selectedPowerKw: 0,
    paymentMethod: 'Bonifico',
    paymentBonifico: { primaRata: 30, secondaRata: 50 },
    paymentMisto: {
        bonificoAmount: 0,
        finanziamentoAmount: 0,
        primaRata: 30,
        secondaRata: 50,
        installmentAmount: 0,
        installments: 120,
    },
    installmentAmount: 0,
    installments: 120,
    maintenance: { enabled: false, cost: 0 },
    insurance: { enabled: false, cost: 0 },
    incentives: [],
    discounts: [],
    additionalCosts: [],
    generateBusinessPlan: false,
    fiscalDeductionType: 'nessuna',
});

const handleNext = () => {
  currentStep.value = Math.min(currentStep.value + 1, STEPS.length - 1);
};

const handleBack = () => {
  currentStep.value = Math.max(currentStep.value - 1, 0);
};

const handleStepClick = (index) => {
  if (index < currentStep.value) {
      currentStep.value = index;
  }
};

const updateFormData = (newData) => {
    formData.value = { ...formData.value, ...newData };
};

const updateBillEntryMode = (newMode) => {
    billEntryMode.value = newMode;
}

const isNextDisabled = computed(() => {
  switch (currentStep.value) {
    case 0:
      return !formData.value.client;
    case 1:
      const isDataIncomplete = 
          (billEntryMode.value === 'monthly' && formData.value.billData.length < 12) ||
          (billEntryMode.value === 'bimonthly' && formData.value.billData.length < 6) ||
          (billEntryMode.value === 'annual' && formData.value.billData.length < 12) ||
          !billEntryMode.value;
      
      if (isDataIncomplete) return true;

      return !formData.value.totalBillCost || formData.value.totalBillCost <= 0;
    case 3:
       // Se non c'è un prodotto selezionato, è sicuramente invalido
       const selectedProduct = formData.value.selectedProduct;
       
       if (!selectedProduct || selectedProduct === '' || selectedProduct === 0) {
         return true;
       }
       
       // Controlla se il metodo di pagamento è Bonifico (case-insensitive)
       const paymentMethod = formData.value.paymentMethod || '';
       const isBonifico = paymentMethod.toLowerCase().includes('bonifico') && paymentMethod !== 'Misto';
       const isMisto = paymentMethod === 'Misto';
       
       if (isBonifico) {
          // Assicurati che paymentBonifico esista
          if (!formData.value.paymentBonifico) {
            return true; // Se non esiste paymentBonifico, è invalido
          }
          const { primaRata = 0, secondaRata = 0 } = formData.value.paymentBonifico;
          const sommaRate = primaRata + secondaRata;
          if (sommaRate !== 80) {
            return true; // La somma deve essere esattamente 80
          }
       }
       
       if (isMisto) {
          // Valida pagamento misto
          if (!formData.value.paymentMisto) {
            return true;
          }
          const { primaRata = 0, secondaRata = 0, bonificoAmount = 0, finanziamentoAmount = 0, installmentAmount = 0, installments = 0 } = formData.value.paymentMisto;
          const sommaRate = primaRata + secondaRata;
          if (sommaRate !== 80) {
            return true;
          }
          if (bonificoAmount <= 0 || finanziamentoAmount <= 0) {
            return true;
          }
          if (installmentAmount <= 0 || installments <= 0) {
            return true;
          }
       }
       
       return false; // Tutto ok
    default:
      return false;
  }
});

const stepComponents = [
  Step1ClientSelection,
  Step2BillData,
  Step3ConsumptionSummary,
  Step4SystemSelection,
  Step5BusinessPlan,
  Step6GenerateQuote,
];

const currentStepComponent = computed(() => stepComponents[currentStep.value]);
</script>

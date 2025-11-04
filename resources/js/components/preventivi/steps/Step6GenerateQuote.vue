<template>
    <div>
      <h2 class="section-title">Generazione Preventivo</h2>
      <p class="muted" style="margin-bottom:16px;">Rivedi i dettagli finali. Cliccando su "Genera Preventivo" creerai il documento PDF da inviare al cliente.</p>
      
      <div class="card" style="padding:16px;background:#f9fafb;max-height:384px;overflow:auto;display:flex;flex-direction:column;gap:4px;">
        <SummaryItem label="Cliente" :value="formData.client" />
        <SummaryItem 
            label="Prodotto Selezionato" 
            :value="selectedProductData?.name" 
        />
        <SummaryItem 
            label="Batteria Selezionata" 
            :value="formData.selectedBatteryCapacity > 0 ? `Batteria ${formData.selectedBatteryCapacity} kWh` : 'Nessuna'" 
        />
        <SummaryItem label="Area Geografica" :value="formData.geographicArea" />
        <SummaryItem 
            label="Pagamento" 
            :value="paymentSummary" 
        />
        <SummaryItem label="Manutenzione" :value="formData.maintenance.enabled ? `Sì (${formData.maintenance.cost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})` : 'No'" />
        <SummaryItem label="Assicurazione" :value="formData.insurance.enabled ? `Sì (${formData.insurance.cost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})` : 'No'" />
        <SummaryItem label="Business Plan" :value="formData.generateBusinessPlan ? 'Incluso' : 'Non incluso'" />
        <AdjustmentListSummary title="Incentivi" :items="formData.incentives" />
        <AdjustmentListSummary title="Sconti" :items="formData.discounts" />
        <AdjustmentListSummary title="Costi Aggiuntivi" :items="formData.additionalCosts" />
      </div>
      
       <p class="help-text" style="margin-top:16px;text-align:center;">
        Confermando, attesti la correttezza dei dati inseriti e procedi con la creazione dell'offerta commerciale.
      </p>
    </div>
</template>

<script setup lang="js">
import { computed, defineProps, onMounted, ref } from 'vue';
import { PRODUCTS } from '../constants';
import SummaryItem from '../SummaryItem.vue';
import AdjustmentListSummary from '../AdjustmentListSummary.vue';
import { usePreventiviApi } from '@/composables/usePreventiviApi';

const props = defineProps({
  formData: Object,
});

const { loadProdottiFotovoltaico } = usePreventiviApi();
const prodottiFotovoltaico = ref([]);

onMounted(async () => {
  try {
    const prodotti = await loadProdottiFotovoltaico();
    prodottiFotovoltaico.value = prodotti;
  } catch (error) {
    console.error('Errore nel caricamento prodotti:', error);
  }
});

const selectedProductData = computed(() => {
  // Cerca prima nei prodotti caricati dall'API
  if (prodottiFotovoltaico.value.length > 0) {
    const prodotto = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
    if (prodotto) {
      return {
        name: prodotto.codice_prodotto,
        price: prodotto.prezzo_base ? prodotto.prezzo_base / 100 : 0,
      };
    }
  }
  // Fallback ai prodotti hardcoded
  return PRODUCTS.find(p => p.name === props.formData.selectedProduct);
});

const paymentSummary = computed(() => {
    // Controlla se il metodo di pagamento contiene "Finanziamento" (case-insensitive)
    const isFinanziamento = props.formData.paymentMethod && 
      (props.formData.paymentMethod.toLowerCase().includes('finanziamento') || 
       props.formData.paymentMethod === 'Finanziamento');
    
    if (isFinanziamento) {
        return `Finanziamento (${props.formData.installments} rate da ${props.formData.installmentAmount.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})`;
    }
    
    // Altrimenti assume Bonifico
    return `Bonifico (${props.formData.paymentBonifico?.primaRata || 0}% - ${props.formData.paymentBonifico?.secondaRata || 0}% - 20%)`;
});

</script>

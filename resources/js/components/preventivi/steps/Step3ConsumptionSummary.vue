<template>
    <div>
        <h2 class="section-title">Analisi Consumi e Risparmio</h2>
        <p class="muted">Questa sezione mostra i calcoli automatici basati sui dati forniti. È puramente informativa.</p>
        
        <div class="grid-2" style="margin-bottom:16px;">
            <StatCard 
                title="Totale Consumo Annuo" 
                :value="totalAnnualConsumption.toLocaleString('it-IT', { maximumFractionDigits: 0 })"
                unit="kWh" 
            />
            <StatCard 
                title="Totale Costi Annui" 
                :value="totalAnnualCost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', minimumFractionDigits: 2, maximumFractionDigits: 2 })"
                unit="" 
            />
            <StatCard 
                title="Consumo Diurno Annuo" 
                :value="daytimeConsumption.toLocaleString('it-IT', { maximumFractionDigits: 0 })"
                unit="kWh" 
            />
            <StatCard 
                title="Consumo Notturno Annuo" 
                :value="nighttimeConsumption.toLocaleString('it-IT', { maximumFractionDigits: 0 })"
                unit="kWh" 
            />
            <StatCard 
                title="Capacità Batteria Consigliata" 
                :value="recommendedBattery.toLocaleString('it-IT', { maximumFractionDigits: 1 })"
                unit="kWh" 
            />
        </div>
    </div>
</template>

<script setup lang="js">
import { computed, defineProps } from 'vue';
import StatCard from '../StatCard.vue';

const props = defineProps({
  formData: Object,
});

const totals = computed(() => {
    return props.formData.billData.reduce((acc, month) => {
        acc.f1 += month.f1;
        acc.f2 += month.f2;
        acc.f3 += month.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
});

const daytimeConsumption = computed(() => totals.value.f1 * 0.83 + totals.value.f2 * 0.26 + totals.value.f3 * 0.17);
const nighttimeConsumption = computed(() => totals.value.f1 * 0.17 + totals.value.f2 * 0.74 + totals.value.f3 * 0.83);

const totalAnnualConsumption = computed(() => daytimeConsumption.value + nighttimeConsumption.value);
const totalAnnualCost = computed(() => totalAnnualConsumption.value * (props.formData.costPerKwh || 0));

const recommendedBattery = computed(() => nighttimeConsumption.value / 365);

</script>

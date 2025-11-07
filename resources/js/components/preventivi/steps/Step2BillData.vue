<template>
  <div>
    <h2 class="section-title">Dati Bolletta Energetica</h2>
    <p class="muted">Indica se possiedi i dati di consumo su base mensile, bimestrale o solo il totale annuale.</p>
    
    <div class="segments" style="margin-bottom:16px;">
      <button @click="handleModeSelect('monthly')" :class="['segment', localBillEntryMode === 'monthly' ? 'is-active' : '']">
        Ho dati Mensili
      </button>
      <button @click="handleModeSelect('bimonthly')" :class="['segment', localBillEntryMode === 'bimonthly' ? 'is-active' : '']">
        Ho dati Bimestrali
      </button>
      <button @click="handleModeSelect('annual')" :class="['segment', localBillEntryMode === 'annual' ? 'is-active' : '']">
        Ho dati Annuali
      </button>
    </div>

    <div v-if="localBillEntryMode === 'annual'" class="pill" style="margin-bottom:12px;">
      <p class="muted" style="margin-bottom:12px;">Inserisci i consumi totali dell'ultimo anno e il periodo di riferimento.</p>
      <div class="grid-responsive-3">
          <input type="number" placeholder="Consumo F1 annuale (kWh)" v-model.number="annualData.f1" class="field-input"/>
          <input type="number" placeholder="Consumo F2 annuale (kWh)" v-model.number="annualData.f2" class="field-input"/>
          <input type="number" placeholder="Consumo F3 annuale (kWh)" v-model.number="annualData.f3" class="field-input"/>
      </div>
      <div class="inline-fields" style="margin-top:12px;">
          <div class="field field--grow">
              <label class="field-label">Mese di partenza</label>
              <select v-model="startMonth" class="field-select">
                  <option v-for="m in ALL_MONTHS" :key="m" :value="m">{{ m }}</option>
              </select>
          </div>
          <div class="field field--grow">
              <label class="field-label">Anno di partenza</label>
              <select v-model.number="startYear" class="field-select">
                  <option v-for="y in Array.from({length: 5}, (_, i) => new Date().getFullYear() - i)" :key="y" :value="y">{{ y }}</option>
              </select>
          </div>
      </div>
      <button @click="handleGenerateFromAnnual" class="btn btn-primary btn-block mt-3">
        Genera Dati Mensili
      </button>
    </div>

    <div v-if="localBillEntryMode === 'monthly' || localBillEntryMode === 'bimonthly'" style="display:flex;flex-direction:column;gap:16px;">
      <div class="grid-responsive-2">
        <div>
            <label class="field-label">Mese di partenza</label>
            <select v-model="startMonth" :disabled="formData.billData.length > 0" class="field-select">
                <template v-if="localBillEntryMode === 'monthly'">
                    <option v-for="m in ALL_MONTHS" :key="m" :value="m">{{ m }}</option>
                </template>
                <template v-else>
                    <option v-for="m in BIMESTER_START_MONTHS" :key="m" :value="m">{{ m }}</option>
                </template>
            </select>
        </div>
        <div>
            <label class="field-label">Anno di partenza</label>
            <select v-model.number="startYear" :disabled="formData.billData.length > 0" class="field-select">
                <option v-for="y in Array.from({length: 5}, (_, i) => new Date().getFullYear() - i)" :key="y" :value="y">{{ y }}</option>
            </select>
        </div>
      </div>
      <div class="inline-fields">
            <template v-if="localBillEntryMode === 'monthly'">
                <button @click="handleAddMonth" :disabled="formData.billData.length >= 12" class="btn btn-primary">
                    Aggiungi Mese
                </button>
                <button v-if="formData.billData.length > 0 && formData.billData.length < 12" @click="handleAutoGenerate" class="btn btn-ghost">
                    Usa media per mesi restanti
                </button>
            </template>
            <template v-if="localBillEntryMode === 'bimonthly'">
                <button @click="handleAddBimestre" :disabled="formData.billData.length >= 6" class="btn btn-primary">
                    Aggiungi Bimestre
                </button>
                <button v-if="formData.billData.length > 0 && formData.billData.length < 6" @click="handleAutoGenerateBimestri" class="btn btn-ghost">
                    Usa media per bimestri restanti
                </button>
            </template>
      </div>
    </div>

    <div v-if="isDataComplete && localBillEntryMode" class="pill" style="margin-top:16px;">
        <label for="totalBillCost" class="section-subtitle">
            {{ costInputLabels[localBillEntryMode] }}
        </label>
        <div class="inline-fields">
            <input 
                type="number" 
                id="totalBillCost" 
                :value="formData.totalBillCost"
                @input="handleTotalCostChange($event.target.value)"
                step="0.01" 
                min="0" 
                placeholder="es. 85.50" 
                class="field-input" style="max-width:260px;"
            />
            <div v-if="formData.costPerKwh > 0" class="pill">
                <p class="pill-title">Costo medio calcolato</p>
                <p class="pill-value">{{ formData.costPerKwh.toFixed(4) }} €/kWh</p>
            </div>
        </div>
        <p class="help-text">
            {{ localBillEntryMode === 'annual' 
                ? 'Inserisci il costo totale comprensivo di tutte le voci della bolletta annuale.' 
                : `Inserisci il costo totale dell'ultima bolletta ${localBillEntryMode === 'monthly' ? 'mensile' : 'bimestrale'}.`
            }}
        </p>
    </div>

    <div v-if="formData.billData.length > 0" class="table-container" style="margin-top:12px;">
        <div class="table-scroll">
            <table class="table">
                <thead>
                   <tr>
                       <th>Periodo</th>
                       <th class="text-right">Consumo F1</th>
                       <th class="text-right">Consumo F2</th>
                       <th class="text-right">Consumo F3</th>
                       <th v-if="localBillEntryMode === 'monthly' || localBillEntryMode === 'bimonthly'" class="text-center">Azione</th>
                   </tr>
                </thead>
                <tbody>
                   <tr v-for="(item, index) in formData.billData" :key="`${item.month}-${index}`">
                       <th>{{ item.month }} {{ item.year }}</th>
                       <td><input type="number" :value="item.f1" @input="handleBillChange(index, 'f1', $event.target.value)" :readonly="localBillEntryMode === 'annual'" class="field-input" style="text-align:right;"/></td>
                       <td><input type="number" :value="item.f2" @input="handleBillChange(index, 'f2', $event.target.value)" :readonly="localBillEntryMode === 'annual'" class="field-input" style="text-align:right;"/></td>
                       <td><input type="number" :value="item.f3" @input="handleBillChange(index, 'f3', $event.target.value)" :readonly="localBillEntryMode === 'annual'" class="field-input" style="text-align:right;"/></td>
                       <td v-if="localBillEntryMode === 'monthly' || localBillEntryMode === 'bimonthly'" class="text-center">
                           <button @click="handleRemoveMonth(index)" class="btn-icon danger">&times;</button>
                       </td>
                   </tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</template>

<script setup lang="js">
import { computed, defineEmits, defineProps, reactive, ref, toRefs, watch } from 'vue';

const ALL_MONTHS = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
const BIMESTER_START_MONTHS = ALL_MONTHS.filter((_, i) => i % 2 === 0);

const props = defineProps({
  formData: Object,
  billEntryMode: String,
});
const emit = defineEmits(['update:formData', 'update:billEntryMode']);

const { formData } = toRefs(props);
const localBillEntryMode = ref(props.billEntryMode);

const annualData = reactive({ f1: 0, f2: 0, f3: 0 });
const startMonth = ref(ALL_MONTHS[0]);
const startYear = ref(new Date().getFullYear());

const updateFormData = (field, value) => {
  const updatedFormData = { ...formData.value, [field]: value };
  emit('update:formData', updatedFormData);
};

watch(localBillEntryMode, (newMode) => {
    emit('update:billEntryMode', newMode);
    // Salva anche nel formData
    updateFormData('billEntryMode', newMode);
});

const handleModeSelect = (mode) => {
  if (mode !== localBillEntryMode.value) {
    // Reset completo della tabella e di tutti i dati in un singolo update
    emit('update:formData', {
      ...formData.value,
      billData: [],
      totalBillCost: 0,
      costPerKwh: 0,
    });
    
    // Reset dati annuali
    annualData.f1 = 0;
    annualData.f2 = 0;
    annualData.f3 = 0;
    
    // Reset mese e anno di partenza
    startMonth.value = mode === 'bimonthly' ? BIMESTER_START_MONTHS[0] : ALL_MONTHS[0];
    startYear.value = new Date().getFullYear();
  }
  localBillEntryMode.value = mode;
};

const handleGenerateFromAnnual = () => {
    const { f1, f2, f3 } = annualData;
    if (f1 <= 0 && f2 <= 0 && f3 <= 0) return;

    const avgF1 = f1 / 12;
    const avgF2 = f2 / 12;
    const avgF3 = f3 / 12;

    const newBillData = [];
    const startMonthIndex = ALL_MONTHS.indexOf(startMonth.value);

    for (let i = 0; i < 12; i++) {
      const monthIndex = (startMonthIndex + i) % 12;
      const currentYear = startMonthIndex + i >= 12 ? startYear.value + 1 : startYear.value;
      newBillData.push({
        month: ALL_MONTHS[monthIndex],
        year: currentYear,
        f1: Math.round(avgF1),
        f2: Math.round(avgF2),
        f3: Math.round(avgF3),
      });
    }
    updateFormData('billData', newBillData);
};

const handleAddMonth = () => {
    if (formData.value.billData.length >= 12) return;

    const lastMonthData = formData.value.billData[formData.value.billData.length - 1];
    const nextMonthIndex = lastMonthData ? (ALL_MONTHS.indexOf(lastMonthData.month) + 1) % 12 : ALL_MONTHS.indexOf(startMonth.value);
    const nextYear = lastMonthData ? (nextMonthIndex === 0 ? lastMonthData.year + 1 : lastMonthData.year) : startYear.value;

    const newBillData = [...formData.value.billData, {
      month: ALL_MONTHS[nextMonthIndex],
      year: nextYear,
      f1: 0, f2: 0, f3: 0,
    }];
    updateFormData('billData', newBillData);
};

const handleAddBimestre = () => {
    if (formData.value.billData.length >= 6) return;

    const lastBimesterData = formData.value.billData[formData.value.billData.length - 1];
    
    let nextStartMonthIndex;
    let nextYear;

    if (lastBimesterData) {
        const lastBimesterStartMonth = lastBimesterData.month.split('-')[0];
        const lastBimesterStartIndex = ALL_MONTHS.indexOf(lastBimesterStartMonth);
        nextStartMonthIndex = (lastBimesterStartIndex + 2) % 12;
        nextYear = (lastBimesterStartIndex + 2 >= 12 && lastBimesterStartIndex < 10) ? lastBimesterData.year + 1 : lastBimesterData.year;
    } else {
        nextStartMonthIndex = ALL_MONTHS.indexOf(startMonth.value);
        nextYear = startYear.value;
    }

    const bimesterLabel = `${ALL_MONTHS[nextStartMonthIndex]}-${ALL_MONTHS[(nextStartMonthIndex + 1) % 12]}`;

    const newBillData = [...formData.value.billData, {
      month: bimesterLabel,
      year: nextYear,
      f1: 0, f2: 0, f3: 0,
    }];
    updateFormData('billData', newBillData);
};

const handleRemoveMonth = (index) => {
  updateFormData('billData', formData.value.billData.filter((_, i) => i !== index));
};

const handleBillChange = (index, field, value) => {
  const newBillData = [...formData.value.billData];
  newBillData[index] = { ...newBillData[index], [field]: Number(value) || 0 };
  updateFormData('billData', newBillData);
};

const handleAutoGenerate = () => {
    if (formData.value.billData.length === 0 || formData.value.billData.length >= 12) return;
    
    // Verifica che tutte le righe esistenti siano popolate (almeno un valore > 0)
    const allRowsPopulated = formData.value.billData.every(month => 
        month.f1 > 0 || month.f2 > 0 || month.f3 > 0
    );
    
    if (!allRowsPopulated) {
        alert('Per favore, compila tutti i mesi esistenti prima di generare automaticamente i restanti.');
        return;
    }
    
    const avg = formData.value.billData.reduce((acc, month) => {
        acc.f1 += month.f1;
        acc.f2 += month.f2;
        acc.f3 += month.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
    
    avg.f1 /= formData.value.billData.length;
    avg.f2 /= formData.value.billData.length;
    avg.f3 /= formData.value.billData.length;

    const filledData = [...formData.value.billData];
    let lastMonthData = filledData[filledData.length - 1];

    while (filledData.length < 12) {
        const nextMonthIndex = (ALL_MONTHS.indexOf(lastMonthData.month) + 1) % 12;
        const nextYear = nextMonthIndex === 0 ? lastMonthData.year + 1 : lastMonthData.year;
        const newMonth = {
            month: ALL_MONTHS[nextMonthIndex],
            year: nextYear,
            f1: Math.round(avg.f1),
            f2: Math.round(avg.f2),
            f3: Math.round(avg.f3),
        };
        filledData.push(newMonth);
        lastMonthData = newMonth;
    }
    updateFormData('billData', filledData);
};

const handleAutoGenerateBimestri = () => {
    if (formData.value.billData.length === 0 || formData.value.billData.length >= 6) return;
    
    // Verifica che tutti i bimestri esistenti siano popolati (almeno un valore > 0)
    const allRowsPopulated = formData.value.billData.every(bimester => 
        bimester.f1 > 0 || bimester.f2 > 0 || bimester.f3 > 0
    );
    
    if (!allRowsPopulated) {
        alert('Per favore, compila tutti i bimestri esistenti prima di generare automaticamente i restanti.');
        return;
    }
    
    const avg = formData.value.billData.reduce((acc, bimester) => {
        acc.f1 += bimester.f1;
        acc.f2 += bimester.f2;
        acc.f3 += bimester.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
    
    avg.f1 /= formData.value.billData.length;
    avg.f2 /= formData.value.billData.length;
    avg.f3 /= formData.value.billData.length;

    const filledData = [...formData.value.billData];
    let lastBimesterData = filledData[filledData.length - 1];

    while (filledData.length < 6) {
        const lastBimesterStartMonth = lastBimesterData.month.split('-')[0];
        const lastBimesterStartIndex = ALL_MONTHS.indexOf(lastBimesterStartMonth);
        const nextStartMonthIndex = (lastBimesterStartIndex + 2) % 12;
        const nextYear = (lastBimesterStartIndex + 2 >= 12 && lastBimesterStartIndex < 10) ? lastBimesterData.year + 1 : lastBimesterData.year;
        const bimesterLabel = `${ALL_MONTHS[nextStartMonthIndex]}-${ALL_MONTHS[(nextStartMonthIndex + 1) % 12]}`;

        const newBimester = {
            month: bimesterLabel,
            year: nextYear,
            f1: Math.round(avg.f1),
            f2: Math.round(avg.f2),
            f3: Math.round(avg.f3),
        };
        filledData.push(newBimester);
        lastBimesterData = newBimester;
    }
    updateFormData('billData', filledData);
};

const handleTotalCostChange = (value) => {
    const totalCost = Number(value) || 0;

    let totalKwhForPeriod = 0;
    if (localBillEntryMode.value === 'annual') {
        totalKwhForPeriod = formData.value.billData.reduce((sum, item) => sum + item.f1 + item.f2 + item.f3, 0);
    } else if ((localBillEntryMode.value === 'monthly' || localBillEntryMode.value === 'bimonthly') && formData.value.billData.length > 0) {
        const lastPeriod = formData.value.billData[formData.value.billData.length - 1];
        if (lastPeriod) {
            totalKwhForPeriod = lastPeriod.f1 + lastPeriod.f2 + lastPeriod.f3;
        }
    }

    const newCostPerKwh = totalCost > 0 && totalKwhForPeriod > 0
        ? totalCost / totalKwhForPeriod
        : 0;

    // Emit one combined update to avoid overwriting the previous field with stale data
    emit('update:formData', { ...formData.value, totalBillCost: totalCost, costPerKwh: newCostPerKwh });
};

const isDataComplete = computed(() => localBillEntryMode.value && (
    (localBillEntryMode.value === 'monthly' && formData.value.billData.length === 12) ||
    (localBillEntryMode.value === 'bimonthly' && formData.value.billData.length === 6) ||
    (localBillEntryMode.value === 'annual' && formData.value.billData.length === 12)
));

const costInputLabels = {
  monthly: 'Costo totale bolletta (mensile)',
  bimonthly: 'Costo totale bolletta (bimestrale)',
  annual: 'Costo totale bolletta (annuale)',
};
</script>

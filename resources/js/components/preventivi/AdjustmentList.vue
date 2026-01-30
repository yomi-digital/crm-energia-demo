<template>
    <div class="card" style="padding:12px;">
        <h4 class="section-subtitle">{{ title }}</h4>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <div v-for="(item, index) in items" :key="index" class="form-row">
                <select 
                    :value="item.id_voce || item.description" 
                    @change="handleDescriptionChange(index, $event.target.value)"
                    class="field-select field--grow"
                >
                   <option v-for="option in currentOptions" :key="option.id_voce || option.id" :value="option.id_voce || option.id">
                       {{ option.nome_voce || option.description }}
                       <span v-if="option.tipo_valore === '%'"> ({{ option.valore_default }}%)</span>
                       <span v-else-if="option.tipo_valore === '$'"> ({{ option.valore_default.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' }) }})</span>
                   </option>
                </select>
                <input 
                    type="number" 
                    :placeholder="item.tipo_valore === '%' ? 'Importo (%)' : 'Importo (€)'"
                    :value="item.tipo_valore === '%' ? (item.valore_default || item.amount) : (item.amount || item.valore_default)" 
                    @input="handleAmountChange(index, $event.target.value)"
                    :step="item.tipo_valore === '%' ? '0.01' : '0.01'"
                    min="0"
                    :disabled="disabled"
                    class="field-input" style="width:85px;"
                />
                <button @click="handleRemoveItem(index)" class="btn-icon danger" aria-label="Rimuovi">&times;</button>
            </div>
        </div>
        <button :disabled="currentOptions.length <= 0" @click="handleAddItem" class="btn btn-small btn-secondary" style="margin-top:8px;">
            <span v-if="currentOptions.length <= 0">Nessuna voce disponibile</span>
            <span v-else>+ Aggiungi {{ title }}</span>
        </button>
    </div>
</template>

<script setup lang="js">
import { usePreventiviApi } from '@/composables/usePreventiviApi';
import { computed, defineEmits, defineProps, onMounted, ref } from 'vue';
import { SAMPLE_ADDITIONAL_COSTS, SAMPLE_DISCOUNTS, SAMPLE_INCENTIVES } from './constants';

const props = defineProps({
    title: String,
    listName: String,
    items: Array,
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:items']);

const { loadVociEconomiche } = usePreventiviApi();
const vociEconomiche = ref([]);

// Mappa per il tipo di voce economica
const tipoVoceMap = {
    incentives: 'incentivo',
    discounts: 'sconto',
    additionalCosts: 'costo',
    products: 'prodotto',
};

// Carica le voci economiche all'avvio
onMounted(async () => {
    await loadVociFromApi();
});

const loadVociFromApi = async () => {
    try {
        const tipoVoce = tipoVoceMap[props.listName] || '';
        const voci = await loadVociEconomiche('', tipoVoce);
        
        // Converti i dati dall'API nel formato atteso
        vociEconomiche.value = voci.map(voce => ({
            id_voce: voce.id_voce,
            nome_voce: voce.nome_voce,
            tipo_voce: voce.tipo_voce,
            tipo_valore: voce.tipo_valore,
            valore_default: voce.valore_default,
            anni_durata_default: voce.anni_durata_default,
            anno_inizio: voce.anno_inizio,
            anno_fine: voce.anno_fine,
            iva: voce.iva || false, // Flag IVA
            // Manteniamo anche description e amount per compatibilità con il formato esistente
            description: voce.nome_voce,
            amount: voce.valore_default, // Importo di default, sarà calcolato se percentuale
        }));
    } catch (error) {
        console.error('Errore nel caricamento voci economiche:', error);
        // Fallback ai valori di default
        const fallbackMap = {
            incentives: SAMPLE_INCENTIVES,
            discounts: SAMPLE_DISCOUNTS,
            additionalCosts: SAMPLE_ADDITIONAL_COSTS,
            products: [],
        };
        vociEconomiche.value = fallbackMap[props.listName] || [];
    }
};

const currentOptions = computed(() => {
    // Se abbiamo voci dall'API, usa quelle, altrimenti usa i valori di default
    if (vociEconomiche.value.length > 0) {
        return vociEconomiche.value;
    }
    
    const fallbackMap = {
        incentives: SAMPLE_INCENTIVES,
        discounts: SAMPLE_DISCOUNTS,
        additionalCosts: SAMPLE_ADDITIONAL_COSTS,
        products: [],
    };
    return fallbackMap[props.listName] || [];
});

const handleDescriptionChange = (index, newIdVoce) => {
    const newList = [...props.items];
    const selectedOption = currentOptions.value.find(opt => 
        (opt.id_voce && opt.id_voce === Number(newIdVoce)) || 
        (opt.id && opt.id === Number(newIdVoce)) ||
        (opt.description === newIdVoce) // Fallback per compatibilità
    );
    
    if (selectedOption) {
        // Se è una voce dall'API, usa i campi corretti
        if (selectedOption.id_voce) {
            newList[index] = {
                id_voce: selectedOption.id_voce,
                nome_voce: selectedOption.nome_voce,
                tipo_valore: selectedOption.tipo_valore,
                valore_default: selectedOption.valore_default,
                anni_durata_default: selectedOption.anni_durata_default,
                anno_inizio: selectedOption.anno_inizio,
                anno_fine: selectedOption.anno_fine,
                iva: selectedOption.iva || false, // Flag IVA
                // Mantieni anche description e amount per compatibilità
                description: selectedOption.nome_voce,
                amount: selectedOption.valore_default, // Sarà calcolato se percentuale
            };
        } else {
            // Fallback per valori di default
            newList[index] = { 
                description: selectedOption.description, 
                amount: selectedOption.amount 
            };
        }
        emit('update:items', newList);
    }
};

const handleAddItem = () => {
    const firstOption = currentOptions.value[0];
    if (firstOption) {
        if (firstOption.id_voce) {
            // Voce dall'API
            emit('update:items', [...props.items, {
                id_voce: firstOption.id_voce,
                nome_voce: firstOption.nome_voce,
                tipo_valore: firstOption.tipo_valore,
                valore_default: firstOption.valore_default,
                anni_durata_default: firstOption.anni_durata_default,
                anno_inizio: firstOption.anno_inizio,
                anno_fine: firstOption.anno_fine,
                iva: firstOption.iva || false, // Flag IVA
                description: firstOption.nome_voce,
                amount: firstOption.valore_default,
            }]);
        } else {
            // Fallback per valori di default
            emit('update:items', [...props.items, { 
                description: firstOption.description, 
                amount: firstOption.amount 
            }]);
        }
    }
};

const handleRemoveItem = (index) => {
    emit('update:items', props.items.filter((_, i) => i !== index));
};

const handleAmountChange = (index, newValue) => {
    const newList = [...props.items];
    const item = newList[index];
    const numValue = Number(newValue) || 0;
    
    if (item) {
        if (item.tipo_valore === '%') {
            // Se è una percentuale, aggiorna valore_default
            newList[index] = {
                ...item,
                valore_default: numValue,
                amount: numValue, // Mantieni anche amount per compatibilità
            };
        } else {
            // Se è un importo fisso, aggiorna amount
            newList[index] = {
                ...item,
                amount: numValue,
                valore_default: numValue, // Aggiorna anche valore_default per coerenza
            };
        }
        emit('update:items', newList);
    }
};
</script>

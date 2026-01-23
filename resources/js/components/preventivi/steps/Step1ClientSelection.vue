<template>
  <div>
    <h2 class="section-title">Selezione Cliente</h2>
    <p class="muted">Seleziona il cliente dal menù a tendina o creane uno nuovo per avviare la simulazione.</p>

    <div class="form-row" style="align-items: flex-end; gap: 12px;">
      <div class="field field--grow">
        <label for="client" class="field-label">Cliente</label>
        <AppAutocomplete
          v-model="selectedCustomer"
          v-model:search="search"
          :loading="loading"
          :items="customers"
          placeholder="Cerca per nome, cognome, P.IVA, CF o email..."
          return-object
          item-title="title"
          item-value="value"
          clearable
          :custom-filter="() => true"
          auto-select-first
        >
          <template #no-data>
            <VListItem>
              <VListItemTitle v-if="loading">
                Caricamento in corso...
              </VListItemTitle>
              <VListItemTitle v-else-if="!search || search.length < 2">
                Inizia a digitare per cercare un cliente...
              </VListItemTitle>
              <VListItemTitle v-else>
                Nessun cliente trovato
              </VListItemTitle>
            </VListItem>
          </template>
          <template #item="{ props, item }">
            <VListItem v-bind="props">
              <VListItemSubtitle v-if="item.raw.rawData">
                <span v-if="item.raw.rawData.email">{{ item.raw.rawData.email }}</span>
                <span v-if="item.raw.rawData.email && (item.raw.rawData.phone || item.raw.rawData.mobile)"> • </span>
                <span v-if="item.raw.rawData.phone">{{ item.raw.rawData.phone }}</span>
                <span v-if="item.raw.rawData.phone && item.raw.rawData.mobile"> / </span>
                <span v-if="item.raw.rawData.mobile">{{ item.raw.rawData.mobile }}</span>
              </VListItemSubtitle>
            </VListItem>
          </template>
        </AppAutocomplete>
      </div>
      <button class="btn btn-secondary" @click="openModal" style="margin-bottom: 0;">Crea Cliente</button>
    </div>

    <!-- Modal per creare un nuovo cliente -->
    <VDialog
      v-model="isModalOpen"
      max-width="900px"
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crea Nuovo Cliente</span>
          <VBtn
            icon
            variant="text"
            color="primary"
            @click="closeModal"
          >
            <VIcon color="#000000" icon="tabler-x" />
          </VBtn>
        </VCardTitle>

        <VCardText>
          <FormCreate @customerData="handleCustomerCreated" />
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup lang="js">
import { usePreventiviApi } from '@/composables/usePreventiviApi';
import FormCreate from '@/views/workflow/customers/FormCreate.vue';
import { defineEmits, defineProps, onMounted, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { COEFFICIENTS } from '../constants';

const props = defineProps({
  formData: Object,
});
const emit = defineEmits(['update:formData']);

const isModalOpen = ref(false);
const customers = ref([]);
const search = ref('');
const loading = ref(false);
const selectedCustomer = ref(null);
const { loadCoefficientiProduzione } = usePreventiviApi();

const preventDefaultStopPropagation = (event) => {
  event.preventDefault();
  event.stopPropagation();
};

// Funzione per formattare il nome del cliente come in PaperworkCustomer
const getCustomerName = (customer) => {
  if (!customer) return 'Cliente sconosciuto';
  
  let name = '';
  if (customer.name) {
    name = [customer.name, customer.last_name].filter(Boolean).join(' ').trim();
  } else if (customer.business_name) {
    name = customer.business_name;
  } else {
    name = 'Cliente senza nome';
  }
  
  if (customer.vat_number) {
    name += ` - ${customer.vat_number}`;
  }
  if (customer.tax_id_code) {
    name += ` - ${customer.tax_id_code}`;
  }
  if (customer.email) {
    name += ` - ${customer.email}`;
  }
  
  return name;
};

// Funzione per caricare i clienti con ricerca dinamica
const fetchCustomers = async (query, id = null) => {
  try {
    loading.value = true;
    // Encode la query per gestire caratteri speciali nell'email
    const encodedQuery = query ? encodeURIComponent(query) : '';
    const url = `/customers?skipControl=false&itemsPerPage=50&select=1&q=${encodedQuery}` + (id ? `&id=${id}` : '');
    const response = await $api(url);
    
    if (!response || !response.customers || !Array.isArray(response.customers)) {
      console.error('Risposta API non valida:', response);
      customers.value = [];
      return;
    }
    
    customers.value = response.customers.map(customer => ({
      title: getCustomerName(customer),
      value: customer.id,
      category: customer.category || 'Business', // Aggiungi la categoria
      rawData: customer, // Salva i dati completi
    }));
    
    // Se abbiamo caricato per ID, imposta il valore selezionato
    if (id && customers.value.length === 1) {
      selectedCustomer.value = customers.value[0];
    }
  } catch (error) {
    console.error('Errore nel caricamento clienti:', error);
    customers.value = [];
  } finally {
    loading.value = false;
  }
};

// Carica i coefficienti produzione all'avvio dello step 1
onMounted(async () => {
  // Carica i coefficienti solo se non sono già stati caricati
  if (!props.formData.coefficientsMap || Object.keys(props.formData.coefficientsMap || {}).length === 0) {
    try {
      const coefficienti = await loadCoefficientiProduzione();
      
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
        
        // Salva i coefficienti nel formData solo se abbiamo dati validi
        if (Object.keys(map).length > 0) {
          emit('update:formData', { 
            ...props.formData, 
            coefficientsMap: map 
          });
        } else {
          // Fallback ai valori di default
          emit('update:formData', { 
            ...props.formData, 
            coefficientsMap: COEFFICIENTS 
          });
        }
      } else {
        // Fallback ai valori di default
        emit('update:formData', { 
          ...props.formData, 
          coefficientsMap: COEFFICIENTS 
        });
      }
    } catch (error) {
      console.error('Errore nel caricamento coefficienti:', error);
      // Fallback ai valori di default
      emit('update:formData', { 
        ...props.formData, 
        coefficientsMap: COEFFICIENTS 
      });
    }
  }
  
  // Carica il cliente iniziale se presente
  if (props.formData.client) {
    const numericId = typeof props.formData.client === 'object' ? props.formData.client.value : props.formData.client;
    await fetchCustomers('', numericId);
  }
});

// Debounced function per la ricerca clienti
const debouncedFetchCustomers = useDebounceFn((query) => {
  if (!query && !selectedCustomer.value) {
    customers.value = [];
    return;
  }
  if (query && query.length >= 2) {
    fetchCustomers(query);
  }
}, 500);

// Watch per la ricerca con debounce
watch(search, (query) => {
  debouncedFetchCustomers(query);
});

// Watch per aggiornare formData quando cambia la selezione
watch(selectedCustomer, (newVal, oldVal) => {
  console.log('selectedCustomer cambiato:', { newVal, oldVal });
  
  if (!newVal) {
    console.log('Cliente deselezionato, emetto client: null');
    emit('update:formData', { 
      ...props.formData, 
      client: null,
      clientCategory: null,
      clientData: null
    });
    return;
  }
  
  console.log(newVal)

  const clientValue = typeof newVal === 'object' ? newVal.value : newVal;
  const clientCategory = typeof newVal === 'object' ? newVal.category : 'Business';
  const clientData = typeof newVal === 'object' ? newVal.rawData : null;
  
  console.log('Emetto update:formData con client:', clientValue, 'category:', clientCategory);
  emit('update:formData', { 
    ...props.formData, 
    client: clientValue,
    clientCategory: clientCategory,
    clientData: clientData
  });
});

const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const handleCustomerCreated = async (customerData) => {
  console.log('Cliente creato - dati ricevuti:', customerData);
  
  if (!customerData || !customerData.id) {
    console.error('Dati cliente non validi:', customerData);
    alert('Errore: dati cliente non validi');
    return;
  }
  
  try {
    // Ricarica il cliente appena creato dall'API per avere dati completi
    console.log('Caricamento cliente con ID:', customerData.id);
    await fetchCustomers('', customerData.id);
    
    console.log('Clienti caricati:', customers.value);
    
    // Assicurati che il cliente sia selezionato (fetchCustomers dovrebbe averlo già fatto)
    // Ma forziamo la selezione per sicurezza
    if (customers.value.length > 0) {
      selectedCustomer.value = customers.value[0];
      console.log('Cliente selezionato:', selectedCustomer.value);
    } else {
      console.error('Nessun cliente trovato dopo la creazione');
      alert('Attenzione: cliente creato ma non trovato nella lista');
    }
  } catch (error) {
    console.error('Errore durante il caricamento del cliente creato:', error);
    alert('Errore durante il caricamento del cliente creato');
  } finally {
    // Chiudi il modal comunque
    closeModal();
  }
};
</script>

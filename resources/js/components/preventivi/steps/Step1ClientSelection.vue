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

    <div class="field" style="margin-top: 16px;">
      <label for="agent" class="field-label">Agente/Struttura (opzionale)</label>
      <AppAutocomplete
        v-model="selectedAgent"
        v-model:search="agentSearch"
        :loading="loadingAgents"
        :items="filteredAgents"
        placeholder="Cerca per nome o cognome..."
        return-object
        item-title="title"
        item-value="value"
        clearable
        :custom-filter="() => true"
      >
        <template #no-data>
          <VListItem>
            <VListItemTitle v-if="loadingAgents">
              Caricamento in corso...
            </VListItemTitle>
            <VListItemTitle v-else-if="!agentSearch || agentSearch.length < 2">
              Inizia a digitare per cercare un agente o struttura...
            </VListItemTitle>
            <VListItemTitle v-else>
              Nessun agente o struttura trovato
            </VListItemTitle>
          </VListItem>
        </template>
      </AppAutocomplete>
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
import { computed, defineEmits, defineProps, onMounted, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { COEFFICIENTS } from '../constants';

const props = defineProps({
  formData: Object,
});
const emit = defineEmits(['update:formData']);

const isModalOpen = ref(false);
const customers = ref([]);
const agents = ref([]);
const search = ref('');
const agentSearch = ref('');
const loading = ref(false);
const loadingAgents = ref(false);
const selectedCustomer = ref(null);
const selectedAgent = ref(null);
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

// Funzione per caricare gli agenti, strutture e amministratori
const fetchAgents = async (id = null) => {
  try {
    loadingAgents.value = true;
    const url = `/agents?itemsPerPage=99999999&select=1&structures=1&gestione=1&amministrazione=1${id ? `&id=${id}` : ''}`;
    const response = await $api(url);
    
    if (response?.agents && Array.isArray(response.agents)) {
      agents.value = response.agents.map(agent => ({
        title: [agent.name, agent.last_name].filter(Boolean).join(' '),
        value: agent.id,
        rawData: agent,
      }));
      
      // Se abbiamo caricato per ID, imposta il valore selezionato
      if (id && agents.value.length === 1) {
        selectedAgent.value = agents.value[0];
      }
    } else {
      agents.value = [];
    }
  } catch (error) {
    console.error('Errore nel caricamento agenti:', error);
    agents.value = [];
  } finally {
    loadingAgents.value = false;
  }
};

// Filtra gli agenti in base alla ricerca locale
const filteredAgents = computed(() => {
  if (!agentSearch.value || agentSearch.value.length < 2) {
    return agents.value;
  }
  const searchLower = agentSearch.value.toLowerCase();
  return agents.value.filter(agent => 
    agent.title.toLowerCase().includes(searchLower)
  );
});

// Watch per aggiornare formData quando cambia la selezione agente
watch(selectedAgent, (newVal) => {
  if (!newVal || newVal === null) {
    // Se viene cancellato (null), emetti null e resetta la ricerca
    agentSearch.value = '';
    emit('update:formData', {
      ...props.formData,
      selectedAgent: null,
    });
    return;
  }
  
  const agentValue = typeof newVal === 'object' ? newVal.value : (newVal || null);
  emit('update:formData', {
    ...props.formData,
    selectedAgent: agentValue,
  });
});

// Carica i coefficienti produzione all'avvio dello step 1
onMounted(async () => {
  // Carica tutti gli agenti iniziali
  await fetchAgents();
  
  // Se c'è un agente già selezionato nel formData, trovarlo nella lista
  if (props.formData.selectedAgent) {
    const numericId = typeof props.formData.selectedAgent === 'object' ? props.formData.selectedAgent.value : props.formData.selectedAgent;
    const foundAgent = agents.value.find(a => a.value === numericId);
    if (foundAgent) {
      selectedAgent.value = foundAgent;
    } else {
      // Se non trovato, prova a caricarlo per ID
      await fetchAgents(numericId);
    }
  }
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

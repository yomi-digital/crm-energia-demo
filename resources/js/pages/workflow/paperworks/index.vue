<script setup>
import PaperworkNotesDialog from '@/components/dialogs/PaperworkNotesDialog.vue'
import StatusChip from '@/components/StatusChip.vue'
import { nextTick, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'

definePage({
  meta: {
    action: 'access',
    subject: 'paperworks',
  },
})



// 👉 Store
const route = useRoute()
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const isSearchLoading = ref(false)

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedAgent = ref(route.query.user_id ? Number(route.query.user_id) : null)
const selectedCustomer = ref(route.query.customer_id ? Number(route.query.customer_id) : null)
const selectedCategory = ref(route.query.category || '')
const dateFrom = ref(route.query.date_from || '')
const dateTo = ref(route.query.date_to || '')
const selectedYear = ref('')
const selectedMonth = ref('')
const phoneSearch = ref(route.query.phone || '')
const taxIdSearch = ref(route.query.tax_id || '')
const emailSearch = ref(route.query.email || '')
const podPdrSearch = ref(route.query.pod_pdr || '')
const selectedProduct = ref(route.query.product_id ? Number(route.query.product_id) : '')
const selectedContractType = ref(route.query.contract_type || '')
const selectedSupplyType = ref(route.query.type || '')

// Debounce per la ricerca (500ms)
let searchTimeout = null
watch(searchQuery, (newValue) => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    debouncedSearchQuery.value = newValue
  }, 500)
}, { immediate: true })

// Watch per tracciare quando la ricerca debounced cambia e avviare il caricamento
watch(debouncedSearchQuery, async (newValue, oldValue) => {
  // Se il valore è cambiato e c'è una ricerca attiva, imposta il loading
  if (newValue !== oldValue && newValue !== '') {
    isSearchLoading.value = false
    await nextTick()
  } else if (newValue === '') {
    isSearchLoading.value = false
  }
})

// Cleanup del timeout quando il componente viene smontato
onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
})

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const selected = ref([])
const isBulkActionDialogOpen = ref(false)

const loggedInUser = useCookie('userData').value
// Check if in the roles array there is a role with name 'agente'
const isAgent = loggedInUser?.roles?.some(role => role.name === 'agente' || role.name === 'struttura')
const isAdmin = loggedInUser?.roles?.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
const canViewPayout = useCookie('userData').value?.roles?.some(role => role.name === 'gestione' || role.name === 'amministrazione')

// Headers
let headers = [
  // {
  //   title: '#',
  //   key: 'id',
  // },
  {
    title: 'ID Pratica',
    key: 'order_code',
  },
  {
    title: 'Account/POD/PDR',
    key: 'account_pod_pdr',
  },
  {
    title: 'Agente',
    key: 'user_id',
    sortable: false,
  },
  {
    title: 'Cliente',
    key: 'customer_id',
    sortable: false,
  },
  {
    title: 'Data Creazione',
    key: 'created_at',
  },
  {
    title: 'Data Invio',
    key: 'partner_sent_at',
  },
  {
    title: 'Stato Ordine',
    key: 'order_status',
  },
  {
    title: 'Esito Partner',
    key: 'partner_outcome',
  },
  {
    title: 'Data Esito Partner',
    key: 'partner_outcome_at',
  },
  {
    title: 'Prodotto',
    key: 'product_id',
    sortable: false,
  },
  {
    title: 'Categoria',
    key: 'category',
  },
  {
    title: 'Mandato',
    key: 'mandate_id',
    sortable: false,
  },
  {
    title: 'Pagato',
    key: 'paid',
  },
  {
    title: 'Compenso',
    key: 'pay',
    sortable: false,
  },
  {
    title: 'Azioni',
    key: 'actions',
    sortable: false,
  },
]

if (! canViewPayout) {
  headers = headers.filter(header => header.key !== 'paid')
  headers = headers.filter(header => header.key !== 'pay')
}

// Computed per pulire i parametri vuoti dalla query
const queryParams = computed(() => {
  const params = {
    itemsPerPage: itemsPerPage.value,
    page: page.value,
  }
  
  // Aggiungi solo i parametri che hanno un valore
  if (debouncedSearchQuery.value && debouncedSearchQuery.value.trim() !== '') {
    params.q = debouncedSearchQuery.value
  }
  if (selectedAgent.value !== null && selectedAgent.value !== '') {
    params.user_id = selectedAgent.value
  }
  if (selectedCustomer.value !== null && selectedCustomer.value !== '') {
    params.customer_id = selectedCustomer.value
  }
  if (selectedCategory.value && selectedCategory.value !== '') {
    params.category = selectedCategory.value
  }
  if (dateFrom.value && dateFrom.value !== '') {
    params.date_from = dateFrom.value
  }
  if (dateTo.value && dateTo.value !== '') {
    params.date_to = dateTo.value
  }
  if (phoneSearch.value && phoneSearch.value.trim() !== '') {
    params.phone = phoneSearch.value
  }
  if (taxIdSearch.value && taxIdSearch.value.trim() !== '') {
    params.tax_id = taxIdSearch.value
  }
  if (emailSearch.value && emailSearch.value.trim() !== '') {
    params.email = emailSearch.value
  }
  if (podPdrSearch.value && podPdrSearch.value.trim() !== '') {
    params.pod_pdr = podPdrSearch.value
  }
  if (selectedProduct.value !== null && selectedProduct.value !== '') {
    params.product_id = selectedProduct.value
  }
  if (selectedContractType.value && selectedContractType.value !== '') {
    params.contract_type = selectedContractType.value
  }
  if (selectedSupplyType.value && selectedSupplyType.value !== '') {
    params.type = selectedSupplyType.value
  }
  if (sortBy.value) {
    params.sortBy = sortBy.value
  }
  if (orderBy.value) {
    params.orderBy = orderBy.value
  }
  
  return params
})

const {
  data: paperworksData,
  execute: fetchPaperworks,
  pending: isFetchingPaperworks,
} = await useApi(createUrl('/paperworks', {
  query: queryParams,
}))

const paperworks = computed(() => paperworksData.value.paperworks)
const totalPaperworks = computed(() => paperworksData.value.totalPaperworks)

// Computed per mostrare il loader quando si sta cercando o caricando
const showSearchLoader = computed(() => {
  // Mostra il loader se:
  // 1. L'utente ha digitato qualcosa che non è ancora stato applicato alla ricerca (debounce)
  // 2. Oppure se sta caricando i risultati dalla chiamata API
  return searchQuery.value !== debouncedSearchQuery.value || isSearchLoading.value || (isFetchingPaperworks?.value === true)
})

// Watch per nascondere il loader quando la chiamata API è completata
watch(() => isFetchingPaperworks?.value, (isLoading) => {
  if (isLoading === false) {
    // La chiamata è completata, nascondi il loader dopo un breve delay
    setTimeout(() => {
      isSearchLoading.value = false
    }, 100)
  }
})

// Polling automatico ogni 10 secondi per aggiornare le pratiche
let pollingInterval = null

const startPolling = () => {
  // Previeni polling multipli
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
  
  pollingInterval = setInterval(async () => {
    // Non eseguire il polling se:
    // 1. C'è già una chiamata in corso
    // 2. L'utente sta digitando nella ricerca (debounce attivo)
    if (isFetchingPaperworks?.value === true || searchQuery.value !== debouncedSearchQuery.value) {
      return
    }
    
    await fetchPaperworks()
  }, 10000) // 10 secondi
}

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

// Avvia il polling quando il componente viene montato
onMounted(async () => {
  // Se ci sono query parameters, forza il refresh dei dati
  if (route.query.date_from || route.query.date_to || route.query.user_id || route.query.customer_id) {
    await fetchPaperworks()
  }
  startPolling()
})

// Ferma il polling quando l'utente esce dalla pagina
onUnmounted(() => {
  stopPolling()
})

const getCustomerName = (customer) => {
  if (! customer) {
    return 'N/A'
  }
  if (customer.name) {
    return [customer.name, customer.last_name].join(' ')
  } else if (customer.business_name) {
    return customer.business_name
  } else {
    return '#' + customer.id
  }
}

const widgetData = ref([
  {
    title: 'Session',
    value: '21,459',
    change: 29,
    desc: 'Total Users',
    icon: 'tabler-users',
    iconColor: 'primary',
  },
  {
    title: 'Paid Users',
    value: '4,567',
    change: 18,
    desc: 'Last Week Analytics',
    icon: 'tabler-user-plus',
    iconColor: 'error',
  },
  {
    title: 'Active Users',
    value: '19,860',
    change: -14,
    desc: 'Last Week Analytics',
    icon: 'tabler-user-check',
    iconColor: 'success',
  },
  {
    title: 'Pending Users',
    value: '237',
    change: 42,
    desc: 'Last Week Analytics',
    icon: 'tabler-user-search',
    iconColor: 'warning',
  },
])

const agents = ref([])
const fetchAgents = async () => {
  agents.value = []
  const response = await $api('/agents?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.agents.length; i++) {
    agents.value.push({
      title: [response.agents[i].name, response.agents[i].last_name].join(' '),
      value: response.agents[i].id,
    })
  }
  // Se c'è un valore dai query parameters, assicurati che sia impostato correttamente
  if (route.query.user_id) {
    const agentId = Number(route.query.user_id)
    if (agents.value.find(a => a.value === agentId)) {
      selectedAgent.value = agentId
    }
  }
}
if (useAbility().can('view', 'users')) {
  fetchAgents()
}

const customers = ref([])
const fetchCustomers = async () => {
  customers.value = []
  const response = await $api('/customers?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.customers.length; i++) {
    customers.value.push({
      title: getCustomerName(response.customers[i]),
      value: response.customers[i].id,
    })
  }
  // Se c'è un valore dai query parameters, assicurati che sia impostato correttamente
  if (route.query.customer_id) {
    const customerId = Number(route.query.customer_id)
    if (customers.value.find(c => c.value === customerId)) {
      selectedCustomer.value = customerId
    }
  }
}
fetchCustomers()

const products = ref([])

const fetchProducts = async () => {
  products.value = []
  const response = await $api('/products/personal?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.products.length; i++) {
    products.value.push({
      title: response.products[i].name,
      value: response.products[i].id,
    })
  }
}
fetchProducts()

// Tipi di contratto disponibili
const contractTypes = ref([
  { title: 'Residenziale', value: 'RESIDENZIALE' },
  { title: 'Business', value: 'BUSINESS' },
])

// Tipi di fornitura disponibili
const supplyTypes = ref([
  { title: 'Energia', value: 'ENERGIA' },
  { title: 'Telefonia', value: 'TELEFONIA' },
])

const onSelectionChanged = (newSelection) => {
  selected.value = newSelection
}

const openBulkActionDialog = () => {
  isBulkActionDialogOpen.value = true
}

const closeBulkActionDialog = () => {
  isBulkActionDialogOpen.value = false
}

const handleBulkAction = (newStatus) => {
  fetchPaperworks()
}

const router = useRouter()

const openPaperwork = (event, item) => {
  if(!item.item?.id){
    alert('Item ID non trovato')
    return
  }
  router.push({ name: 'workflow-paperworks-id', params: { id: item.item?.id } })
}

const isDuplicating = ref({})

// Modal states
const isConfirmDuplicateDialogVisible = ref(false)
const isDuplicateSuccessDialogVisible = ref(false)
const paperworkToDuplicate = ref(null)
const duplicatedPaperworkId = ref(null)

const showConfirmDuplicate = (paperworkId) => {
  // Trova la pratica completa dall'array paperworks
  const paperwork = paperworks.value.find(p => p.id === paperworkId)
  paperworkToDuplicate.value = paperwork
  isConfirmDuplicateDialogVisible.value = true
}

const confirmDuplicate = async () => {
  const paperworkId = paperworkToDuplicate.value.id
  isConfirmDuplicateDialogVisible.value = false
  
  try {
    isDuplicating.value[paperworkId] = true
    
    const response = await $api('/paperworks/duplicate', {
      method: 'POST',
      body: {
        praticheIds: [paperworkId]
      }
    })
    
    if (response.result && response.result[0]) {
      const result = response.result[0]
      if (result.duplication === 'success') {
        // Mostra modal di successo
        duplicatedPaperworkId.value = result.new_id
        isDuplicateSuccessDialogVisible.value = true
        // Ricarica la tabella per mostrare la nuova pratica
        await fetchPaperworks()
      } else {
        alert(`Errore nella duplicazione: ${result.message}`)
      }
    }
  } catch (error) {
    console.error('Errore durante la duplicazione:', error)
    alert('Errore durante la duplicazione della pratica')
  } finally {
    isDuplicating.value[paperworkId] = false
  }
}

const onSuccessModalClose = () => {
  isDuplicateSuccessDialogVisible.value = false
  duplicatedPaperworkId.value = null
  paperworkToDuplicate.value = null
}

const viewDuplicatedPaperwork = () => {
  isDuplicateSuccessDialogVisible.value = false
  router.push({ name: 'workflow-paperworks-id', params: { id: duplicatedPaperworkId.value } })
  duplicatedPaperworkId.value = null
  paperworkToDuplicate.value = null
}


// Modal states per le note
const isNotesDialogVisible = ref(false)
const selectedPaperworkForNotes = ref(null)

const showNotesDialog = (paperwork) => {
  selectedPaperworkForNotes.value = paperwork
  isNotesDialogVisible.value = true
}



const categories = ref([
  { title: 'ALLACCIO', value: 'ALLACCIO' },
  { title: 'OTP', value: 'OTP' },
  { title: 'SUBENTRO', value: 'SUBENTRO' },
  { title: 'VOLTURA', value: 'VOLTURA' },
  { title: 'SWITCH', value: 'SWITCH' },
  { title: 'NUOVA LINEA', value: 'NUOVA LINEA' },
  { title: 'PORTABILITÀ', value: 'PORTABILITÀ' },
])

// Anni disponibili (dal 2016 all'anno corrente + 1)
const years = computed(() => {
  const currentYear = new Date().getFullYear()
  const yearsArray = []
  
  for (let year = 2016; year <= currentYear + 1; year++) {
    yearsArray.push({
      title: year.toString(),
      value: year.toString()
    })
  }
  
  return yearsArray
})

// Mesi disponibili
const months = ref([
  { title: 'Gennaio', value: '01' },
  { title: 'Febbraio', value: '02' },
  { title: 'Marzo', value: '03' },
  { title: 'Aprile', value: '04' },
  { title: 'Maggio', value: '05' },
  { title: 'Giugno', value: '06' },
  { title: 'Luglio', value: '07' },
  { title: 'Agosto', value: '08' },
  { title: 'Settembre', value: '09' },
  { title: 'Ottobre', value: '10' },
  { title: 'Novembre', value: '11' },
  { title: 'Dicembre', value: '12' },
])

// Funzione per aggiornare le date quando cambiano anno o mese
const updateDateFromYearMonth = () => {
  if (selectedYear.value && selectedMonth.value) {
    // Imposta la data di inizio al primo giorno del mese
    dateFrom.value = `${selectedYear.value}-${selectedMonth.value}-01`
    
    // Imposta la data di fine all'ultimo giorno del mese
    const lastDay = new Date(selectedYear.value, selectedMonth.value, 0).getDate()
    dateTo.value = `${selectedYear.value}-${selectedMonth.value}-${lastDay.toString().padStart(2, '0')}`
  } else if (selectedYear.value && !selectedMonth.value) {
    // Solo anno selezionato: dal primo gennaio all'ultimo dicembre
    dateFrom.value = `${selectedYear.value}-01-01`
    dateTo.value = `${selectedYear.value}-12-31`
  } else if (!selectedYear.value && selectedMonth.value) {
    // Solo mese selezionato: dal primo al ultimo giorno del mese corrente
    const currentYear = new Date().getFullYear()
    dateFrom.value = `${currentYear}-${selectedMonth.value}-01`
    const lastDay = new Date(currentYear, selectedMonth.value, 0).getDate()
    dateTo.value = `${currentYear}-${selectedMonth.value}-${lastDay.toString().padStart(2, '0')}`
  } else {
    // Nessuna selezione: resetta le date
    dateFrom.value = ''
    dateTo.value = ''
  }
}


</script>

<template>
  <section>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
        <!-- Filtri per data -->
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField
              v-model="dateFrom"
              label="Da"
              type="date"
              clearable
              placeholder="Seleziona data inizio"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppTextField
              v-model="dateTo"
              label="A"
              type="date"
              clearable
              placeholder="Seleziona data fine"
            />
          </VCol>
        </VRow>

        <!-- Altri filtri -->
        <VRow class="mt-4">
          <VCol cols="4" v-if="$can('view', 'users')">
            <AppAutocomplete
              v-model="selectedAgent"
              label="Filtra per Agente"
              clearable
              :items="agents"
              item-title="title"
              item-value="value"
              placeholder="Seleziona un Agente"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedCustomer"
              label="Filtra per Cliente"
              clearable
              :items="customers"
              item-title="title"
              item-value="value"
              placeholder="Seleziona un Cliente"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedCategory"
              label="Filtra per Categoria"
              clearable
              :items="categories"
              placeholder="Seleziona una Categoria"
            />
          </VCol>

          <VCol cols="4">
            <AppTextField
              v-model="phoneSearch"
              label="Numero di Telefono"
              clearable
              placeholder="Cerca per telefono/cellulare"
            />
          </VCol>

          <VCol cols="4">
            <AppTextField
              v-model="taxIdSearch"
              label="Codice Fiscale"
              clearable
              placeholder="Cerca per codice fiscale"
            />
          </VCol>

          <VCol cols="4">
            <AppTextField
              v-model="emailSearch"
              label="Email"
              clearable
              placeholder="Cerca per email"
            />
          </VCol>

          <VCol cols="4">
            <AppTextField
              v-model="podPdrSearch"
              label="POD/PDR"
              clearable
              placeholder="Cerca per POD/PDR"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedProduct"
              label="Prodotto"
              clearable
              :items="products"
              placeholder="Seleziona un Prodotto"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedContractType"
              label="Tipo Contratto"
              clearable
              :items="contractTypes"
              placeholder="Seleziona tipo contratto"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedSupplyType"
              label="Tipo Fornitura"
              clearable
              :items="supplyTypes"
              placeholder="Seleziona tipo fornitura"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText>
        <div class="d-flex align-center justify-space-between mb-4">
          <h6 class="text-h6">Ricerca veloce Anno/Mese</h6>
        </div>
        
        <VRow>
          <VCol cols="6" md="3">
            <AppSelect
              v-model="selectedYear"
              label="Anno"
              clearable
              :items="years"
              placeholder="Seleziona anno"
              @update:model-value="updateDateFromYearMonth"
            />
          </VCol>

          <VCol cols="6" md="3">
            <AppSelect
              v-model="selectedMonth"
              label="Mese"
              clearable
              :items="months"
              placeholder="Seleziona mese"
              @update:model-value="updateDateFromYearMonth"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: 9999999, title: 'All' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />

          <!-- Add this new button for bulk actions -->
          <VBtn
            :disabled="selected.length === 0"
            color="primary"
            @click="openBulkActionDialog"
            v-if="isAdmin"
          >
            Aggiorna Stato Pratiche
          </VBtn>
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Cerca"
            >
              <template #append-inner>
                <VIcon
                  v-if="showSearchLoader"
                  icon="tabler-loader-2"
                  class="tabler-loader-2"
                  size="20"
                />
              </template>
            </AppTextField>
          </div>

          <!-- 👉 Export button -->
          <!-- <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Esporta
          </VBtn> -->

          <!-- 👉 Add paperwork button -->
          <VBtn
            :to="{ name: 'workflow-paperworks-create-wizard' }"
            v-if="$can('create', 'paperworks')"
            prepend-icon="tabler-plus"
          >
            Crea Pratica
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:select="selected"
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="paperworks"
        :items-length="totalPaperworks"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
        @update:model-value="onSelectionChanged"
        @click:row="openPaperwork"
      >
        <!-- Paperwork -->
        <template #item.id="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'workflow-paperworks-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                  :title="item.id"
                  @click.stop
                >
                  {{ item.id }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 Order Code -->
        <template #item.order_code="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.order_code }}
            </div>
          </div>
        </template>

        <!-- 👉 Account/POD/PDR -->
        <template #item.account_pod_pdr="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.account_pod_pdr }}
            </div>
          </div>
        </template>

        <!-- 👉 Agent -->
        <template #item.user_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.user && $can('view', 'users')"
                :to="{ name: 'admin-users-id', params: { id: item.user.id } }"
                class="font-weight-medium text-link"
                @click.stop
              >
                {{ [item.user.name, item.user.last_name].join(' ') }}
              </RouterLink>
              <template v-else>
                {{ [item.user?.name, item.user?.last_name].join(' ') }}
              </template>
            </div>
          </div>
        </template>

        <!-- 👉 Customer -->
        <template #item.customer_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.customer?.id"
                :to="{ name: 'workflow-customers-id', params: { id: item.customer.id } }"
                class="font-weight-medium text-link"
                :title="getCustomerName(item.customer)"
                @click.stop
              >
                {{ getCustomerName(item.customer) }}
              </RouterLink>
              <span
                v-else
                class="font-weight-medium"
                :title="getCustomerName(item.customer)"
              >
                {{ getCustomerName(item.customer) }}
              </span>
            </div>
          </div>
        </template>

        <!-- 👉 Created At -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(item.created_at)) }}
            </div>
          </div>
        </template>

        <!-- 👉 Partner Sent At -->
        <template #item.partner_sent_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.partner_sent_at ? new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(item.partner_sent_at)) : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- 👉 Order Status -->
        <template #item.order_status="{ item }">
          <div class="d-flex align-center gap-x-2">
            <StatusChip 
              :status="item.order_status" 
              size="small"
              fallback-style="text"
            />
          </div>
        </template>


        <!-- 👉 Prodotto -->
        <template #item.product_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.product?.name }}
            </div>
          </div>
        </template>

        <!-- 👉 Categoria -->
        <template #item.category="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.category }}
            </div>
          </div>
        </template>

        <!-- 👉 Agency -->
        <template #item.mandate_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.mandate?.name }}
            </div>
          </div>
        </template>

        <!-- 👉 Partner Outcome -->
        <template #item.partner_outcome="{ item }">
          <div class="d-flex align-center gap-x-2">
            <StatusChip 
              :status="item.partner_outcome" 
              size="small"
              fallback-style="text"
            />
          </div>
        </template>

        <!-- 👉 Partner Outcome At -->
        <template #item.partner_outcome_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.partner_outcome_at ? new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(item.partner_outcome_at)) : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- 👉 Paid -->
        <template #item.paid="{ item }">
          <VChip
            :color="item.paid ? 'success' : 'error'"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.paid ? 'SI' : 'NO' }}
          </VChip>
        </template>

        <!-- 👉 Pay -->
        <template #item.pay="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.pay && typeof item.pay === 'number' && item.pay > 0 ? `€ ${item.pay}` : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- 👉 Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VBtn
              v-if="!isAgent"
              size="small"
              color="primary"
              variant="tonal"
              :loading="isDuplicating[item?.id]"
              @click.stop="showConfirmDuplicate(item?.id)"
              :title="`Duplica pratica ${item?.id}`"
            >
              Duplica
            </VBtn>
            
            <VBtn
              v-if="item.notes || item.owner_notes"
              size="small"
              color="info"
              variant="tonal"
              @click.stop="showNotesDialog(item)"
              :title="`Visualizza note della pratica ${item?.id}`"
            >
              Note
            </VBtn>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPaperworks"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- Add the BulkActionDialog component -->
    <PaperworkUpdateStatusesBulkDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isBulkActionDialogOpen"
      :ids="selected"
      @submit="handleBulkAction"
    />

    <!-- 👉 Confirm Duplicate Dialog -->
    <ConfirmDuplicateDialog
      v-if="paperworkToDuplicate"
      v-model:isDialogVisible="isConfirmDuplicateDialogVisible"
      :paperworkData="paperworkToDuplicate"
      @confirm="confirmDuplicate"
    />

    <!-- 👉 Duplicate Success Dialog -->
    <DuplicateSuccessDialog
      v-model:isDialogVisible="isDuplicateSuccessDialogVisible"
      :duplicatedPaperworkId="duplicatedPaperworkId"
      @close="onSuccessModalClose"
      @viewPaperwork="viewDuplicatedPaperwork"
    />

    <!-- 👉 Notes Dialog -->
    <PaperworkNotesDialog
      :isDialogVisible="isNotesDialogVisible"
      :paperworkData="selectedPaperworkForNotes"
      @update:isDialogVisible="isNotesDialogVisible = $event"
    />
  </section>
</template>

<style>
.tabler-loader,
.tabler-fidget-spinner,
.tabler-loader-3,
.tabler-loader-quarter,
.tabler-refresh-dot,
.tabler-reload,
.tabler-loader-2 {
  animation: spin-animation .8s infinite;
}

@keyframes spin-animation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(359deg);
  }
}
</style>

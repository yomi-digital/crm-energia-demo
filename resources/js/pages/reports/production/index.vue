<script setup>
import { AREAS } from '@/utils/constants'
import { useRoute, useRouter } from 'vue-router'

definePage({
  meta: {
    action: 'access',
    subject: 'reports-production',
  },
})

// 👉 Router
const route = useRoute()
const router = useRouter()

// 👉 Store
const searchQuery = ref(route.query.q || '')

// Data table options
const itemsPerPage = ref(Number(route.query.itemsPerPage) || 100)
const page = ref(Number(route.query.page) || 1)
const sortBy = ref(route.query.sortBy || '')
const orderBy = ref(route.query.orderBy || '')

// Inizializza selectedBrand dall'URL (può essere una stringa separata da virgole o trattini)
const parseBrandFromUrl = () => {
  const brandParam = route.query.brand_id
  if (!brandParam) return []
  if (typeof brandParam === 'string') {
    // Supporta sia '-' che ',' come separatori
    return brandParam.split(/[-,\s]+/).filter(Boolean).map(Number).filter(n => !isNaN(n))
  }
  return []
}

// Inizializza selectedProduct dall'URL (può essere una stringa separata da virgole o trattini)
const parseProductFromUrl = () => {
  const productParam = route.query.product_id
  if (!productParam) return []
  if (typeof productParam === 'string') {
    // Supporta sia '-' che ',' come separatori
    return productParam.split(/[-,\s]+/).filter(Boolean).map(Number).filter(n => !isNaN(n))
  }
  return []
}

const selectedArea = ref(route.query.area || '')
const selectedBrand = ref(parseBrandFromUrl())
const selectedProduct = ref(parseProductFromUrl())
const selectedAgent = ref(route.query.agent_id || '')
const selectedAgency = ref(route.query.agency_id || '')
const selectedMandate = ref(route.query.mandate_id || '')
const selectedStatus = ref(route.query.status || '')
const selectedCategory = ref(route.query.category || '')
const selectedHasAppointment = ref(route.query.has_appointment || '')

const loggedInUser = useCookie('userData').value
const isAgent = loggedInUser?.roles?.some(role => role.name === 'agente')
const isAdminOrBackoffice = computed(() => {
  if (!loggedInUser?.roles) return false
  return loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice')
})
const canEditProduct = computed(() => {
  if (!loggedInUser?.roles) return false
  return loggedInUser.roles.some(role => 
    role.name === 'amministrazione' || 
    role.name === 'backoffice' || 
    role.name === 'gestione'
  )
})

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Struttura',
    key: 'parent',
    sortable: false,
  },
  {
    title: 'Agente',
    key: 'agent',
    sortable: false,
  },
  {
    title: 'Agenzia',
    key: 'agency',
    sortable: false,
  },
  {
    title: 'Mandato',
    key: 'mandate',
    sortable: false,
  },
  {
    title: 'Cliente',
    key: 'customer',
    sortable: false,
  },
  {
    title: 'Brand',
    key: 'brand',
    sortable: false,
  },
  {
    title: 'Prodotto',
    key: 'product',
    sortable: false,
    width: '200px',
  },
  {
    title: 'Tipologia',
    key: 'category',
    sortable: false,
  },
  {
    title: 'Appuntamento',
    key: 'has_appointment',
    sortable: false,
  },
  {
    title: 'Pratica',
    key: 'paperwork_id',
    sortable: false,
  },
  {
    title: 'Inserimento',
    key: 'inserted_at',
    sortable: false,
  },
  {
    title: 'Stato',
    key: 'status',
    sortable: false,
  },
  {
    title: 'Azioni',
    key: 'actions',
    sortable: false,
  },
]

// Filtra gli headers in base ai permessi dell'utente
const filteredHeaders = computed(() => {
  if (isAdminOrBackoffice.value) {
    return headers
  }
  // Gli agenti non vedono parent, agency e mandate
  return headers.filter(header => header.key !== 'parent' && header.key !== 'agency' && header.key !== 'mandate')
})

// Default to last 30 days o dall'URL
const defaultFromDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
const defaultToDate = new Date().toISOString().split('T')[0]
const fromDate = ref(route.query.from || defaultFromDate)
const toDate = ref(route.query.to || defaultToDate)
const dateRange = ref([fromDate.value, toDate.value])

// Add this watch function
watch(dateRange, (newRange) => {
  // New range looks like this: 2024-09-18 al 2024-09-21, so should get the dates from that string
  if (newRange) {
    const [from, to] = newRange.split(' al ')
    if (from && to) {
      fromDate.value = from
      toDate.value = to
    }
  }
}, { deep: true })

// Computed per concatenare i brand IDs
const brandIdsParam = computed(() => {
  return Array.isArray(selectedBrand.value) && selectedBrand.value.length > 0 
    ? selectedBrand.value.join('-') 
    : ''
})

// Computed per concatenare i product IDs
const productIdsParam = computed(() => {
  return Array.isArray(selectedProduct.value) && selectedProduct.value.length > 0 
    ? selectedProduct.value.join('-') 
    : ''
})

// Flag per evitare loop infiniti quando aggiorniamo l'URL
let isUpdatingFromUrl = false

// Update URL on filter change
watch([
  searchQuery, 
  itemsPerPage, 
  page, 
  sortBy, 
  orderBy, 
  fromDate, 
  toDate,
  selectedArea, 
  selectedBrand, 
  selectedProduct, 
  selectedAgent, 
  selectedAgency, 
  selectedMandate,
  selectedStatus, 
  selectedCategory, 
  selectedHasAppointment
], () => {
  if (isUpdatingFromUrl) return
  
  const query = {}
  
  if (searchQuery.value) query.q = searchQuery.value
  if (itemsPerPage.value) query.itemsPerPage = itemsPerPage.value
  if (page.value) query.page = page.value
  if (sortBy.value) query.sortBy = sortBy.value
  if (orderBy.value) query.orderBy = orderBy.value
  if (fromDate.value) query.from = fromDate.value
  if (toDate.value) query.to = toDate.value
  if (selectedArea.value) query.area = selectedArea.value
  if (Array.isArray(selectedBrand.value) && selectedBrand.value.length > 0) {
    query.brand_id = selectedBrand.value.join('-')
  }
  if (Array.isArray(selectedProduct.value) && selectedProduct.value.length > 0) {
    query.product_id = selectedProduct.value.join('-')
  }
  if (selectedAgent.value) query.agent_id = selectedAgent.value
  if (selectedAgency.value) query.agency_id = selectedAgency.value
  if (selectedMandate.value) query.mandate_id = selectedMandate.value
  if (selectedStatus.value) query.status = selectedStatus.value
  if (selectedCategory.value) query.category = selectedCategory.value
  if (selectedHasAppointment.value) query.has_appointment = selectedHasAppointment.value
  
  router.replace({ query })
}, { deep: true })

// Watch route.query per reagire ai cambiamenti dell'URL (browser back/forward)
watch(() => route.query, (newQuery) => {
  isUpdatingFromUrl = true
  
  if (newQuery.q !== undefined) searchQuery.value = newQuery.q || ''
  if (newQuery.itemsPerPage !== undefined) itemsPerPage.value = Number(newQuery.itemsPerPage) || 100
  if (newQuery.page !== undefined) page.value = Number(newQuery.page) || 1
  if (newQuery.sortBy !== undefined) sortBy.value = newQuery.sortBy || ''
  if (newQuery.orderBy !== undefined) orderBy.value = newQuery.orderBy || ''
  if (newQuery.from !== undefined) fromDate.value = newQuery.from || defaultFromDate
  if (newQuery.to !== undefined) toDate.value = newQuery.to || defaultToDate
  if (newQuery.area !== undefined) selectedArea.value = newQuery.area || ''
  if (newQuery.brand_id !== undefined) {
    const brandParam = newQuery.brand_id
    if (brandParam && typeof brandParam === 'string') {
      selectedBrand.value = brandParam.split(/[-,\s]+/).filter(Boolean).map(Number).filter(n => !isNaN(n))
    } else {
      selectedBrand.value = []
    }
  }
  if (newQuery.product_id !== undefined) {
    const productParam = newQuery.product_id
    if (productParam && typeof productParam === 'string') {
      selectedProduct.value = productParam.split(/[-,\s]+/).filter(Boolean).map(Number).filter(n => !isNaN(n))
    } else {
      selectedProduct.value = []
    }
  }
  if (newQuery.agent_id !== undefined) selectedAgent.value = newQuery.agent_id || ''
  if (newQuery.agency_id !== undefined) selectedAgency.value = newQuery.agency_id || ''
  if (newQuery.mandate_id !== undefined) selectedMandate.value = newQuery.mandate_id || ''
  if (newQuery.status !== undefined) selectedStatus.value = newQuery.status || ''
  if (newQuery.category !== undefined) selectedCategory.value = newQuery.category || ''
  if (newQuery.has_appointment !== undefined) selectedHasAppointment.value = newQuery.has_appointment || ''
  
  // Aggiorna dateRange
  dateRange.value = [fromDate.value, toDate.value]
  
  isUpdatingFromUrl = false
}, { deep: true })

const {
  data: reportData,
  execute: fetchReport,
} = await useApi(createUrl('/reports/production', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
    from: fromDate,
    to: toDate,
    area: selectedArea,
    brand_id: brandIdsParam,
    product_id: productIdsParam,
    agent_id: selectedAgent,
    agency_id: selectedAgency,
    mandate_id: selectedMandate,
    status: selectedStatus,
    category: selectedCategory,
    has_appointment: selectedHasAppointment,
  },
}))

const exportReport = async () => {
  try {
    const brandIds = Array.isArray(selectedBrand.value) && selectedBrand.value.length > 0 
      ? selectedBrand.value.join('-') 
      : ''
    const productIds = Array.isArray(selectedProduct.value) && selectedProduct.value.length > 0 
      ? selectedProduct.value.join('-') 
      : ''
    
    const data = await $api(`/reports/production`, {
      method: 'GET',
      query: {
        q: searchQuery.value,
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
        from: fromDate.value,
        to: toDate.value,
        export: 'csv',
        area: selectedArea.value,
        brand_id: brandIds,
        product_id: productIds,
        agent_id: selectedAgent.value,
        agency_id: selectedAgency.value,
        mandate_id: selectedMandate.value,
        status: selectedStatus.value,
        category: selectedCategory.value,
        has_appointment: selectedHasAppointment.value,
      },
      responseType: 'blob'
    })

    // Get the filename from the response headers
    const fileName = 'report_produzione.xlsx';

    const blob = new Blob([data], { type: data.type })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting report:', error)
    // Handle the error (e.g., show a notification to the user)
  }
}

const entries = computed(() => reportData.value.entries)
const totalEntries = computed(() => reportData.value.totalEntries)

const areas = AREAS

const defaultAgencyOption = {
  title: 'Tutte',
  value: '',
}

const agencies = ref([defaultAgencyOption])
const isFetchingAgencies = ref(false)

const products = ref([])

const brands = ref([])

const agents = ref([
  {
    title: 'Tutti',
    value: '',
  },
])

const statuses = ref([
  {
    title: 'Tutti',
    value: '',
  },
  {
    title: 'ACCANTONATO',
    value: 'ACCANTONATO',
  },
  {
    title: 'ATTIVO',
    value: 'ATTIVO',
  },
  {
    title: 'IN PROVISIONING',
    value: 'IN PROVISIONING',
  },
  {
    title: 'OK PAGABILE',
    value: 'OK PAGABILE',
  },
  {
    title: 'CONFERMATO',
    value: 'CONFERMATO',
  },
  {
    title: 'INVIATO',
    value: 'INVIATO',
  },
  {
    title: 'KO',
    value: 'KO',
  },
  {
    title: 'STORNO',
    value: 'STORNO',
  },
  {
    title: 'INSERITO',
    value: 'INSERITO',
  },
  {
    title: 'DA LAVORARE',
    value: 'DA LAVORARE',
  },
  {
    title: 'SOSPESO',
    value: 'SOSPESO',
  },
  {
    title: 'INVIATO OTP',
    value: 'INVIATO OTP',
  },
])

const categories = ref([
  {
    title: 'Tutti',
    value: '',
  },
  {
    title: 'ALLACCIO',
    value: 'ALLACCIO',
  },
  {
    title: 'OTP',
    value: 'OTP',
  },
  {
    title: 'SUBENTRO',
    value: 'SUBENTRO',
  },
  {
    title: 'VOLTURA',
    value: 'VOLTURA',
  },
  {
    title: 'SWITCH',
    value: 'SWITCH',
  },
  {
    title: 'NUOVA LINEA',
    value: 'NUOVA LINEA',
  },
  {
    title: 'PORTABILITÀ',
    value: 'PORTABILITÀ',
  },
])

const hasAppointmentOptions = ref([
  {
    title: 'Tutti',
    value: '',
  },
  {
    title: 'SI',
    value: 'SI',
  },
  {
    title: 'NO',
    value: 'NO',
  },
])

const mandates = ref([
  {
    title: 'Tutti',
    value: '',
  },
])

const fetchMandates = async () => {
  try {
    const response = await $api('/mandates?itemsPerPage=999999')
    mandates.value = [
      {
        title: 'Tutti',
        value: '',
      },
      ...response.mandates.map(mandate => ({
        title: mandate.name,
        value: mandate.id,
      }))
    ]
  } catch (error) {
    console.error('Errore durante il recupero dei mandati:', error)
  }
}

// Carica i mandati solo se l'utente non è un agente
if (!isAgent) {
  fetchMandates()
}
const fetchBrands = async (query) => {
  const response = await $api('/brands?itemsPerPage=999999&select=1')
  for (const brand of response.brands) {
    brands.value.push({
      title: brand.name,
      value: brand.id,
    })
  }
}
fetchBrands()

// Computed per i prodotti filtrati in base ai brand selezionati
const filteredProducts = computed(() => {
  return products.value
})

const fetchProducts = async (brandIds = []) => {
  products.value = []
  
  // Carica tutti i prodotti con i loro brand
  const response = await $api('/products?itemsPerPage=999999&enabled=1')
  const allProducts = response.products
  
  // Se ci sono brand selezionati, filtra i prodotti per quei brand
  if (Array.isArray(brandIds) && brandIds.length > 0) {
    // Filtra i prodotti che appartengono ai brand selezionati
    const filtered = allProducts.filter(product => {
      return product.brand_id && brandIds.includes(product.brand_id)
    })
    
    for (const product of filtered) {
      products.value.push({
        title: product.name,
        value: product.id,
        brand_id: product.brand_id,
      })
    }
  } else {
    // Nessun brand selezionato, carica tutti i prodotti
    for (const product of allProducts) {
      products.value.push({
        title: product.name,
        value: product.id,
        brand_id: product.brand_id,
      })
    }
  }
}

// Watch per ricaricare i prodotti quando cambiano i brand selezionati
watch(selectedBrand, async (newBrands) => {
  await fetchProducts(newBrands)
  // Se i prodotti selezionati non sono più disponibili dopo il filtro, rimuovili
  if (Array.isArray(selectedProduct.value) && selectedProduct.value.length > 0) {
    const availableProductIds = products.value.map(p => p.value)
    selectedProduct.value = selectedProduct.value.filter(id => availableProductIds.includes(id))
  }
}, { deep: true })

// Carica i prodotti iniziali
fetchProducts(selectedBrand.value)

const fetchAgents = async (query) => {
  const response = await $api('/agents?select=1')
  for (const agent of response.agents) {
    agents.value.push({
      title: `${agent.name} ${agent.last_name}`.trim(),
      value: agent.id,
    })
  }
}
fetchAgents()

const fetchAgencies = async (query = '') => {
  try {
    isFetchingAgencies.value = true

    const response = await $api('/agencies', {
      query: {
        itemsPerPage: 10,
        q: query,
      },
    })

    const fetchedAgencies = response.agencies?.map(agency => ({
      title: agency.name,
      value: agency.id,
    })) || []

    const nextAgencies = [defaultAgencyOption, ...fetchedAgencies]
    const selectedAgencyOption = agencies.value.find(agency => agency.value === selectedAgency.value)

    if (selectedAgency.value && selectedAgencyOption && !nextAgencies.some(agency => agency.value === selectedAgency.value))
      nextAgencies.push(selectedAgencyOption)

    agencies.value = nextAgencies
  } catch (error) {
    console.error('Errore durante il recupero delle agenzie:', error)
  } finally {
    isFetchingAgencies.value = false
  }
}

const handleAgenciesSearch = useDebounceFn(value => {
  fetchAgencies(value)
}, 300)

fetchAgencies()

// Dialog per mostrare le note
const isNotesDialogVisible = ref(false)
const selectedNotes = ref('')
const selectedItemInfo = ref(null)

const showNotesDialog = (item) => {
  selectedNotes.value = item.notes || 'Nessuna nota disponibile'
  selectedItemInfo.value = {
    customer: item.customer,
    orderCode: item.order_code,
    paperworkId: item.paperwork_id,
  }
  isNotesDialogVisible.value = true
}

const closeNotesDialog = () => {
  isNotesDialogVisible.value = false
  selectedNotes.value = ''
  selectedItemInfo.value = null
}

// Dialog per modificare il prodotto
const isEditProductDialogVisible = ref(false)
const selectedItemForProductEdit = ref(null)
const selectedProductId = ref(null)
const isSavingProduct = ref(false)

const showEditProductDialog = (item) => {
  selectedItemForProductEdit.value = item
  selectedProductId.value = item.product_id || null
  isEditProductDialogVisible.value = true
}

const closeEditProductDialog = () => {
  isEditProductDialogVisible.value = false
  selectedItemForProductEdit.value = null
  selectedProductId.value = null
}

const saveProduct = async () => {
  if (!selectedItemForProductEdit.value?.paperwork_id) {
    return
  }

  isSavingProduct.value = true
  
  try {
    await $api(`/paperworks/${selectedItemForProductEdit.value.paperwork_id}`, {
      method: 'PUT',
      body: {
        product_id: selectedProductId.value,
      },
    })
    
    // Ricarica il report
    await fetchReport()
    closeEditProductDialog()
  } catch (error) {
    console.error('Errore durante l\'aggiornamento del prodotto:', error)
    alert('Errore durante l\'aggiornamento del prodotto')
  } finally {
    isSavingProduct.value = false
  }
}

</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Replace the existing AppDatePicker with AppDateTimePicker -->
          <VCol cols="3">
            <AppDateTimePicker
              v-model="dateRange"
              label="Filtra per Data"
              placeholder="Seleziona intervallo date"
              :config="{ mode: 'range' }"
            />
          </VCol>

          <VCol
            v-if="!isAgent"
            cols="3"
          >
            <AppAutocomplete
              v-model="selectedArea"
              label="Filtra per Area"
              clearable
              :items="areas"
              placeholder="Seleziona un'Area"
            />
          </VCol>

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedBrand"
              label="Filtra per Brand"
              clearable
              multiple
              chips
              closable-chips
              :items="brands"

            >
              <template #selection="{ item, index }">
                <VChip v-if="index < 2">
                  <span>{{ item.title }}</span>
                </VChip>
                <span
                  v-if="index === 2"
                  class="text-grey text-caption align-self-center"
                >
                  (+{{ selectedBrand.length - 2 }} altri)
                </span>
              </template>
              <template #prepend-inner>
                <span v-if="selectedBrand.length === 0" class="text-high-emphasis">Tutti</span>
              </template>
            </AppAutocomplete>
          </VCol>

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedProduct"
              label="Filtra per Prodotto"
              clearable
              multiple
              chips
              closable-chips
              :items="filteredProducts"
              item-title="title"
              item-value="value"
              placeholder="Seleziona uno o più Prodotti"
            >
              <template #selection="{ item, index }">
                <VChip v-if="index < 2">
                  <span>{{ item.title }}</span>
                </VChip>
                <span
                  v-if="index === 2"
                  class="text-grey text-caption align-self-center"
                >
                  (+{{ selectedProduct.length - 2 }} altri)
                </span>
              </template>
              <template #prepend-inner>
                <span v-if="selectedProduct.length === 0" class="text-high-emphasis">Tutti</span>
              </template>
            </AppAutocomplete>
          </VCol>

        </VRow>

        <VRow>
          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedAgent"
              label="Filtra per Agente"
              clearable
              :items="agents"
              placeholder="Seleziona un Agente"
            />
          </VCol>

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedStatus"
              label="Filtra per Stato"
              clearable
              :items="statuses"
              placeholder="Seleziona uno Stato"
            />
          </VCol>

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedCategory"
              label="Filtra per Tipologia"
              clearable
              :items="categories"
              placeholder="Seleziona una Tipologia"
            />
          </VCol>

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedHasAppointment"
              label="Filtra per Appuntamento"
              clearable
              :items="hasAppointmentOptions"
              placeholder="Ha Appuntamento?"
            />
          </VCol>
        </VRow>

        <VRow v-if="isAdminOrBackoffice">
          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedAgency"
              label="Filtra per Agenzia"
              clearable
              :items="agencies"
            :loading="isFetchingAgencies"
              placeholder="Seleziona un'Agenzia"
            @update:search="handleAgenciesSearch"
            />
          </VCol>
          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedMandate"
              label="Filtra per Mandato"
              clearable
              :items="mandates"
              placeholder="Seleziona un Mandato"
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
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Export -->
          <VBtn
            variant="tonal"
            color="primary"
            prepend-icon="tabler-download"
            @click="exportReport"
          >
            Esporta
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="entries"
        :items-length="totalEntries"
        :headers="filteredHeaders"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Agente -->
        <template #item.agent="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                <RouterLink
                  v-if="item.agent_id"
                  :to="{ name: 'admin-users-id', params: { id: item.agent_id } }"
                  class="font-weight-medium text-link"
                  :title="item.agent"
                >
                  {{ item.agent }}
                </RouterLink>
                <span v-else>
                  {{ item.agent || 'N/A' }}
                </span>
              </h6>
            </div>
          </div>
        </template>

        <!-- Cliente -->
        <template #item.customer="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.customer_id"
                :to="{ name: 'workflow-customers-id', params: { id: item.customer_id } }"
                class="font-weight-medium text-link"
                :title="item.customer"
              >
                {{ item.customer }}
              </RouterLink>
              <span v-else>
                {{ item.customer || 'N/A' }}
              </span>
            </div>
          </div>
        </template>

        <!-- Agenzia -->
        <template #item.agency="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.agency || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Mandato -->
        <template #item.mandate="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.mandate || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Struttura -->
        <template #item.parent="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.parent_id"
                :to="{ name: 'admin-users-id', params: { id: item.parent_id } }"
                class="font-weight-medium text-link"
                :title="item.parent"
              >
                {{ item.parent }}
              </RouterLink>
              <span v-else>
                {{ item.parent || 'N/A' }}
              </span>
            </div>
          </div>
        </template>

        <!-- Brand -->
        <template #item.brand="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.brand }}
            </div>
          </div>
        </template>

        <!-- Product -->
        <template #item.product="{ item }">
          <div class="d-flex flex-column" style="max-width: 200px;width: 200px; white-space: normal;">
            <div class="text-capitalize text-high-emphasis text-body-1" style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
              <RouterLink
                :to="{ name: 'configuration-products-id', params: { id: item.product_id } }"
                class="font-weight-medium text-link"
                :title="item.product"
                style="white-space: normal; word-break: break-word;"
              >
                {{ item.product }}
              </RouterLink>
            </div>
            <VChip
              v-if="item.category"
              size="small"
              color="primary"
              variant="tonal"
              class="align-self-start"
            >
              {{ item.category }}
            </VChip>
          </div>
        </template>

        <!-- Paperwork ID -->
        <template #item.paperwork_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                :to="{ name: 'workflow-paperworks-id', params: { id: item.paperwork_id } }"
                class="font-weight-medium text-link"
                :title="item.paperwork_id"
              >
                {{ item.order_code }}
              </RouterLink>
            </div>
          </div>
        </template>

        <!-- Inserted At -->
        <template #item.inserted_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.inserted_at }}
            </div>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.status || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex flex-column gap-y-1">
            <VBtn
              v-if="item.notes"
              size="small"
              color="info"
              variant="tonal"
              @click="showNotesDialog(item)"
            >
              Mostra Note
            </VBtn>
            <VBtn
              v-if="item.paperwork_id && canEditProduct"
              size="small"
              color="warning"
              variant="tonal"
              @click="showEditProductDialog(item)"
            >
              Modifica Prodotto
            </VBtn>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalEntries"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- Notes Dialog -->
    <VDialog
      v-model="isNotesDialogVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span class="text-h5">Note</span>
          <VBtn
            icon
            variant="text"
            size="small"
            @click="closeNotesDialog"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        
        <VDivider />
        
        <VCardText class="pt-6">
          <div v-if="selectedItemInfo" class="mb-4">
            <div class="text-body-2 text-medium-emphasis mb-1">Cliente:</div>
            <div class="text-body-1 font-weight-medium mb-3">{{ selectedItemInfo.customer }}</div>
            <div v-if="selectedItemInfo.orderCode" class="text-body-2 text-medium-emphasis mb-1">Codice Ordine:</div>
            <div v-if="selectedItemInfo.orderCode" class="text-body-1 font-weight-medium mb-3">{{ selectedItemInfo.orderCode }}</div>
          </div>
          
          <div>
            <div class="text-body-2 text-medium-emphasis mb-2">Note:</div>
            <VCard variant="outlined" class="pa-4">
              <div class="text-body-1" style="white-space: pre-wrap;">
                {{ selectedNotes }}
              </div>
            </VCard>
          </div>
        </VCardText>
        
        <VDivider />
        
        <VCardActions>
          <VSpacer />
          <VBtn
            color="primary"
            @click="closeNotesDialog"
          >
            Chiudi
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Product Dialog -->
    <VDialog
      v-model="isEditProductDialogVisible"
      max-width="500"
    >
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span class="text-h5">Modifica Prodotto</span>
          <VBtn
            icon
            variant="text"
            size="small"
            @click="closeEditProductDialog"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        
        <VDivider />
        
        <VCardText class="pt-6">
          <div v-if="selectedItemForProductEdit" class="mb-4">
            <div class="text-body-2 text-medium-emphasis mb-1">Cliente:</div>
            <div class="text-body-1 font-weight-medium mb-3">{{ selectedItemForProductEdit.customer }}</div>
            <div v-if="selectedItemForProductEdit.order_code" class="text-body-2 text-medium-emphasis mb-1">Codice Ordine:</div>
            <div v-if="selectedItemForProductEdit.order_code" class="text-body-1 font-weight-medium mb-3">{{ selectedItemForProductEdit.order_code }}</div>
            <div class="text-body-2 text-medium-emphasis mb-1">Prodotto Attuale:</div>
            <div class="text-body-1 font-weight-medium mb-4">{{ selectedItemForProductEdit.product || 'N/A' }}</div>
          </div>
          
          <div>
            <AppAutocomplete
              v-model="selectedProductId"
              label="Seleziona Prodotto"
              :items="products.filter(p => p.value !== '')"
              item-title="title"
              item-value="value"
              placeholder="Seleziona un prodotto"
              clearable
            />
          </div>
        </VCardText>
        
        <VDivider />
        
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="tonal"
            color="secondary"
            @click="closeEditProductDialog"
            :disabled="isSavingProduct"
          >
            Annulla
          </VBtn>
          <VBtn
            color="primary"
            @click="saveProduct"
            :loading="isSavingProduct"
          >
            Salva
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>

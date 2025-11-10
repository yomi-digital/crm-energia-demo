<script setup>
import { AREAS } from '@/utils/constants'

definePage({
  meta: {
    action: 'access',
    subject: 'reports-production',
  },
})

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(100)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const selectedArea = ref('')
const selectedBrand = ref('')
const selectedProduct = ref('')
const selectedAgent = ref('')
const selectedAgency = ref('')
const selectedStatus = ref('')
const selectedCategory = ref('')
const selectedHasAppointment = ref('')

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
    title: 'Brand',
    key: 'brand',
    sortable: false,
  },
  {
    title: 'Prodotto',
    key: 'product',
    sortable: false,
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
    title: 'Data Inserimento',
    key: 'inserted_at',
    sortable: false,
  },
  {
    title: 'Stato',
    key: 'status',
    sortable: false,
  },
]

// Default to last 30 days
const fromDate = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0])
const toDate = ref(new Date().toISOString().split('T')[0])
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
    brand_id: selectedBrand,
    product_id: selectedProduct,
    agent_id: selectedAgent,
    agency_id: selectedAgency,
    status: selectedStatus,
    category: selectedCategory,
    has_appointment: selectedHasAppointment,
  },
}))

const exportReport = async () => {
  try {
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
        brand_id: selectedBrand.value,
        product_id: selectedProduct.value,
        agent_id: selectedAgent.value,
        agency_id: selectedAgency.value,
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

const products = ref([
  {
    title: 'Tutti',
    value: '',
  },
])

const brands = ref([
  {
    title: 'Tutti',
    value: '',
  },
])

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
    title: 'OK PAGABILE',
    value: 'OK PAGABILE',
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

const fetchProducts = async (query) => {
  const response = await $api('/products?itemsPerPage=999999&enabled=1')
  for (const product of response.products) {
    products.value.push({
      title: product.name,
      value: product.id,
    })
  }
}
fetchProducts()

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
        itemsPerPage: 100,
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

          <VCol cols="3">
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
              :items="brands"
              placeholder="Seleziona una Brand"
            />
          </VCol>

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedProduct"
              label="Filtra per Prodotto"
              clearable
              :items="products"
              placeholder="Seleziona un Prodotto"
            />
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

        <VRow>
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
        :headers="headers"
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

        <!-- Agenzia -->
        <template #item.agency="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.agency || 'N/A' }}
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
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                :to="{ name: 'configuration-products-id', params: { id: item.product_id } }"
                class="font-weight-medium text-link"
                :title="item.product"
              >
                {{ item.product }}
              </RouterLink>
            </div>
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
  </section>
</template>

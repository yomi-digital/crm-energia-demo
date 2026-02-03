<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'reports-admin',
  },
})

import { AREAS } from '@/utils/constants'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(100)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const selectedUser = ref('')
const selectedBrand = ref('')
const selectedProduct = ref('')
const selectedStatus = ref('')
const selectedCategory = ref('')
const selectedAgency = ref('')
const selectedArea = ref('')
const selectedAdditionalCosts = ref('')
// const selectedMandate = ref('')

// Selected items in the table
const selectedItems = ref([])

const router = useRouter()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Pratica',
    key: 'paperwork_id',
    sortable: false,
  },
  {
    title: 'Account POD/PDR',
    key: 'account_pod_pdr',
    sortable: false,
  },
  {
    title: 'Struttura',
    key: 'parent',
    sortable: false,
  },
  {
    title: 'Agenzia',
    key: 'agency',
    sortable: false,
  },
  {
    title: 'Collaboratore',
    key: 'agent',
    sortable: false,
  },
  {
    title: 'Area',
    key: 'area',
    sortable: false,
  },
  {
    title: 'Cliente',
    key: 'customer',
    sortable: false,
  },
  {
    title: 'CF',
    key: 'tax_id_code',
    sortable: false,
  },
  {
    title: 'Partita IVA',
    key: 'vat_number',
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
    title: 'Stato',
    key: 'status',
    sortable: false,
  },
  {
    title: 'Data Inserimento',
    key: 'inserted_at',
    sortable: false,
  },
  {
    title: 'Data Esito Partner',
    key: 'activated_at',
    sortable: false,
  },
  {
    title: 'Compenso',
    key: 'payout',
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
} = await useApi(createUrl('/reports/admin', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
    from: fromDate,
    to: toDate,
    user_id: selectedUser,
    brand_id: selectedBrand,
    product_id: selectedProduct,
    status: selectedStatus,
    category: selectedCategory,
    agency_id: selectedAgency,
    area: selectedArea,
    additional_costs: selectedAdditionalCosts,
  },
}))

const exportReport = async () => {
  try {
    const data = await $api(`/reports/admin`, {
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
        user_id: selectedUser.value,
        brand_id: selectedBrand.value,
        product_id: selectedProduct.value,
        status: selectedStatus.value,
        category: selectedCategory.value,
        agency_id: selectedAgency.value,
        area: selectedArea.value || undefined,
      },
      responseType: 'blob'
    })

    // Get the filename from the response headers
    const fileName = 'report_amministrativo.xlsx';

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

const users = ref([
  {
    title: 'Tutti',
    value: '',
  },
])
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

const agencies = ref([
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
    title: 'KO',
    value: 'KO',
  },
  {
    title: 'OK PAGABILE',
    value: 'OK PAGABILE',
  },
  {
    title: 'STORNO',
    value: 'STORNO',
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

const additionalCostsOptions = ref([
  {
    title: 'Tutti',
    value: '',
  },
  {
    title: 'Con Spedizione',
    value: 'shipping',
  },
  {
    title: 'Con Visura',
    value: 'visura',
  },
  {
    title: 'Con Altri Costi',
    value: 'other',
  },
  {
    title: 'Con Costi Aggiuntivi',
    value: 'any',
  },
  {
    title: 'Senza Costi Aggiuntivi',
    value: 'none',
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

const fetchUsers = async (query) => {
  const response = await $api('/users?itemsPerPage=999999&enabled=1')
  for (const user of response.users) {
    users.value.push({
      title: `${user.name} ${user.last_name}`,
      value: user.id,
    })
  }
}
fetchUsers()

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

const fetchAgencies = async (query) => {
  const response = await $api('/agencies?itemsPerPage=999999&select=1')
  for (const agency of response.agencies) {
    agencies.value.push({
      title: agency.name,
      value: agency.id,
    })
  }
}
fetchAgencies()


const saveReport = async () => {
  const data = await $api(`/reports/admin`, {
    method: 'GET',
    query: {
      q: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
      from: fromDate.value,
      to: toDate.value,
      save: true,
      user_id: selectedUser.value,
      brand_id: selectedBrand.value,
      product_id: selectedProduct.value,
      agency_id: selectedAgency.value,
      area: selectedArea.value || undefined,
      additional_costs: selectedAdditionalCosts.value,
    },
  })
  // Redirect to the saved report
  router.push({ name: 'reports-saved-id', params: { id: data.report_id } })
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

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedUser"
              label="Filtra per Account"
              clearable
              :items="users"
              placeholder="Seleziona un Account"
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

          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedAgency"
              label="Filtra per Agenzia"
              clearable
              :items="agencies"
              placeholder="Seleziona un'Agenzia"
            />
          </VCol>

          <VCol cols="3">
            <AppSelect
              v-model="selectedArea"
              label="Filtra per Area"
              placeholder="Tutte le aree"
              :items="AREAS"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>

        <VRow>
          <VCol cols="3">
            <AppAutocomplete
              v-model="selectedStatus"
              label="Filtra per Esito Partner"
              clearable
              :items="statuses"
              placeholder="Seleziona un Esito Partner"
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
              v-model="selectedAdditionalCosts"
              label="Filtra per Costi Aggiuntivi"
              clearable
              :items="additionalCostsOptions"
              placeholder="Seleziona un filtro"
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
          <!-- Save -->
          <VBtn
            variant="tonal"
            color="primary"
            prepend-icon="tabler-cloud-upload"
            :disabled="!selectedUser"
            @click="saveReport"
          >
            Salva
          </VBtn>
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
        v-model:selected="selectedItems"
        :items="entries"
        :items-length="totalEntries"
        :headers="headers"
        item-value="paperwork_id"
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
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
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
              </h6>
            </div>
          </div>
        </template>

        <!-- CF -->
        <template #item.tax_id_code="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.tax_id_code }}
            </div>
          </div>
        </template>

        <!-- Partita IVA -->
        <template #item.vat_number="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.vat_number }}
            </div>
          </div>
        </template>

        <!-- Account POD/PDR -->
        <template #item.account_pod_pdr="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.account_pod_pdr }}
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

        <!-- Agenzia -->
        <template #item.agency="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.agency || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Area -->
        <template #item.area="{ item }">
          <VChip
            v-if="item.area"
            color="primary"
            size="small"
            label
            class="text-uppercase"
          >
            {{ item.area }}
          </VChip>
          <span v-else class="text-medium-emphasis">N/A</span>
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
                v-if="item.product_id"
                :to="{ name: 'configuration-products-id', params: { id: item.product_id } }"
                class="font-weight-medium text-link"
                :title="item.product"
              >
                {{ item.product }}
              </RouterLink>
              <span v-else>
                {{ item.product }}
              </span>
            </div>
          </div>
        </template>

        <!-- Paperwork ID -->
        <template #item.paperwork_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.paperwork_id"
                :to="{ name: 'workflow-paperworks-id', params: { id: item.paperwork_id } }"
                class="font-weight-medium text-link"
                :title="item.paperwork_id"
              >
                {{ item.order_code }}
              </RouterLink>
              <span v-else>
                {{ item.order_code || 'N/A' }}
              </span>
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

        <!-- Activated At -->
        <template #item.activated_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.activated_at }}
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

        <!-- Payout -->
        <template #item.payout="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.payout ? `€ ${item.payout}` : 'N/A' }}
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

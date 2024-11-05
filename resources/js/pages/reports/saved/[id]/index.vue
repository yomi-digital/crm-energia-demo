<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'reports',
  },
})

const route = useRoute('reports-saved-id')

// Data table options
const itemsPerPage = ref(100)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

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
    title: 'Data Attivazione',
    key: 'activated_at',
    sortable: false,
  },
  {
    title: 'Stato',
    key: 'status',
    sortable: false,
  },
  {
    title: 'Compenso',
    key: 'payout',
    sortable: false,
  },
  {
    title: 'Compenso confermato',
    key: 'payout_confirmed',
    sortable: false,
  }
]

const {
  data: reportData,
  execute: fetchReport,
} = await useApi(createUrl(`/reports/${route.params.id}/entries`, {
  query: {
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const exportReport = async () => {
  try {
    const data = await $api(`/reports/${route.params.id}/entries`, {
      method: 'GET',
      query: {
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
        export: 'csv',
      },
      responseType: 'blob'
    })

    // Get the filename from the response headers
    const fileName = 'report_amministrativo.csv';

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
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <h5 class="text-h5">
          {{ reportData.report.name }}
        </h5>
      </VCardText>
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
          <!-- Add line -->
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
          >
            Aggiungi riga
          </VBtn>

          <!-- Export -->
          <VBtn
            variant="tonal"
            color="primary"
            prepend-icon="tabler-upload"
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

        <!-- Payout Confirmed -->
        <template #item.payout_confirmed="{ item }">
          <div class="d-flex align-center gap-x-2">
            <AppTextField
              type="number"
              v-model="item.payout_confirmed"
              placeholder="Compenso confermato"
            />
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

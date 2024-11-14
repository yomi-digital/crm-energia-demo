<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'statements',
  },
})

// Data table options
const itemsPerPage = ref(100)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const selectedBrand = ref('')

const router = useRouter()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Brand',
    key: 'name',
    sortable: false,
  },
  {
    title: 'Pratiche',
    key: 'paperworks_count',
    sortable: false,
  },
  {
    title: 'Attivi',
    key: 'plus',
    sortable: false,
  },
  {
    title: 'Storni',
    key: 'minus',
    sortable: false,
  },
  {
    title: 'Valore netto',
    key: 'net',
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
  data: statementData,
  execute: fetchStatement,
} = await useApi(createUrl('/statements', {
  query: {
    itemsPerPage,
    page,
    sortBy,
    orderBy,
    from: fromDate,
    to: toDate,
    brand_id: selectedBrand,
  },
}))

const exportStatement = async () => {
  try {
    const data = await $api(`/statements`, {
      method: 'GET',
      query: {
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
        from: fromDate.value,
        to: toDate.value,
        export: 'csv',
        brand_id: selectedBrand.value,
      },
      responseType: 'blob'
    })

    // Get the filename from the response headers
    const fileName = 'conto_economico.csv';

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
    console.error('Error exporting statement:', error)
    // Handle the error (e.g., show a notification to the user)
  }
}

const entries = computed(() => statementData.value.entries)
const totalEntries = computed(() => statementData.value.totalEntries)

const brands = ref([
  {
    title: 'Tutti',
    value: '',
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
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
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
              v-model="selectedBrand"
              label="Filtra per Brand"
              clearable
              :items="brands"
              placeholder="Seleziona una Brand"
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
            @click="exportStatement"
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
        <!-- Brand -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.name }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Pratiche -->
        <template #item.paperworks_count="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.paperworks_count }}
            </div>
          </div>
        </template>

        <!-- Attivi -->
        <template #item.plus="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              € {{ item.plus }}
            </div>
          </div>
        </template>

        <!-- Storni -->
        <template #item.minus="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              € {{ item.minus }}
            </div>
          </div>
        </template>

        <!-- Valore netto -->
        <template #item.net="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              € {{ item.net }}
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

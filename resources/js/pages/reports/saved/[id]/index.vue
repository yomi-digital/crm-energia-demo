<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'reports-admin',
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
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
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
    const fileName = reportData.value.report.name + '.csv';

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

const isUpdatePayoutDialogVisible = ref(false)
const selectedEntry = ref(null)
const selectedEntryPayoutConfirmed = ref(null)

const updatePayoutConfirmed = (item) => {
  // Should open up a dialog to update the payout confirmed
  selectedEntry.value = item
  isUpdatePayoutDialogVisible.value = true
}

const savePayoutConfirmed = async () => {
  await $api(`/reports/${route.params.id}/entries/${selectedEntry.value.id}/payout-confirmed`, {
    method: 'PUT',
    body: { payout_confirmed: selectedEntryPayoutConfirmed.value },
  })

  isUpdatePayoutDialogVisible.value = false
  selectedEntry.value = null
  selectedEntryPayoutConfirmed.value = null
  fetchReport()
}

const selectedEntryRemove = ref(null)
const isRemoveDialogVisible = ref(false)

const selectEntryForRemove = entry => {
  selectedEntryRemove.value = entry
  isRemoveDialogVisible.value = true
}

const deleteReportEntry = async id => {
  await $api(`/reports/${ route.params.id }/entries/${ id }/delete`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchReport()
}

const isEditNameDialogVisible = ref(false)
const reportNewName = ref(reportData.value.report.name)
const reportNewStatus = ref(reportData.value.report.status)

const saveReportName = async () => {
  await $api(`/reports/${ route.params.id }/update`, { method: 'PUT', body: { name: reportNewName.value, status: reportNewStatus.value } })
  isEditNameDialogVisible.value = false
  fetchReport()
}

const reportStatuses = ref([
  { value: 1, title: 'Bozza' },
  { value: 2, title: 'Confermato' },
  { value: 3, title: 'Inviato' },
])

const reportStatus = computed(() => reportStatuses.value.find(status => status.value === reportData.value.report.status))

const isAddEntryDialogVisible = ref(false)
const addEntryDescription = ref('')
const addEntryPayout = ref(null)

const saveAddEntry = async () => {
  await $api(`/reports/${ route.params.id }/entries/add`, { method: 'POST', body: { description: addEntryDescription.value, payout: addEntryPayout.value } })
  isAddEntryDialogVisible.value = false
  addEntryDescription.value = ''
  addEntryPayout.value = null
  fetchReport()
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <h5 class="text-h5 d-flex align-center gap-x-2">
          <IconBtn @click="isEditNameDialogVisible = true">
            <VIcon color="primary" icon="tabler-pencil" />
          </IconBtn>
          {{ reportData.report.name }}
          <VChip
            variant="tonal"
            color="info"
            label
            size="small"
          >
            {{ reportStatus.title }}
          </VChip>
        </h5>
        <p class="text-body-2">
          Totale compenso confermato: <strong>€ {{ reportData.totalPayoutConfirmed }}</strong>
        </p>
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
            @click="isAddEntryDialogVisible = true"
          >
            Aggiungi riga
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
        density="compact"
        :items="entries"
        :items-length="totalEntries"
        :headers="headers"
        class="text-no-wrap"
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
                v-if="item.product_id"
                :to="{ name: 'configuration-products-id', params: { id: item.product_id } }"
                class="font-weight-medium text-link"
                :title="item.product"
              >
                {{ item.product }}
              </RouterLink>
              <span v-else>
                {{ item.product || 'N/A' }}
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
                {{ item.order_code || 'N/A' }} (#{{ item.paperwork_id }})
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
              {{ item.status || '' }}
            </div>
          </div>
        </template>

        <!-- Payout -->
        <template #item.payout="{ item }">
          <div class="d-flex align-center justify-end gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.payout ? `€ ${item.payout}` : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Payout Confirmed -->
        <template #item.payout_confirmed="{ item }">
          <div class="d-flex align-center justify-end gap-x-2">
            {{ item.payout_confirmed ? `€ ${item.payout_confirmed}` : 'N/A' }}
            <IconBtn @click="updatePayoutConfirmed(item)">
              <VIcon color="primary" icon="tabler-pencil" />
            </IconBtn>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="selectEntryForRemove(item)" v-if="$can('edit', 'reports')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
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
    
    <VDialog
      v-model="isUpdatePayoutDialogVisible"
      width="500"
      v-if="selectedEntry"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isUpdatePayoutDialogVisible = !isUpdatePayoutDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Modifica compenso">
        <VCardText>
          <!-- This should have an input for the payout confirmed -->
          <p>
            Vuoi modificare il compenso di <b>€ {{ selectedEntry.payout }}</b>?
          </p>
          <AppTextField
            v-model="selectedEntryPayoutConfirmed"
            placeholder="Nuovo Compenso"
          />
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="success" @click="savePayoutConfirmed">
            Conferma
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedEntryRemove && $can('edit', 'reports')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Riga">
        <VCardText>
          Sei sicuro di voler eliminare la riga <b>ID Pratica: {{ selectedEntryRemove.order_code || 'N/A' }}</b> (#{{ selectedEntryRemove.paperwork_id }})?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteReportEntry(selectedEntryRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isEditNameDialogVisible"
      width="500"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isEditNameDialogVisible = !isEditNameDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Modifica">
        <VCardText>
          <VRow>
            <VCol cols="12" md="12">
              <AppTextField
                v-model="reportNewName"
                label="Nome"
                placeholder="Nuovo Nome"
              />
            </VCol>
            <VCol cols="12" md="12">
              <AppSelect
                v-model="reportNewStatus"
                label="Stato"
                :items="reportStatuses"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="success" @click="saveReportName">
            Conferma
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isAddEntryDialogVisible"
      width="500"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isAddEntryDialogVisible = !isAddEntryDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Aggiungi Riga">
        <VCardText>
          <VRow>
            <VCol cols="12" md="12">
              <AppTextField
                v-model="addEntryDescription"
                label="Descrizione"
                placeholder="Descrizione"
              />
            </VCol>
            <VCol cols="12" md="12">
              <AppTextField
                v-model="addEntryPayout"
                label="Compenso"
                placeholder="Compenso"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="success" @click="saveAddEntry">
            Aggiungi
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

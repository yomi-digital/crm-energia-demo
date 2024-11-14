<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'reports-admin',
  },
})

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(100)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const selectedUser = ref('')
const selectedStatus = ref('')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Report',
    key: 'name',
  },
  {
    title: 'Account',
    key: 'user_id',
    sortable: false,
  },
  {
    title: 'Stato',
    key: 'status',
  },
  {
    title: 'Creato il',
    key: 'created_at',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]


const {
  data: reportData,
  execute: fetchReports,
} = await useApi(createUrl('/reports', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
    user_id: selectedUser,
    status: selectedStatus,
  },
}))

const entries = computed(() => reportData.value.entries)
const totalEntries = computed(() => reportData.value.totalEntries)

const users = ref([
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
    title: 'Bozza',
    value: '1',
  },
  {
    title: 'Confermato',
    value: '2',
  },
  {
    title: 'Inviato',
    value: '3',
  },
])

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

const statusTest = (status) => {
  return status == '1' ? 'Bozza' : status == '2' ? 'Confermato' : 'Inviato'
}

const selectedReportRemove = ref(null)
const isRemoveDialogVisible = ref(false)

const selectReportForRemove = report => {
  selectedReportRemove.value = report
  isRemoveDialogVisible.value = true
}

const deleteReport = async id => {
  await $api(`/reports/${ id }/delete`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchReports()
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
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
              v-model="selectedStatus"
              label="Filtra per Stato"
              clearable
              :items="statuses"
              placeholder="Seleziona uno Stato"
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
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Cerca"
            />
          </div>
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

        <!-- ID -->
        <template #item.id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                :to="{ name: 'reports-saved-id', params: { id: item.id } }"
                class="font-weight-medium text-link"
                :title="item.id"
              >
                {{ item.id }}
              </RouterLink>
            </div>
          </div>
        </template>

        <!-- Account -->
        <template #item.user_id="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                <RouterLink
                  v-if="item.user_id"
                  :to="{ name: 'admin-users-id', params: { id: item.user_id } }"
                  class="font-weight-medium text-link"
                  :title="item.user.name"
                >
                  {{ item.user.name }} {{ item.user.last_name }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <!-- Nome -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              <RouterLink
                :to="{ name: 'reports-saved-id', params: { id: item.id } }"
                class="font-weight-medium text-link"
                :title="item.name"
              >
                {{ item.name }}
              </RouterLink>
            </div>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ statusTest(item.status) }}
            </div>
          </div>
        </template>

        <!-- Created At -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.created_at }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="selectReportForRemove(item)" v-if="$can('delete', 'reports')">
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
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedReportRemove && $can('delete', 'reports')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Brand">
        <VCardText>
          Sei sicuro di voler eliminare il report <b>{{ selectedReportRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteReport(selectedReportRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-tetti',
  },
})

import AddNewTettoDrawer from '@/views/solar/tetti/AddNewTettoDrawer.vue'
import EditTettoDrawer from '@/views/solar/tetti/EditTettoDrawer.vue'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedTetto = ref()
const selectedTettoRemove = ref()
const selectedStatus = ref('true')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Nome Tipologia',
    key: 'nome_tipologia',
  },
  {
    title: 'Costo Extra kWp',
    key: 'costo_extra_kwp',
  },
  {
    title: 'Note',
    key: 'note',
  },
  {
    title: 'Stato',
    key: 'is_active',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const queryParams = computed(() => {
  const params = {
    q: searchQuery.value,
    itemsPerPage: itemsPerPage.value,
    page: page.value,
    sortBy: sortBy.value,
    orderBy: orderBy.value,
  }
  
  // Aggiungi is_active solo se non è vuoto (non "Tutti")
  if (selectedStatus.value && selectedStatus.value !== '') {
    params.is_active = selectedStatus.value
  }
  
  return params
})

const {
  data: tettiData,
  execute: fetchTetti,
} = await useApi(createUrl('/tipologie-tetto', {
  query: queryParams,
}))

const tetti = computed(() => {
  const data = tettiData.value
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalTetti = computed(() => {
  const data = tettiData.value
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || 0
})

const isAddNewTettoDrawerVisible = ref(false)
const isEditTettoDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewTetto = async tettoData => {
  await $api('/tipologie-tetto', {
    method: 'POST',
    body: tettoData,
  })

  fetchTetti()
}

const deleteTetto = async id => {
  const tettoId = typeof id === 'object' ? (id.id_voce || id.id) : id
  await $api(`/tipologie-tetto/${ tettoId }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchTetti()
}

const updateTetto = async tettoData => {
  await $api(`/tipologie-tetto/${ tettoData.id_voce || tettoData.id }`, {
    method: 'PUT',
    body: tettoData,
  })

  fetchTetti()
}

const toggleTettoStatus = async (item) => {
  const newStatus = !item.is_active
  await $api(`/tipologie-tetto/${ item.id_voce || item.id }`, {
    method: 'PUT',
    body: {
      is_active: newStatus,
    },
  })
  fetchTetti()
}

const selectTettoForRemove = tetto => {
  selectedTettoRemove.value = tetto
  isRemoveDialogVisible.value = true
}

const editTetto = tetto => {
  selectedTetto.value = tetto
  isEditTettoDrawerVisible.value = true
}

const statuses = [
  { value: 'true', title: 'Attivi' },
  { value: 'false', title: 'Disattivi' },
  { value: 'all', title: 'Tutti' },
]
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedStatus"
              label="Filtra per Stato"
              clearable
              :items="statuses"
              placeholder="Seleziona uno stato"
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

          <!-- 👉 Add Tetto button -->
          <VBtn
            v-if="$can('create', 'solar-tetti')"
            prepend-icon="tabler-plus"
            @click="isAddNewTettoDrawerVisible = true"
          >
            Aggiungi Tetto
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="tetti"
        :items-length="totalTetti"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Nome Tipologia -->
        <template #item.nome_tipologia="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.nome_tipologia }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Costo Extra kWp -->
        <template #item.costo_extra_kwp="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.costo_extra_kwp ? `€${item.costo_extra_kwp}` : '-' }}
            </div>
          </div>
        </template>

        <!-- Note -->
        <template #item.note="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.note || '-' }}
            </div>
          </div>
        </template>

        <!-- 👉 Enabled -->
        <template #item.is_active="{ item }">
          <VSwitch
            :model-value="item.is_active"
            @update:model-value="toggleTettoStatus(item)"
            color="success"
            hide-details
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editTetto(item)" v-if="$can('edit', 'solar-tetti')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectTettoForRemove(item)" v-if="$can('delete', 'solar-tetti')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalTetti"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Tetto -->
    <AddNewTettoDrawer
      v-if="$can('create', 'solar-tetti')"
      v-model:isDrawerOpen="isAddNewTettoDrawerVisible"
      @tetto-data="addNewTetto"
    />
    <!-- 👉 Edit Tetto -->
    <EditTettoDrawer
      v-if="selectedTetto && $can('edit', 'solar-tetti')"
      v-model:isDrawerOpen="isEditTettoDrawerVisible"
      @tetto-data="updateTetto"
      :tetto="selectedTetto"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedTettoRemove && $can('delete', 'solar-tetti')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Tetto">
        <VCardText>
          Sei sicuro di voler eliminare il tetto <b>{{ selectedTettoRemove.nome_tipologia }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteTetto(selectedTettoRemove.id_voce || selectedTettoRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>


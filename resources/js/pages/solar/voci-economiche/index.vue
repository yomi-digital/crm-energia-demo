<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-voci-economiche',
  },
})

import AddNewVoceEconomicaDrawer from '@/views/solar/voci-economiche/AddNewVoceEconomicaDrawer.vue'
import EditVoceEconomicaDrawer from '@/views/solar/voci-economiche/EditVoceEconomicaDrawer.vue'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedVoceEconomica = ref()
const selectedVoceEconomicaRemove = ref()
const selectedStatus = ref('true')
const selectedTipoVoce = ref('')
const selectedCustomerType = ref('')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Nome Voce',
    key: 'nome_voce',
  },
  {
    title: 'Tipo Voce',
    key: 'tipo_voce',
  },
  {
    title: 'Tipo Valore',
    key: 'tipo_valore',
  },
  {
    title: 'Valore Default',
    key: 'valore_default',
  },
  {
    title: 'Tipo Cliente',
    key: 'tipo_cliente',
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
  
  // Aggiungi tipo_voce solo se non è vuoto
  if (selectedTipoVoce.value && selectedTipoVoce.value !== '') {
    params.tipo_voce = selectedTipoVoce.value
  }
  
  // Aggiungi customer_type solo se non è vuoto
  if (selectedCustomerType.value && selectedCustomerType.value !== '') {
    params.customer_type = selectedCustomerType.value
  }
  
  // Aggiungi is_active solo se non è vuoto (non "Tutti")
  if (selectedStatus.value && selectedStatus.value !== '') {
    params.is_active = selectedStatus.value
  }
  
  return params
})

const {
  data: vociEconomicheData,
  execute: fetchVociEconomiche,
} = await useApi(createUrl('/voci-economiche', {
  query: queryParams,
}))

const vociEconomiche = computed(() => {
  const data = vociEconomicheData.value
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalVociEconomiche = computed(() => {
  const data = vociEconomicheData.value
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || 0
})

const isAddNewVoceEconomicaDrawerVisible = ref(false)
const isEditVoceEconomicaDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewVoceEconomica = async voceEconomicaData => {
  await $api('/voci-economiche', {
    method: 'POST',
    body: voceEconomicaData,
  })

  fetchVociEconomiche()
}

const deleteVoceEconomica = async id => {
  const voceId = typeof id === 'object' ? (id.id_voce || id.id) : id
  await $api(`/voci-economiche/${ voceId }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchVociEconomiche()
}

const updateVoceEconomica = async voceEconomicaData => {
  await $api(`/voci-economiche/${ voceEconomicaData.id_voce || voceEconomicaData.id }`, {
    method: 'PUT',
    body: voceEconomicaData,
  })

  fetchVociEconomiche()
}

const toggleVoceEconomicaStatus = async (item) => {
  const newStatus = !item.is_active
  await $api(`/voci-economiche/${ item.id_voce || item.id }`, {
    method: 'PUT',
    body: {
      is_active: newStatus,
    },
  })
  fetchVociEconomiche()
}

const selectVoceEconomicaForRemove = voceEconomica => {
  selectedVoceEconomicaRemove.value = voceEconomica
  isRemoveDialogVisible.value = true
}

const editVoceEconomica = voceEconomica => {
  selectedVoceEconomica.value = voceEconomica
  isEditVoceEconomicaDrawerVisible.value = true
}

const statuses = [
  { value: 'true', title: 'Attivi' },
  { value: 'false', title: 'Disattivi' },
  { value: 'all', title: 'Tutti' },
]

const tipoVoci = [
  { value: '', title: 'Tutti' },
  { value: 'incentivo', title: 'Incentivo' },
  {value: 'sconto', title: 'Sconto'},
  { value: 'costo', title: 'Costo' },
]

const customerTypes = [
  { value: '', title: 'Tutti' },
  { value: 'RESIDENZIALE', title: 'Residenziale' },
  { value: 'BUSINESS', title: 'Business' },
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
          <!-- 👉 Select Tipo Voce -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedTipoVoce"
              label="Filtra per Tipo Voce"
              clearable
              :items="tipoVoci"
              placeholder="Seleziona tipo voce"
            />
          </VCol>

          <!-- 👉 Select Customer Type -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedCustomerType"
              label="Filtra per Tipo Cliente"
              clearable
              :items="customerTypes"
              placeholder="Seleziona tipo cliente"
            />
          </VCol>

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

          <!-- 👉 Add Voce Economica button -->
          <VBtn
            v-if="$can('create', 'solar-voci-economiche')"
            prepend-icon="tabler-plus"
            @click="isAddNewVoceEconomicaDrawerVisible = true"
          >
            Aggiungi Voce Economica
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="vociEconomiche"
        :items-length="totalVociEconomiche"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Nome Voce -->
        <template #item.nome_voce="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.nome_voce }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Tipo Voce -->
        <template #item.tipo_voce="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VChip
              :color="item.tipo_voce === 'incentivo' ? 'success' : 'warning'"
              size="small"
            >
              {{ item.tipo_voce }}
            </VChip>
          </div>
        </template>

        <!-- Tipo Valore -->
        <template #item.tipo_valore="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.tipo_valore }}
            </div>
          </div>
        </template>

        <!-- Valore Default -->
        <template #item.valore_default="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.valore_default }}{{ item.tipo_valore === '%' ? '%' : '' }}
            </div>
          </div>
        </template>

        <!-- Tipo Cliente -->
        <template #item.tipo_cliente="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VChip
              v-for="tipo in (item.tipo_cliente || [])"
              :key="tipo"
              class="me-1"
              size="small"
            >
              {{ tipo }}
            </VChip>
          </div>
        </template>

        <!-- 👉 Enabled -->
        <template #item.is_active="{ item }">
          <VSwitch
            :model-value="item.is_active"
            @update:model-value="toggleVoceEconomicaStatus(item)"
            color="success"
            hide-details
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editVoceEconomica(item)" v-if="$can('edit', 'solar-voci-economiche')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectVoceEconomicaForRemove(item)" v-if="$can('delete', 'solar-voci-economiche')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalVociEconomiche"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Voce Economica -->
    <AddNewVoceEconomicaDrawer
      v-if="$can('create', 'solar-voci-economiche')"
      v-model:isDrawerOpen="isAddNewVoceEconomicaDrawerVisible"
      @voce-economica-data="addNewVoceEconomica"
    />
    <!-- 👉 Edit Voce Economica -->
    <EditVoceEconomicaDrawer
      v-if="selectedVoceEconomica && $can('edit', 'solar-voci-economiche')"
      v-model:isDrawerOpen="isEditVoceEconomicaDrawerVisible"
      @voce-economica-data="updateVoceEconomica"
      :voce-economica="selectedVoceEconomica"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedVoceEconomicaRemove && $can('delete', 'solar-voci-economiche')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Voce Economica">
        <VCardText>
          Sei sicuro di voler eliminare la voce economica <b>{{ selectedVoceEconomicaRemove.nome_voce }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteVoceEconomica(selectedVoceEconomicaRemove.id_voce || selectedVoceEconomicaRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>


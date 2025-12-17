<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-pagamenti',
  },
})

import AddNewPagamentoDrawer from '@/views/solar/pagamenti/AddNewPagamentoDrawer.vue'
import EditPagamentoDrawer from '@/views/solar/pagamenti/EditPagamentoDrawer.vue'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedPagamento = ref()
const selectedPagamentoRemove = ref()
const selectedStatus = ref('true')
const selectedCustomerType = ref('')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Nome Modalità',
    key: 'nome_modalita',
  },
  {
    title: 'Descrizione',
    key: 'descrizione',
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
    itemsPerPage: itemsPerPage.value,
    page: page.value,
    sortBy: sortBy.value,
    orderBy: orderBy.value,
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
  data: pagamentiData,
  execute: fetchPagamenti,
} = await useApi(createUrl('/modalita-pagamento', {
  query: queryParams,
}))

const pagamenti = computed(() => {
  const data = pagamentiData.value
  if (!data) return []
  
  // Se i dati sono dentro modalita_pagamento
  if (data.modalita_pagamento) {
    const modalitaPagamento = data.modalita_pagamento
    if (Array.isArray(modalitaPagamento)) {
      return modalitaPagamento
    }
    return modalitaPagamento?.data || []
  }
  
  // Fallback per compatibilità con strutture vecchie
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalPagamenti = computed(() => {
  const data = pagamentiData.value
  if (!data) return 0
  
  // Se i dati sono dentro modalita_pagamento
  if (data.modalita_pagamento) {
    const modalitaPagamento = data.modalita_pagamento
    if (Array.isArray(modalitaPagamento)) {
      return modalitaPagamento.length
    }
    return modalitaPagamento?.total || 0
  }
  
  // Fallback per compatibilità con strutture vecchie
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || 0
})

const isAddNewPagamentoDrawerVisible = ref(false)
const isEditPagamentoDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewPagamento = async pagamentoData => {
  await $api('/modalita-pagamento', {
    method: 'POST',
    body: pagamentoData,
  })

  fetchPagamenti()
}

const deletePagamento = async id => {
  const pagamentoId = typeof id === 'object' ? (id.id_modalita || id.id) : id
  await $api(`/modalita-pagamento/${ pagamentoId }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchPagamenti()
}

const updatePagamento = async pagamentoData => {
  await $api(`/modalita-pagamento/${ pagamentoData.id_modalita || pagamentoData.id }`, {
    method: 'PUT',
    body: pagamentoData,
  })

  fetchPagamenti()
}

const togglePagamentoStatus = async (item) => {
  const newStatus = !item.is_active
  await $api(`/modalita-pagamento/${ item.id_modalita || item.id }`, {
    method: 'PUT',
    body: {
      is_active: newStatus,
    },
  })
  fetchPagamenti()
}

// Il watch non è più necessario perché queryParams è computed e si aggiorna automaticamente

const selectPagamentoForRemove = pagamento => {
  selectedPagamentoRemove.value = pagamento
  isRemoveDialogVisible.value = true
}

const editPagamento = pagamento => {
  selectedPagamento.value = pagamento
  isEditPagamentoDrawerVisible.value = true
}

const statuses = [
  { value: 'true', title: 'Attivi' },
  { value: 'false', title: 'Disattivi' },
  { value: 'all', title: 'Tutti' },
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
          <!-- 👉 Add Pagamento button -->
          <VBtn
            v-if="$can('create', 'solar-pagamenti')"
            prepend-icon="tabler-plus"
            @click="isAddNewPagamentoDrawerVisible = true"
          >
            Aggiungi Pagamento
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="pagamenti"
        :items-length="totalPagamenti"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Nome Modalità -->
        <template #item.nome_modalita="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.nome_modalita }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Descrizione -->
        <template #item.descrizione="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.descrizione || '-' }}
            </div>
          </div>
        </template>

        <!-- Tipo Cliente -->
        <template #item.tipo_cliente="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VChip
              v-for="app in (item.applicabilita || [])"
              :key="app.tipo_cliente"
              class="me-1"
              size="small"
            >
              {{ app.tipo_cliente }}
            </VChip>
          </div>
        </template>

        <!-- 👉 Enabled -->
        <template #item.is_active="{ item }">
          <VSwitch
            :model-value="item.is_active"
            @update:model-value="togglePagamentoStatus(item)"
            color="success"
            hide-details
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editPagamento(item)" v-if="$can('edit', 'solar-pagamenti')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectPagamentoForRemove(item)" v-if="$can('delete', 'solar-pagamenti')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPagamenti"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Pagamento -->
    <AddNewPagamentoDrawer
      v-if="$can('create', 'solar-pagamenti')"
      v-model:isDrawerOpen="isAddNewPagamentoDrawerVisible"
      @pagamento-data="addNewPagamento"
    />
    <!-- 👉 Edit Pagamento -->
    <EditPagamentoDrawer
      v-if="selectedPagamento && $can('edit', 'solar-pagamenti')"
      v-model:isDrawerOpen="isEditPagamentoDrawerVisible"
      @pagamento-data="updatePagamento"
      :pagamento="selectedPagamento"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedPagamentoRemove && $can('delete', 'solar-pagamenti')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Pagamento">
        <VCardText>
          Sei sicuro di voler eliminare la modalità di pagamento <b>{{ selectedPagamentoRemove.nome_modalita }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deletePagamento(selectedPagamentoRemove.id_modalita || selectedPagamentoRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>


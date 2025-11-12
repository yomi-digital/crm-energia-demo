<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-coefficienti-produzione',
  },
})

import EditCoefficienteDrawer from '@/views/solar/coefficienti-produzione/EditCoefficienteDrawer.vue'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(50)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedCoefficiente = ref()
const selectedCoefficienteRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Area Geografica',
    key: 'area_geografica',
  },
  {
    title: 'Esposizione',
    key: 'esposizione',
  },
  {
    title: 'Coefficiente kWh/kWp',
    key: 'coefficiente_kwh_kwp',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: coefficientiData,
  execute: fetchCoefficienti,
} = await useApi(createUrl('/coefficienti-produzione', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const coefficienti = computed(() => {
  const data = coefficientiData.value
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalCoefficienti = computed(() => {
  const data = coefficientiData.value
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || coefficienti.value.length
})

const isAddNewCoefficienteDrawerVisible = ref(false)
const isEditCoefficienteDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewCoefficiente = async coefficienteData => {
  // Nota: l'API non supporta POST (restituisce 405)
  // Rimuovere il drawer di aggiunta o implementare la logica lato backend
  fetchCoefficienti()
}

const deleteCoefficiente = async id => {
  // Nota: l'API non supporta DELETE (restituisce 405)
  // Rimuovere il dialog di eliminazione o implementare la logica lato backend
  isRemoveDialogVisible.value = false
  fetchCoefficienti()
}

const updateCoefficiente = async coefficienteData => {
  await $api(`/coefficienti-produzione/${ coefficienteData.id }`, {
    method: 'PUT',
    body: {
      coefficiente_kwh_kwp: coefficienteData.coefficiente_kwh_kwp,
    },
  })

  fetchCoefficienti()
}

const selectCoefficienteForRemove = coefficiente => {
  selectedCoefficienteRemove.value = coefficiente
  isRemoveDialogVisible.value = true
}

const editCoefficiente = coefficiente => {
  selectedCoefficiente.value = coefficiente
  isEditCoefficienteDrawerVisible.value = true
}

watch(searchQuery, () => {
  fetchCoefficienti()
})
</script>

<template>
  <section>
    <VCard class="mb-6">
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

          <!-- Nota: POST non supportato dall'API -->
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="coefficienti"
        :items-length="totalCoefficienti"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Area Geografica -->
        <template #item.area_geografica="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.area_geografica }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Esposizione -->
        <template #item.esposizione="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.esposizione }}
            </div>
          </div>
        </template>

        <!-- Coefficiente kWh/kWp -->
        <template #item.coefficiente_kwh_kwp="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.coefficiente_kwh_kwp }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editCoefficiente(item)" v-if="$can('edit', 'solar-coefficienti-produzione')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <!-- Nota: DELETE non supportato dall'API -->
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalCoefficienti"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- Nota: POST non supportato dall'API -->
    <!-- 👉 Edit Coefficiente -->
    <EditCoefficienteDrawer
      v-if="selectedCoefficiente && $can('edit', 'solar-coefficienti-produzione')"
      v-model:isDrawerOpen="isEditCoefficienteDrawerVisible"
      @coefficiente-data="updateCoefficiente"
      :coefficiente="selectedCoefficiente"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedCoefficienteRemove && $can('delete', 'solar-coefficienti-produzione')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Coefficiente">
        <VCardText>
          Sei sicuro di voler eliminare il coefficiente <b>{{ selectedCoefficienteRemove.area_geografica }} - {{ selectedCoefficienteRemove.esposizione }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteCoefficiente(selectedCoefficienteRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>


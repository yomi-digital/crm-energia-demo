<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-prodotti',
  },
})

import AddNewProdottoDrawer from '@/views/solar/prodotti/AddNewProdottoDrawer.vue'
import EditProdottoDrawer from '@/views/solar/prodotti/EditProdottoDrawer.vue'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedProdotto = ref()
const selectedProdottoRemove = ref()
const selectedStatus = ref('true')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Codice Prodotto',
    key: 'codice_prodotto',
  },
  {
    title: 'Descrizione',
    key: 'descrizione',
  },
  {
    title: 'Categoria',
    key: 'fk_categoria',
  },
  {
    title: 'Potenza kWp',
    key: 'potenza_kwp',
  },
  {
    title: 'Capacità kWh',
    key: 'capacita_kwh',
  },
  {
    title: 'Prezzo Base',
    key: 'prezzo_base',
  },
  {
    title: 'Potenza Inverter',
    key: 'potenza_inverter',
  },
  {
    title: 'Marca',
    key: 'marca',
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
  data: prodottiData,
  execute: fetchProdotti,
} = await useApi(createUrl('/prodotti-fotovoltaico', {
  query: queryParams,
}))

const prodotti = computed(() => {
  const data = prodottiData.value
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalProdotti = computed(() => {
  const data = prodottiData.value
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || 0
})

const isAddNewProdottoDrawerVisible = ref(false)
const isEditProdottoDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

// Toast variables
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const addNewProdotto = async prodottoData => {
  try {
    await $api('/prodotti-fotovoltaico', {
      method: 'POST',
      body: prodottoData,
    })

    snackbarMessage.value = 'Prodotto creato con successo!'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchProdotti()
  } catch (error) {
    console.error(error)
    
    // Extract error message from API response
    let errorMessage = 'Errore durante la creazione del prodotto'
    
    if (error?.data) {
      // Check for validation errors (422)
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      // Check for error message
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      // Check for error field
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const deleteProdotto = async id => {
  try {
    const prodottoId = typeof id === 'object' ? (id.id_prodotto || id.id) : id
    await $api(`/prodotti-fotovoltaico/${ prodottoId }`, { method: 'DELETE' })
    
    isRemoveDialogVisible.value = false

    snackbarMessage.value = 'Prodotto eliminato con successo!'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchProdotti()
  } catch (error) {
    console.error(error)
    
    // Extract error message from API response
    let errorMessage = 'Errore durante l\'eliminazione del prodotto'
    
    if (error?.data) {
      // Check for validation errors (422)
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      // Check for error message
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      // Check for error field
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
    
    // Chiudi il dialog anche in caso di errore
    isRemoveDialogVisible.value = false
  }
}

const updateProdotto = async prodottoData => {
  try {
    await $api(`/prodotti-fotovoltaico/${ prodottoData.id_prodotto || prodottoData.id }`, {
      method: 'PUT',
      body: prodottoData,
    })

    snackbarMessage.value = 'Prodotto modificato con successo!'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchProdotti()
  } catch (error) {
    console.error(error)
    
    // Extract error message from API response
    let errorMessage = 'Errore durante la modifica del prodotto'
    
    if (error?.data) {
      // Check for validation errors (422)
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      // Check for error message
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      // Check for error field
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const toggleProdottoStatus = async (item) => {
  try {
    const newStatus = !item.is_active
    await $api(`/prodotti-fotovoltaico/${ item.id_prodotto || item.id }`, {
      method: 'PUT',
      body: {
        is_active: newStatus,
      },
    })
    
    snackbarMessage.value = `Prodotto ${newStatus ? 'abilitato' : 'disabilitato'} con successo`
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchProdotti()
  } catch (error) {
    console.error(error)
    snackbarMessage.value = 'Errore durante l\'aggiornamento dello stato'
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const selectProdottoForRemove = prodotto => {
  selectedProdottoRemove.value = prodotto
  isRemoveDialogVisible.value = true
}

const editProdotto = prodotto => {
  selectedProdotto.value = prodotto
  isEditProdottoDrawerVisible.value = true
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

          <!-- 👉 Add Prodotto button -->
          <VBtn
            v-if="$can('create', 'solar-prodotti')"
            prepend-icon="tabler-plus"
            @click="isAddNewProdottoDrawerVisible = true"
          >
            Aggiungi Prodotto
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="prodotti"
        :items-length="totalProdotti"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Codice Prodotto -->
        <template #item.codice_prodotto="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-high-emphasis text-body-1">
                {{ item.codice_prodotto }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Descrizione -->
        <template #item.descrizione="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.descrizione }}
            </div>
          </div>
        </template>

        <!-- Categoria -->
        <template #item.fk_categoria="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.categoria?.nome_categoria || '-' }}
            </div>
          </div>
        </template>

        <!-- Potenza kWp -->
        <template #item.potenza_kwp="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.potenza_kwp }} kWp
            </div>
          </div>
        </template>

        <!-- Capacità kWh -->
        <template #item.capacita_kwh="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.capacita_kwh }} kWh
            </div>
          </div>
        </template>

        <!-- Prezzo Base -->
        <template #item.prezzo_base="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              €{{ item.prezzo_base }}
            </div>
          </div>
        </template>

        <!-- Potenza Inverter -->
        <template #item.potenza_inverter="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.potenza_inverter }} kW
            </div>
          </div>
        </template>

        <!-- Marca -->
        <template #item.marca="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.marca }}
            </div>
          </div>
        </template>

        <!-- 👉 Enabled -->
        <template #item.is_active="{ item }">
          <VSwitch
            :model-value="item.is_active"
            @update:model-value="toggleProdottoStatus(item)"
            color="success"
            hide-details
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="toggleProdottoStatus(item)" v-if="$can('edit', 'solar-prodotti')">
            <VIcon :color="item.is_active ? 'warning' : 'success'" :icon="item.is_active ? 'tabler-square-x' : 'tabler-square-check'" />
            <VTooltip
              activator="parent"
              location="top"
            >
              {{ item.is_active ? 'Disabilita' : 'Abilita' }}
            </VTooltip>
          </IconBtn>

          <IconBtn @click="editProdotto(item)" v-if="$can('edit', 'solar-prodotti')">
            <VIcon icon="tabler-pencil" />
            <VTooltip
              activator="parent"
              location="top"
            >
              Modifica
            </VTooltip>
          </IconBtn>

          <IconBtn @click="selectProdottoForRemove(item)" v-if="$can('delete', 'solar-prodotti')">
            <VIcon color="error" icon="tabler-trash" />
            <VTooltip
              activator="parent"
              location="top"
            >
              Elimina
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalProdotti"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Prodotto -->
    <AddNewProdottoDrawer
      v-if="$can('create', 'solar-prodotti')"
      v-model:isDrawerOpen="isAddNewProdottoDrawerVisible"
      @prodotto-data="addNewProdotto"
    />
    <!-- 👉 Edit Prodotto -->
    <EditProdottoDrawer
      v-if="selectedProdotto && $can('edit', 'solar-prodotti')"
      v-model:isDrawerOpen="isEditProdottoDrawerVisible"
      @prodotto-data="updateProdotto"
      :prodotto="selectedProdotto"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedProdottoRemove && $can('delete', 'solar-prodotti')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Prodotto">
        <VCardText>
          Sei sicuro di voler eliminare il prodotto <b>{{ selectedProdottoRemove.codice_prodotto }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteProdotto(selectedProdottoRemove.id_prodotto || selectedProdottoRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- 👉 Toast Notification -->
    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </section>
</template>


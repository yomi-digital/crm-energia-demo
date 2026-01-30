<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-listini',
  },
})

import AddNewListinoDrawer from '@/views/solar/listini/AddNewListinoDrawer.vue'
import EditListinoDrawer from '@/views/solar/listini/EditListinoDrawer.vue'

// 👉 Store
const route = useRoute()
const router = useRouter()
const searchQuery = ref(route.query.q || '')

// Data table options
const itemsPerPage = ref(Number(route.query.itemsPerPage) || 25)
const page = ref(Number(route.query.page) || 1)
const sortBy = ref(route.query.sortBy)
const orderBy = ref(route.query.orderBy)
const selectedListino = ref()
const selectedListinoRemove = ref()
const selectedStatus = ref(route.query.is_active || 'true')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Update URL on filter change
watch([searchQuery, itemsPerPage, page, sortBy, orderBy, selectedStatus], () => {
  router.replace({
    query: {
      ...route.query,
      q: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
      is_active: selectedStatus.value,
    }
  })
})

// Headers
const headers = [
  {
    title: 'Nome',
    key: 'nome',
  },
  {
    title: 'Tipo Cliente',
    key: 'tipo_cliente',
  },
  {
    title: 'Tipo Fase',
    key: 'tipo_fase',
  },
  {
    title: 'Descrizione',
    key: 'descrizione',
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
  
  if (selectedStatus.value && selectedStatus.value !== '') {
    params.is_active = selectedStatus.value
  }
  
  return params
})

const {
  data: listiniData,
  execute: fetchListini,
} = await useApi(createUrl('/listini', {
  query: queryParams,
}))

const listini = computed(() => {
  const data = listiniData.value
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalListini = computed(() => {
  const data = listiniData.value
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || 0
})

const isAddNewListinoDrawerVisible = ref(false)
const isEditListinoDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

// Toast variables
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const addNewListino = async listinoData => {
  try {
    await $api('/listini', {
      method: 'POST',
      body: listinoData,
    })

    snackbarMessage.value = 'Listino creato con successo!'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchListini()
  } catch (error) {
    console.error(error)
    
    let errorMessage = 'Errore durante la creazione del listino'
    
    if (error?.data) {
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const deleteListino = async id => {
  try {
    await $api(`/listini/${id}`, { method: 'DELETE' })
    
    isRemoveDialogVisible.value = false

    snackbarMessage.value = 'Listino eliminato con successo!'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchListini()
  } catch (error) {
    console.error(error)
    
    let errorMessage = 'Errore durante l\'eliminazione del listino'
    
    if (error?.data) {
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
    
    isRemoveDialogVisible.value = false
  }
}

const updateListino = async listinoData => {
  try {
    await $api(`/listini/${listinoData.id}`, {
      method: 'PUT',
      body: listinoData,
    })

    snackbarMessage.value = 'Listino modificato con successo!'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchListini()
  } catch (error) {
    console.error(error)
    
    let errorMessage = 'Errore durante la modifica del listino'
    
    if (error?.data) {
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const toggleListinoStatus = async (item) => {
  try {
    const newStatus = !item.is_active
    await $api(`/listini/${item.id}`, {
      method: 'PUT',
      body: {
        is_active: newStatus,
      },
    })
    
    snackbarMessage.value = `Listino ${newStatus ? 'abilitato' : 'disabilitato'} con successo`
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    fetchListini()
  } catch (error) {
    console.error(error)
    snackbarMessage.value = 'Errore durante l\'aggiornamento dello stato'
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const selectListinoForRemove = listino => {
  selectedListinoRemove.value = listino
  isRemoveDialogVisible.value = true
}

const editListino = listino => {
  selectedListino.value = listino
  isEditListinoDrawerVisible.value = true
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
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Cerca"
            />
          </div>

          <VBtn
            v-if="$can('create', 'solar-listini')"
            prepend-icon="tabler-plus"
            @click="isAddNewListinoDrawerVisible = true"
          >
            Aggiungi Listino
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="listini"
        :items-length="totalListini"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <template #item.nome="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-high-emphasis text-body-1">
                {{ item.nome }}
              </h6>
            </div>
          </div>
        </template>

        <template #item.tipo_cliente="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.tipo_cliente || '-' }}
            </div>
          </div>
        </template>

        <template #item.tipo_fase="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.tipo_fase || '-' }}
            </div>
          </div>
        </template>

        <template #item.descrizione="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.descrizione || '-' }}
            </div>
          </div>
        </template>

        <template #item.is_active="{ item }">
          <VSwitch
            :model-value="item.is_active"
            @update:model-value="toggleListinoStatus(item)"
            color="success"
            hide-details
          />
        </template>

        <template #item.actions="{ item }">
          <IconBtn @click="toggleListinoStatus(item)" v-if="$can('edit', 'solar-listini')">
            <VIcon :color="item.is_active ? 'warning' : 'success'" :icon="item.is_active ? 'tabler-square-x' : 'tabler-square-check'" />
            <VTooltip
              activator="parent"
              location="top"
            >
              {{ item.is_active ? 'Disabilita' : 'Abilita' }}
            </VTooltip>
          </IconBtn>

          <IconBtn @click="editListino(item)" v-if="$can('edit', 'solar-listini')">
            <VIcon icon="tabler-pencil" />
            <VTooltip
              activator="parent"
              location="top"
            >
              Modifica
            </VTooltip>
          </IconBtn>

          <IconBtn @click="selectListinoForRemove(item)" v-if="$can('delete', 'solar-listini')">
            <VIcon color="error" icon="tabler-trash" />
            <VTooltip
              activator="parent"
              location="top"
            >
              Elimina
            </VTooltip>
          </IconBtn>
        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalListini"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <AddNewListinoDrawer
      v-if="$can('create', 'solar-listini')"
      v-model:isDrawerOpen="isAddNewListinoDrawerVisible"
      @listino-data="addNewListino"
    />

    <EditListinoDrawer
      v-if="selectedListino && $can('edit', 'solar-listini')"
      v-model:isDrawerOpen="isEditListinoDrawerVisible"
      @listino-data="updateListino"
      :listino="selectedListino"
    />

    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedListinoRemove && $can('delete', 'solar-listini')"
    >
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <VCard title="Elimina Listino">
        <VCardText>
          Sei sicuro di voler eliminare il listino <b>{{ selectedListinoRemove.nome }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteListino(selectedListinoRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

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

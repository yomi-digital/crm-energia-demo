<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'solar-categorie',
  },
})

import AddNewCategoriaDrawer from '@/views/solar/categorie/AddNewCategoriaDrawer.vue'
import EditCategoriaDrawer from '@/views/solar/categorie/EditCategoriaDrawer.vue'

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedCategoria = ref()
const selectedCategoriaRemove = ref()
const selectedStatus = ref('true')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Nome Categoria',
    key: 'nome_categoria',
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
  
  // Aggiungi is_active solo se non è vuoto (non "Tutti")
  if (selectedStatus.value && selectedStatus.value !== '') {
    params.is_active = selectedStatus.value
  }
  
  return params
})

const {
  data: categorieData,
  execute: fetchCategorie,
} = await useApi(createUrl('/product-categories', {
  query: queryParams,
}))

const categorie = computed(() => {
  const data = categorieData.value
  if (Array.isArray(data)) {
    return data
  }
  return data?.data || []
})
const totalCategorie = computed(() => {
  const data = categorieData.value
  if (Array.isArray(data)) {
    return data.length
  }
  return data?.total || 0
})

const isAddNewCategoriaDrawerVisible = ref(false)
const isEditCategoriaDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewCategoria = async categoriaData => {
  await $api('/product-categories', {
    method: 'POST',
    body: categoriaData,
  })

  fetchCategorie()
}

const deleteCategoria = async id => {
  const categoriaId = typeof id === 'object' ? (id.id_categoria || id.id) : id
  await $api(`/product-categories/${ categoriaId }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchCategorie()
}

const updateCategoria = async categoriaData => {
  await $api(`/product-categories/${ categoriaData.id_categoria || categoriaData.id }`, {
    method: 'PUT',
    body: categoriaData,
  })

  fetchCategorie()
}

const toggleCategoriaStatus = async (item) => {
  const newStatus = !item.is_active
  await $api(`/product-categories/${ item.id_categoria || item.id }`, {
    method: 'PUT',
    body: {
      is_active: newStatus,
    },
  })
  fetchCategorie()
}

const selectCategoriaForRemove = categoria => {
  selectedCategoriaRemove.value = categoria
  isRemoveDialogVisible.value = true
}

const editCategoria = categoria => {
  selectedCategoria.value = categoria
  isEditCategoriaDrawerVisible.value = true
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

          <!-- 👉 Add Categoria button -->
          <VBtn
            v-if="$can('create', 'solar-categorie')"
            prepend-icon="tabler-plus"
            @click="isAddNewCategoriaDrawerVisible = true"
          >
            Aggiungi Categoria
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="categorie"
        :items-length="totalCategorie"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Nome Categoria -->
        <template #item.nome_categoria="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.nome_categoria }}
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

        <!-- 👉 Enabled -->
        <template #item.is_active="{ item }">
          <VSwitch
            :model-value="item.is_active"
            @update:model-value="toggleCategoriaStatus(item)"
            color="success"
            hide-details
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editCategoria(item)" v-if="$can('edit', 'solar-categorie')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectCategoriaForRemove(item)" v-if="$can('delete', 'solar-categorie')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalCategorie"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Categoria -->
    <AddNewCategoriaDrawer
      v-if="$can('create', 'solar-categorie')"
      v-model:isDrawerOpen="isAddNewCategoriaDrawerVisible"
      @categoria-data="addNewCategoria"
    />
    <!-- 👉 Edit Categoria -->
    <EditCategoriaDrawer
      v-if="selectedCategoria && $can('edit', 'solar-categorie')"
      v-model:isDrawerOpen="isEditCategoriaDrawerVisible"
      @categoria-data="updateCategoria"
      :categoria="selectedCategoria"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedCategoriaRemove && $can('delete', 'solar-categorie')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Categoria">
        <VCardText>
          Sei sicuro di voler eliminare la categoria <b>{{ selectedCategoriaRemove.nome_categoria }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteCategoria(selectedCategoriaRemove.id_categoria || selectedCategoriaRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>


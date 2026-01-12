<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'brands',
  },
})

import AddNewBrandDrawer from '@/views/configuration/brands/AddNewBrandDrawer.vue';
import EditBrandDrawer from '@/views/configuration/brands/EditBrandDrawer.vue';

// Verifica se l'utente è backoffice
const loggedInUser = useCookie('userData').value
const isBackoffice = loggedInUser?.roles?.some(role => role.name === 'backoffice')

// 👉 Store
const route = useRoute()
const router = useRouter()
const searchQuery = ref(route.query.q || '')

// Data table options
const itemsPerPage = ref(Number(route.query.itemsPerPage) || 25)
const page = ref(Number(route.query.page) || 1)
const sortBy = ref(route.query.sortBy)
const orderBy = ref(route.query.orderBy)
const selectedBrand = ref()
const selectedBrandRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const headers = [
  {
    title: 'Brand',
    key: 'name',
  },
  {
    title: 'Tipo',
    key: 'type',
  },
  {
    title: 'Settore',
    key: 'category',
  },
  {
    title: 'Note',
    key: 'notes',
  },
  {
    title: 'N. Prodotti',
    key: 'products_count',
  },
  {
    title: 'Stato',
    key: 'enabled',
  },
  {
    title: 'Aggiunto',
    key: 'created_at',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const selectedType = ref(route.query.type || '')
const selectedCategory = ref(route.query.category || '')
const selectedStatus = ref(route.query.enabled || '1')

// Update URL on filter change
watch([searchQuery, itemsPerPage, page, sortBy, orderBy, selectedType, selectedCategory, selectedStatus], () => {
  router.replace({
    query: {
      ...route.query,
      q: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
      type: selectedType.value,
      category: selectedCategory.value,
      enabled: selectedStatus.value,
    }
  })
})

const {
  data: brandsData,
  execute: fetchBrands,
} = await useApi(createUrl('/brands', {
  query: {
    q: searchQuery,
    enabled: selectedStatus,
    type: selectedType,
    category: selectedCategory,
    with: 'products',
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const brands = computed(() => brandsData.value.brands)
const totalBrands = computed(() => brandsData.value.totalBrands)

const isAddNewBrandDrawerVisible = ref(false)
const isEditBrandDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const deleteBrand = async id => {
  await $api(`/brands/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchBrands()
}

const selectBrandForRemove = brand => {
  selectedBrandRemove.value = brand
  isRemoveDialogVisible.value = true
}

const editBrand = brand => {
  selectedBrand.value = brand
  isEditBrandDrawerVisible.value = true
}

const types = [
  { value: '', title: 'Tutti' },
  { value: 'Residenziale', title: 'Residenziale' },
  { value: 'Business', title: 'Business' },
]
const categories = [
  { value: '', title: 'Tutti' },
  { value: 'Telefonia', title: 'Telefonia' },
  { value: 'Energia', title: 'Energia' },
  { value: 'Altro', title: 'Altro' },
]
const statuses = [
  { value: '1', title: 'Abilitati' },
  { value: '0', title: 'Disabilitati' },
  { value: '', title: 'Tutti' },
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
          <!-- 👉 Select Type -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedType"
              label="Filtra per Tipo"
              clearable
              :items="types"
              placeholder="Seleziona un tipo"
            />
          </VCol>

          <!-- 👉 Select Category -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedCategory"
              label="Filtra per Settore"
              clearable
              :items="categories"
              placeholder="Seleziona un settore"
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

          <!-- 👉 Add brand button -->
          <VBtn
            v-if="$can('create', 'brands') && !isBackoffice"
            prepend-icon="tabler-plus"
            @click="isAddNewBrandDrawerVisible = true"
          >
            Aggiungi Brand
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="brands"
        :items-length="totalBrands"
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

        <!-- 👉 Enabled -->
        <template #item.enabled="{ item }">
          <VChip
            :color="item.enabled ? 'success' : 'error'"
            :text="item.enabled ? 'Abilitato' : 'Disabilitato'"
          />
        </template>

        <!-- 👉 Type -->
        <template #item.type="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.type }}
            </div>
          </div>
        </template>

        <!-- 👉 Category -->
        <template #item.category="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.category }}
            </div>
          </div>
        </template>

        <!-- 👉 Notes -->
        <template #item.notes="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.notes }}
            </div>
          </div>
        </template>

        <!-- 👉 Products -->
        <template #item.products="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.products_count }}
            </div>
          </div>
        </template>


        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editBrand(item)" v-if="$can('edit', 'brands') && !isBackoffice">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectBrandForRemove(item)" v-if="$can('delete', 'brands') && !isBackoffice">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalBrands"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Brand -->
    <AddNewBrandDrawer
      v-if="$can('create', 'brands') && !isBackoffice"
      v-model:isDrawerOpen="isAddNewBrandDrawerVisible"
      @brand-data="fetchBrands"
    />
    <!-- 👉 Edit Brand -->
    <EditBrandDrawer v-if="selectedBrand && $can('edit', 'brands') && !isBackoffice"
      v-model:isDrawerOpen="isEditBrandDrawerVisible"
      @brand-data="fetchBrands"
      :brand="selectedBrand"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedBrandRemove && $can('delete', 'brands') && !isBackoffice"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Brand">
        <VCardText>
          Sei sicuro di voler eliminare il brand <b>{{ selectedBrandRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteBrand(selectedBrandRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

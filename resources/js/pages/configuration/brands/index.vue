<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'brands',
  },
})

import AddNewBrandDrawer from '@/views/configuration/brands/AddNewBrandDrawer.vue';
import EditBrandDrawer from '@/views/configuration/brands/EditBrandDrawer.vue';

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedBrand = ref()
const selectedBrandRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
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
    title: 'Tipologia',
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
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: brandsData,
  execute: fetchBrands,
} = await useApi(createUrl('/brands', {
  query: {
    q: searchQuery,
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

const addNewBrand = async brandData => {
  await $api('/brands', {
    method: 'POST',
    body: brandData,
  })

  fetchBrands()
}

const deleteBrand = async id => {
  await $api(`/brands/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchBrands()
}

const updateBrand = async brandData => {
  await $api(`/brands/${ brandData.id }`, {
    method: 'PUT',
    body: brandData,
  })

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

          <!-- 👉 Add brand button -->
          <VBtn
            v-if="$can('create', 'brands')"
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
          <IconBtn @click="editBrand(item)" v-if="$can('edit', 'brands')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectBrandForRemove(item)" v-if="$can('delete', 'brands')">
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
      v-if="$can('create', 'brands')"
      v-model:isDrawerOpen="isAddNewBrandDrawerVisible"
      @brand-data="addNewBrand"
    />
    <!-- 👉 Edit Brand -->
    <EditBrandDrawer v-if="selectedBrand && $can('edit', 'brands')"
      v-model:isDrawerOpen="isEditBrandDrawerVisible"
      @brand-data="updateBrand"
      :brand="selectedBrand"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedBrandRemove && $can('delete', 'brands')"
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

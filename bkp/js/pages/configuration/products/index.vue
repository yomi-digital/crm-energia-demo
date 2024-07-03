<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'products',
  },
})

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedProductRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Prodotto',
    key: 'name',
  },
  {
    title: 'Brand',
    key: 'brand_id',
  },
  {
    title: 'Categoria Brand',
    key: 'category',
  },
  {
    title: 'Stato',
    key: 'enabled',
  },
  {
    title: 'Note',
    key: 'notes',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: productsData,
  execute: fetchProducts,
} = await useApi(createUrl('/products', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const products = computed(() => productsData.value.products)
const totalProducts = computed(() => productsData.value.totalProducts)

const isRemoveDialogVisible = ref(false)

const deleteProduct = async id => {
  await $api(`/products/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchProducts()
}

const selectProductForRemove = product => {
  selectedProductRemove.value = product
  isRemoveDialogVisible.value = true
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

          <!-- 👉 Add Product button -->
          <VBtn
            :to="{ name: 'configuration-products-create' }"
            v-if="$can('create', 'products')"
            prepend-icon="tabler-plus"
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
        :items="products"
        :items-length="totalProducts"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Product -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.name }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Brand -->
        <template #item.brand_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.brand?.name }}
            </div>
          </div>
        </template>

        <!-- Category -->
        <template #item.category="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.brand.category }}
            </div>
          </div>
        </template>

        <!-- Enabled / chip gree if enabled, red if not-->
        <template #item.enabled="{ item }">
          <VChip
            :color="item.enabled ? 'success' : 'error'"
            :text="item.enabled ? 'Abilitato' : 'Disabilitato'"
          />
        </template>

        <!-- 👉 Notes -->
        <template #item.notes="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.notes }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn>
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectProductForRemove(item)">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalProducts"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedProductRemove"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Prodotto">
        <VCardText>
          Sei sicuro di voler eliminare il prodotto <b>{{ selectedProductRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteProduct(selectedProductRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

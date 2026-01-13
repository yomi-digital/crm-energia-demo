<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'products',
  },
})

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
const selectedProductRemove = ref()
const selectedBrand = ref(route.query.brand ? Number(route.query.brand) : undefined)
const selectedStatus = ref(route.query.enabled || '')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Update URL on filter change
watch([searchQuery, itemsPerPage, page, sortBy, orderBy, selectedBrand, selectedStatus], () => {
  router.replace({
    query: {
      ...route.query,
      q: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
      brand: selectedBrand.value,
      enabled: selectedStatus.value,
    }
  })
})

// Headers
const headers = [
  {
    title: 'Prodotto',
    key: 'name',
  },
  {
    title: 'Prezzo',
    key: 'price',
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
    brand: selectedBrand,
    enabled: selectedStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const products = computed(() => productsData.value.products)
const totalProducts = computed(() => productsData.value.totalProducts)

const isRemoveDialogVisible = ref(false)

const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const toggleProductStatus = async item => {
  try {
    const newStatus = !item.enabled

    await $api(`/products/${ item.id }`, {
      method: 'PUT',
      body: { enabled: newStatus },
    })

    snackbarMessage.value = `Prodotto ${newStatus ? 'abilitato' : 'disabilitato'} con successo`
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true
    
    fetchProducts()
  } catch (error) {
    console.error(error)
    snackbarMessage.value = 'Errore durante l\'aggiornamento dello stato'
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const deleteProduct = async id => {
  await $api(`/products/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchProducts()
}

// 👉 search filters
const brands = [
  {
    title: 'Tutti',
    value: '',
  },
]

const statuses = [
  { title: 'Tutti', value: '' },
  { title: 'Abilitato', value: '1' },
  { title: 'Disabilitato', value: '0' },
]
const fetchBrands = async (query) => {
  const response = await $api('/brands?itemsPerPage=999999&select=1')
  for (const brand of response.brands) {
    brands.push({
      title: brand.name,
      value: brand.id,
    })
  }
}
await fetchBrands()

const selectProductForRemove = product => {
  selectedProductRemove.value = product
  isRemoveDialogVisible.value = true
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Brand -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedBrand"
              label="Filtra per Brand"
              clearable
              :items="brands"
              placeholder="Seleziona un brand"
            />
          </VCol>

          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedStatus"
              label="Stato"
              clearable
              :items="statuses"
              placeholder="Seleziona stato"
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

          <!-- 👉 Add Product button -->
          <VBtn
            :to="{ name: 'configuration-products-create' }"
            v-if="$can('create', 'products') && !isBackoffice"
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
                <RouterLink v-if="$can('view', 'products')" :to="{ name: 'configuration-products-id', params: { id: item.id } }">
                  {{ item.name }}
                </RouterLink>
                <template v-else>{{ item.name }}</template>
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

        <!-- Price -->
        <template #item.price="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.price ? `€${item.price.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : 'N/A' }}
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
          <IconBtn @click="toggleProductStatus(item)" v-if="!isBackoffice">
            <VIcon :color="item.enabled ? 'warning' : 'success'" :icon="item.enabled ? 'tabler-square-x' : 'tabler-square-check'" />
            <VTooltip
              activator="parent"
              location="top"
            >
              {{ item.enabled ? 'Disabilita' : 'Abilita' }}
            </VTooltip>
          </IconBtn>

          <IconBtn @click="selectProductForRemove(item)" v-if="!isBackoffice">
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
            :total-items="totalProducts"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedProductRemove && !isBackoffice"
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

<script setup>
import { nextTick, onUnmounted } from 'vue'

definePage({
  meta: {
    action: 'access',
    subject: 'customers',
  },
})


// 👉 Store
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const isSearchLoading = ref(false)
const selectedBrand = ref()
const selectedCity = ref()

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Debounce per la ricerca (500ms)
let searchTimeout = null
watch(searchQuery, (newValue) => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    debouncedSearchQuery.value = newValue
  }, 500)
}, { immediate: true })

// Watch per tracciare quando la ricerca debounced cambia e avviare il caricamento
watch(debouncedSearchQuery, async (newValue, oldValue) => {
  // Se il valore è cambiato e c'è una ricerca attiva, imposta il loading
  if (newValue !== oldValue && newValue !== '') {
    isSearchLoading.value = true
    // Attendi che la query venga eseguita
    await nextTick()
    // Il loading verrà resettato quando la query è completata (tramite watch su isFetchingCustomers)
  } else if (newValue === '') {
    isSearchLoading.value = false
  }
})

// Cleanup del timeout quando il componente viene smontato
onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
})

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: '',
    key: 'confirmed_at',
  },
  {
    title: 'Nome / Ragione Sociale',
    key: 'name',
  },
  {
    title: 'Codice Fiscale',
    key: 'tax_id_code',
  },
  {
    title: 'P. IVA',
    key: 'vat_number',
  },
  {
    title: 'Città',
    key: 'city',
  },
  {
    title: 'Indirizzo',
    key: 'address',
  },
  {
    title: 'Regione',
    key: 'region',
  },
  {
    title: 'Provincia',
    key: 'province',
  },
  {
    title: 'CAP',
    key: 'zip',
  },
  // {
  //   title: 'Telefono',
  //   key: 'phone',
  // },
  // {
  //   title: 'Cellulare',
  //   key: 'mobile',
  // },
  {
    title: 'Data Inserimento',
    key: 'added_at',
  },
  {
    title: 'Azioni',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: customersData,
  execute: fetchCustomers,
  pending: isFetchingCustomers,
} = await useApi(createUrl('/customers', {
  query: {
    q: debouncedSearchQuery,
    brand: selectedBrand,
    city: selectedCity,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const customers = computed(() => customersData.value.customers)
const totalCustomers = computed(() => customersData.value.totalCustomers)

// Watch per nascondere il loader quando la chiamata API è completata
// Deve essere dopo la definizione di isFetchingCustomers
watch(() => isFetchingCustomers?.value, (isLoading) => {
  if (isLoading === false) {
    // La chiamata è completata, nascondi il loader dopo un breve delay
    setTimeout(() => {
      isSearchLoading.value = false
    }, 100)
  }
})

// Computed per mostrare il loader quando si sta cercando o caricando
const showSearchLoader = computed(() => {
  // Mostra il loader se:
  // 1. L'utente ha digitato qualcosa che non è ancora stato applicato alla ricerca (debounce)
  // 2. Oppure se sta caricando i risultati dalla chiamata API
  return searchQuery.value !== debouncedSearchQuery.value || isSearchLoading.value || (isFetchingCustomers?.value === true)
})

const truncate = (text, length = 30) => {
  if (text.length > length) {
    return text.substring(0, length) + '...'
  }

  return text
}

// 👉 search filters
const brands = [
  {
    title: 'Tutti',
    value: '',
  },
]
const fetchBrands = async (query) => {
  const response = await useApi(createUrl('/brands?itemsPerPage=999999&select=1'))
  if (response && response.brands) {
    for (const brand of response.brands) {
      brands.push({
        title: brand.name,
        value: brand.id,
      })
    }
  }
}
await fetchBrands()

const cities = [
  {
    title: 'Tutte',
    value: '',
  },
]
const fetchCities = async (query) => {
  const response = await useApi(createUrl('/cities'))
  if (response && response.length) {
    for (const city of response) {
      cities.push({
        title: city.city,
        value: city.city,
      })
    }
  }
}
await fetchCities()

const isExporting = ref(false)
const exportCustomers = async () => {
  isExporting.value = true
  try {
    const data = await $api(`/customers`, {
      method: 'GET',
      query: {
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
        export: 'csv',
        q: debouncedSearchQuery.value,
        brand: selectedBrand.value,
        city: selectedCity.value,
      },
      responseType: 'blob'
    })

    const fileName = 'clienti.xlsx';

    const blob = new Blob([data], { type: data.type })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting customers:', error)
    // Handle the error (e.g., show a notification to the user)
  } finally {
    isExporting.value = false
  }
}

// Delete functionality
const isDeleting = ref(false)
const deleteDialog = ref(false)
const customerToDelete = ref(null)

// Result dialog for delete operation
const deleteResultDialog = ref(false)
const deleteResult = ref({
  success: false,
  message: ''
})

// Alert system for notifications
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

const openDeleteDialog = (customer) => {
  customerToDelete.value = customer
  deleteDialog.value = true
  // Nascondi eventuali alert precedenti
  alert.value.show = false
}

const closeDeleteDialog = () => {
  deleteDialog.value = false
  customerToDelete.value = null
  // Nascondi eventuali alert quando si chiude il dialog
  alert.value.show = false
}

const closeDeleteResultDialog = () => {
  deleteResultDialog.value = false
  deleteResult.value = { success: false, message: '' }
}

const deleteCustomer = async () => {
  if (!customerToDelete.value) return
  
  isDeleting.value = true
  try {
    const response = await $api(`/customers/${customerToDelete.value.id}`, {
      method: 'DELETE'
    })

    if (response.success) {
      // Ricarica la lista dei clienti
      await fetchCustomers()
      
      // Chiudi il dialog di conferma
      closeDeleteDialog()
      
      // Mostra dialog di successo
      deleteResult.value = {
        success: true,
        message: 'Cliente eliminato con successo'
      }
      deleteResultDialog.value = true
    } else {
      // Chiudi il dialog di conferma
      closeDeleteDialog()
      
      // Mostra dialog di errore
      deleteResult.value = {
        success: false,
        message: response.message || 'Errore durante l\'eliminazione del cliente'
      }
      deleteResultDialog.value = true
    }
  } catch (error) {
    console.error('Error deleting customer:', error)
    
    // Chiudi il dialog di conferma
    closeDeleteDialog()
    
    // Mostra dialog di errore
    deleteResult.value = {
      success: false,
      message: 'Errore durante l\'eliminazione del cliente'
    }
    deleteResultDialog.value = true
  } finally {
    isDeleting.value = false
  }
}


const widgetData = ref([
  {
    title: 'Session',
    value: '21,459',
    change: 29,
    desc: 'Total Users',
    icon: 'tabler-users',
    iconColor: 'primary',
  },
  {
    title: 'Paid Users',
    value: '4,567',
    change: 18,
    desc: 'Last Week Analytics',
    icon: 'tabler-user-plus',
    iconColor: 'error',
  },
  {
    title: 'Active Users',
    value: '19,860',
    change: -14,
    desc: 'Last Week Analytics',
    icon: 'tabler-user-check',
    iconColor: 'success',
  },
  {
    title: 'Pending Users',
    value: '237',
    change: 42,
    desc: 'Last Week Analytics',
    icon: 'tabler-user-search',
    iconColor: 'warning',
  },
])
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

          <!-- 👉 Select City -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppAutocomplete
              v-model="selectedCity"
              label="Filtra per Città"
              clearable
              :items="cities"
              placeholder="Seleziona una città"
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
            >
              <template #append-inner>
                <VIcon
                  v-if="showSearchLoader"
                  icon="tabler-loader-2"
                  class="tabler-loader-2"
                  size="20"
                />
              </template>
            </AppTextField>
          </div>

          <!-- 👉 Export button -->
          <VBtn
            variant="tonal"
            color="primary"
            :prepend-icon="isExporting ? 'tabler-loader' : 'tabler-download'"
            :disabled="isExporting"
            @click="exportCustomers"
          >
            Esporta
          </VBtn>

          <!-- 👉 Add user button -->
          <VBtn
            :to="{ name: 'workflow-customers-create' }"
            v-if="$can('create', 'customers')"
            prepend-icon="tabler-plus"
          >
            Crea Cliente
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="customers"
        :items-length="totalCustomers"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <template #item.confirmed_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VAvatar
              :color="item.confirmed_at ? 'success' : 'warning'"
              variant="tonal"
              rounded
              size="32"
            >
              <VIcon
                :icon="item.confirmed_at ? 'tabler-circle-check' : 'tabler-alert-triangle'"
                size="20"
              />
            </VAvatar>
            <VTooltip
              activator="parent"
              location="top"
            >
              {{ item.confirmed_at ? 'Confermato' : 'Non confermato' }}
            </VTooltip>
          </div>
        </template>
        <!-- Customer -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  v-if="$can('view', 'customers')"
                  :to="{ name: 'workflow-customers-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                  :title="item.business_name ? item.business_name : [item.name, item.last_name].join(' ')"
                >
                  {{ truncate(item.business_name ? item.business_name : [item.name, item.last_name].join(' ')) }}
                </RouterLink>
                <span v-else>{{ truncate(item.business_name ? item.business_name : [item.name, item.last_name].join(' ')) }}</span>
              </h6>
              <div class="text-sm">
                {{ item.email }}
              </div>
            </div>
          </div>
        </template>

        <!-- 👉 Tax ID Code -->
        <template #item.tax_id_code="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.tax_id_code }}
            </div>
          </div>
        </template>

        <!-- 👉 VAT Number -->
        <template #item.vat_number="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.vat_number }}
            </div>
          </div>
        </template>

        <!-- 👉 City -->
        <template #item.city="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.city }}
            </div>
          </div>
        </template>

        <!-- 👉 Address -->
        <template #item.address="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.address }}
            </div>
          </div>
        </template>

        <!-- 👉 Region -->
        <template #item.region="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.region }}
            </div>
          </div>
        </template>

        <!-- 👉 Province -->
        <template #item.province="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.province }}
            </div>
          </div>
        </template>

        <!-- 👉 ZIP -->
        <template #item.zip="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.zip }}
            </div>
          </div>
        </template>

        <!-- 👉 Phone -->
        <!-- <template #item.phone="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.phone }}
            </div>
          </div>
        </template> -->

        <!-- 👉 Mobile -->
        <!-- <template #item.mobile="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.mobile }}
            </div>
          </div>
        </template> -->

        <!-- 👉 Added At -->
        <template #item.added_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.added_at }}
            </div>
          </div>
        </template>

        <!-- 👉 Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VBtn
              v-if="$can('delete', 'customers')"
              icon
              size="x-small"
              color="error"
              variant="text"
              @click="openDeleteDialog(item)"
            >
              <VIcon
                size="22"
                icon="tabler-trash"
              />
              <VTooltip
                activator="parent"
                location="top"
              >
                Elimina Cliente
              </VTooltip>
            </VBtn>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalCustomers"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- Delete Dialog -->
    <VDialog
      v-model="deleteDialog"
      max-width="500"
    >
      <VCard>
        <VCardTitle class="text-h5">
          Conferma Eliminazione
        </VCardTitle>
        
        <VCardText>
          <!-- Alert per messaggi di feedback -->
          <VAlert
            v-model="alert.show"
            :type="alert.type"
            variant="tonal"
            closable
            class="mb-4"
          >
            {{ alert.message }}
          </VAlert>

          <div class="d-flex align-center gap-x-3 mb-3">
            <VIcon
              icon="tabler-alert-triangle"
              size="24"
              color="warning"
            />
            <span class="text-base">
              Sei sicuro di voler eliminare questo cliente?
            </span>
          </div>
          
          <div v-if="customerToDelete" class="pa-4 bg-grey-50 rounded">
            <div class="text-sm text-medium-emphasis mb-1">Cliente da eliminare:</div>
            <div class="font-weight-medium">
              {{ customerToDelete.business_name ? customerToDelete.business_name : [customerToDelete.name, customerToDelete.last_name].join(' ') }}
            </div>
            <div class="text-sm text-medium-emphasis">{{ customerToDelete.email }}</div>
          </div>
          
          <VAlert
            type="warning"
            variant="tonal"
            class="mt-4"
          >
            <div class="text-sm">
              <strong>Attenzione:</strong> Questa azione eliminerà permanentemente il cliente e tutti i dati ad esso collegati (pratiche, ticket, documenti, ecc.). L'operazione non può essere annullata.
            </div>
          </VAlert>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="grey"
            variant="outlined"
            @click="closeDeleteDialog"
            :disabled="isDeleting"
          >
            Annulla
          </VBtn>
          <VBtn
            color="error"
            variant="elevated"
            @click="deleteCustomer"
            :loading="isDeleting"
          >
            Elimina Cliente
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Result Dialog -->
    <VDialog
      v-model="deleteResultDialog"
      max-width="500"
      persistent
    >
      <VCard class="text-center px-10 py-6">
        <VCardText>
          <VIcon
            :icon="deleteResult.success ? 'tabler-check' : 'tabler-x'"
            :color="deleteResult.success ? 'success' : 'error'"
            size="60"
          />
          <h6 class="text-lg font-weight-medium mt-4">
            {{ deleteResult.success ? 'Eliminazione completata' : 'Eliminazione fallita' }}
          </h6>
          <p class="text-body-2 mt-2">
            {{ deleteResult.message }}
          </p>
        </VCardText>

        <VCardText class="d-flex align-center justify-center">
          <VBtn
            :color="deleteResult.success ? 'success' : 'error'"
            @click="closeDeleteResultDialog"
          >
            Ho capito
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<style>
.tabler-loader,
.tabler-fidget-spinner,
.tabler-loader-3,
.tabler-loader-quarter,
.tabler-refresh-dot,
.tabler-reload,
.tabler-loader-2 {
  animation: spin-animation .8s infinite;
}

@keyframes spin-animation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(359deg);
  }
}
</style>

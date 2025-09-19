<script setup>

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    action: 'access',
    subject: 'lead-alfacom-solar-lead',
  },
})

// Controllo permessi admin
const loggedInUser = useCookie('userData')?.value
const isAdmin = loggedInUser?.roles?.some(role => role?.name === 'gestione' || role?.name === 'amministrazione')

// Redirect se non è admin
if (!isAdmin) {
  await navigateTo('/not-authorized')
}

// Data table options
const itemsPerPage = ref(25)
const maxItemsPerPage = 25
const page = ref(1)
const sortBy = ref('created_at')
const orderBy = ref('desc')

// Filtri
const filters = ref({
  nome: '',
  email: '',
  telefono: '',
  citta: '',
  provincia: '',
  tipo: '',
  incentivo_min: '',
  incentivo_max: ''
})

// Opzioni per il filtro tipo
const tipoOptions = [
  { title: 'Tutti', value: '' },
  { title: 'Producer', value: 'has' },
  { title: 'Consumer', value: 'wants' }
]


const updateOptions = options => {
  // Aggiorna ordinamento
  if (options.sortBy && options.sortBy.length > 0) {
    sortBy.value = options.sortBy[0].key || 'created_at'
    orderBy.value = options.sortBy[0].order || 'desc'
  }
  
  // Aggiorna paginazione
  if (options.page !== undefined) {
    page.value = options.page
  }
  
  if (options.itemsPerPage !== undefined) {
    // Limita il numero di elementi per pagina
    if (options.itemsPerPage > maxItemsPerPage) {
      itemsPerPage.value = maxItemsPerPage
    } else {
      itemsPerPage.value = options.itemsPerPage
    }
  }
}

// Headers
const headers = [
  {
    title: '',
    key: 'customer_status',
    sortable: false,
    width: 60,
    align: 'center',
  },
  {
    title: 'Nominativo',
    key: 'nominativo',
    sortable: true,
  },
  {
    title: 'Email',
    key: 'email',
    sortable: true,
  },
  {
    title: 'Telefono',
    key: 'numeroDiTelefono',
    sortable: false,
  },
  {
    title: 'Città',
    key: 'citta',
    sortable: true,
  },
  {
    title: 'Provincia',
    key: 'provincia',
    sortable: true,
  },
  {
    title: 'Incentivo',
    key: 'incentivo',
    sortable: true,
  },
  {
    title: 'Tipo',
    key: 'hasPanels',
    sortable: true,
  },
  {
    title: 'Data Creazione',
    key: 'created_at',
    sortable: true,
  },
  {
    title: 'Azioni',
    key: 'actions',
    sortable: false,
    align: 'center',
    width: 100,
  },
]

// Computed per pulire i filtri vuoti - si aggiorna automaticamente
const cleanFilters = computed(() => {
  const clean = {}
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      clean[key] = value
    }
  })
  return clean
})

// Computed per la query completa
const queryParams = computed(() => ({
  itemsPerPage: itemsPerPage.value,
  page: page.value,
  sortBy: sortBy.value,
  orderBy: orderBy.value,
  ...cleanFilters.value,
}))

const {
  data: incentiviData,
  execute: fetchIncentivi,
  pending: isLoading,
} = await useApi(createUrl('/incentivi/get-incentive', {
  query: queryParams,
}))

const sortedIncentivi = computed(() => {
  if (!incentiviData.value) return []
  return incentiviData.value.incentivi || []
})

const totalIncentivi = computed(() => {
  if (!incentiviData.value) return 0
  return incentiviData.value.totalIncentivi || 0
})

const incentivi = computed(() => sortedIncentivi.value)

// Watcher per aggiornare i dati quando cambiano i parametri
watchEffect(() => {
  fetchIncentivi()
})

// Watcher per resettare la pagina quando cambiano i filtri
watch(cleanFilters, () => {
  page.value = 1
}, { deep: true })

// Reset filtri
const resetFilters = () => {
  filters.value = {
    nome: '',
    email: '',
    telefono: '',
    citta: '',
    provincia: '',
    tipo: '',
    incentivo_min: '',
    incentivo_max: ''
  }
  page.value = 1
}

// Controlla se ci sono filtri attivi
const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some(filter => filter !== '')
})

// Funzione per formattare il tipo
const formatType = (hasPanels) => {
  if (hasPanels === 'has') return 'Producer'
  if (hasPanels === 'wants') return 'Consumer'
  return hasPanels
}

// Funzione per formattare la data
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('it-IT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Funzione per formattare l'incentivo
const formatIncentivo = (incentivo) => {
  if (!incentivo) return '-'
  return `€ ${parseFloat(incentivo).toFixed(2)}`
}

// Stati per i dialog di eliminazione
const deleteDialog = ref(false)
const showSuccessDialog = ref(false)
const showErrorDialog = ref(false)
const itemToDelete = ref(null)
const resultMessage = ref('')

// Stati per errori export Excel
const showExportErrorDialog = ref(false)
const exportErrorMessage = ref('')

// Stati per errori ricaricamento
const showReloadErrorDialog = ref(false)
const reloadErrorMessage = ref('')

// Stato loading per export Excel
const isExporting = ref(false)

// Funzione per aprire il dialog di conferma eliminazione
const confirmDelete = (item) => {
  itemToDelete.value = item
  deleteDialog.value = true
}

// Funzione per eliminare l'incentivo
const deleteIncentivo = async () => {
  if (!itemToDelete.value) return

  const nomeIncentivo = itemToDelete.value.nominativo

  try {
    // Usa useApi direttamente - gestisce la response
    const response = await useApi(`/incentivi/${itemToDelete.value.id}`, {
      method: 'DELETE'
    })

    // Reset item (il dialog si chiude automaticamente)
    itemToDelete.value = null

    // Controlla il status code per determinare successo/errore
    if (response.statusCode.value >= 200 && response.statusCode.value < 300) {
      // Successo
      resultMessage.value = `L'incentivo di ${nomeIncentivo} è stato eliminato con successo.`
      showSuccessDialog.value = true
    } else {
      // Errore (4xx, 5xx)
      console.error('API returned error status:', response.statusCode.value)
      
      let errorMessage = `Si è verificato un errore durante l'eliminazione dell'incentivo di ${nomeIncentivo}.`
      
      // Prova a estrarre il messaggio dall'API se disponibile
      if (response.data.value && response.data.value.message) {
        errorMessage = response.data.value.message
      } else if (response.statusCode.value === 404) {
        errorMessage = 'Incentivo non trovato'
      } else if (response.statusCode.value === 403) {
        errorMessage = 'Non hai i permessi per eliminare questo incentivo'
      }
      
      resultMessage.value = errorMessage
      showErrorDialog.value = true
    }
    
  } catch (error) {
    console.error('Errore nella chiamata API:', error)
    resultMessage.value = `Si è verificato un errore durante l'eliminazione dell'incentivo di ${nomeIncentivo}. Riprova più tardi.`
    showErrorDialog.value = true
  } finally {
    await fetchIncentivi()
  }
}

// Funzione per annullare l'eliminazione
const cancelDelete = () => {
  itemToDelete.value = null
}

// Funzione per ricaricamento con gestione errori
const reloadData = async () => {
  try {
    await fetchIncentivi()
  } catch (error) {
    console.error('Errore durante il ricaricamento:', error)
    
    // Determina il messaggio di errore appropriato
    let errorMessage = 'Si è verificato un errore durante il ricaricamento dei dati.'
    
    if (error.response) {
      // Errore HTTP
      if (error.response.status === 403) {
        errorMessage = 'Non hai i permessi per accedere a questi dati.'
      } else if (error.response.status === 500) {
        errorMessage = 'Errore del server durante il ricaricamento. Riprova più tardi.'
      } else if (error.response.status === 404) {
        errorMessage = 'Servizio non disponibile.'
      }
    } else if (error.code === 'NETWORK_ERROR') {
      errorMessage = 'Errore di connessione. Verifica la tua connessione internet.'
    }
    
    // Mostra dialog di errore
    reloadErrorMessage.value = errorMessage
    showReloadErrorDialog.value = true
  }
}

// Router per navigazione
const router = useRouter()

// Funzione per navigare al customer
const goToCustomer = (customerId) => {
  router.push({ name: 'workflow-customers-id', params: { id: customerId } })
}


// Funzione per esportare Excel
const exportExcel = async () => {
  if (isExporting.value) return // Previene click multipli
  
  isExporting.value = true
  
  try {
    const response = await $api('/incentivi/get-incentive', {
      method: 'GET',
      query: {
        ...cleanFilters.value,
        export: 'csv',
      },
      responseType: 'blob'
    })

    // Nome file con timestamp
    const fileName = `alfacom_solar_lead_${new Date().toISOString().slice(0, 10)}.xlsx`

    const blob = new Blob([response], { type: response.type })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Errore durante l\'export:', error)
    
    // Determina il messaggio di errore appropriato
    let errorMessage = 'Si è verificato un errore durante l\'esportazione del file Excel.'
    
    if (error.response) {
      // Errore HTTP
      if (error.response.status === 403) {
        errorMessage = 'Non hai i permessi per esportare questi dati.'
      } else if (error.response.status === 500) {
        errorMessage = 'Errore del server durante l\'esportazione. Riprova più tardi.'
      } else if (error.response.status === 404) {
        errorMessage = 'Servizio di esportazione non disponibile.'
      }
    } else if (error.code === 'NETWORK_ERROR') {
      errorMessage = 'Errore di connessione. Verifica la tua connessione internet.'
    }
    
    // Mostra dialog di errore
    exportErrorMessage.value = errorMessage
    showExportErrorDialog.value = true
  } finally {
    isExporting.value = false
  }
}
</script>

<template>
  <VCard>
    <VCardText>
      <div class="d-flex align-center justify-space-between mb-4">
        <h1 class="text-h3">
          Alfacom Solar Lead
        </h1>
        <div class="d-flex gap-2">
          <VBtn
            color="success"
            variant="outlined"
            prepend-icon="tabler-download"
            :loading="isExporting"
            :disabled="isExporting"
            @click="exportExcel"
          >
            {{ isExporting ? 'Esportazione...' : 'Esporta Excel' }}
          </VBtn>
          <VBtn
            color="primary"
            variant="outlined"
            prepend-icon="tabler-refresh"
            @click="reloadData"
            :loading="isLoading"
          >
            Ricarica
          </VBtn>
        </div>
      </div>

      <!-- Filtri -->
      <VRow class="mb-6">
        <!-- Filtro Nome -->
        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.nome"
            label="Nome"
            placeholder="Filtra per nome..."
            clearable
            density="compact"
          />
        </VCol>

        <!-- Filtro Email -->
        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.email"
            label="Email"
            placeholder="Filtra per email..."
            clearable
            density="compact"
          />
        </VCol>

        <!-- Filtro Telefono -->
        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.telefono"
            label="Telefono"
            placeholder="Filtra per telefono..."
            clearable
            density="compact"
          />
        </VCol>

        <!-- Filtro Tipo -->
        <VCol
          cols="12"
          md="3"
        >
          <VSelect
            v-model="filters.tipo"
            :items="tipoOptions"
            label="Tipo"
            clearable
            density="compact"
          />
        </VCol>

        <!-- Filtro Città -->
        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.citta"
            label="Città"
            placeholder="Filtra per città..."
            clearable
            density="compact"
          />
        </VCol>

        <!-- Filtro Provincia -->
        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.provincia"
            label="Provincia"
            placeholder="Filtra per provincia..."
            clearable
            density="compact"
          />
        </VCol>

        <!-- Range Incentivo -->
        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.incentivo_min"
            label="Incentivo minimo (€)"
            placeholder="Es: 100"
            type="number"
            clearable
            density="compact"
          />
        </VCol>

        <VCol
          cols="12"
          md="3"
        >
          <VTextField
            v-model="filters.incentivo_max"
            label="Incentivo massimo (€)"
            placeholder="Es: 5000"
            type="number"
            clearable
            density="compact"
          />
        </VCol>

        <!-- Pulsante Reset e contatore -->
        <VCol
          cols="12"
          class="d-flex align-center gap-4"
        >
          <VBtn
            v-if="hasActiveFilters"
            color="secondary"
            variant="outlined"
            size="small"
            @click="resetFilters"
          >
            Reset Filtri
          </VBtn>
          <VSpacer />
          <span class="text-caption text-medium-emphasis">
            {{ totalIncentivi }} risultati totali
          </span>
        </VCol>
      </VRow>
      
      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="incentivi"
        :headers="headers"
        :items-length="totalIncentivi"
        :loading="isLoading"
        :items-per-page-options="[10, 25, 50, 100]"
        @update:options="updateOptions"
      >
        <!-- Customer Status Column -->
        <template #item.customer_status="{ item }">
          <VAvatar
            :color="item.customer ? 'success' : 'warning'"
            variant="tonal"
            size="32"
          >
            <VIcon
              :icon="item.customer ? 'tabler-user-check' : 'tabler-user-plus'"
              size="18"
            />
          </VAvatar>
          <VTooltip
            activator="parent"
            location="top"
          >
            {{ item.customer ? 'Customer esistente' : 'Customer non trovato' }}
          </VTooltip>
        </template>

        <!-- Nominativo Column -->
        <template #item.nominativo="{ item }">
          <div class="d-flex flex-column">
            <span 
              v-if="!item.customer"
              class="font-weight-medium"
            >
              {{ item.nominativo }}
            </span>
            <span 
              v-else
              class="font-weight-medium text-decoration-underline text-primary"
              style="cursor: pointer;"
              @click="goToCustomer(item.customer.id)"
            >
              {{ item.nominativo }}
            </span>
          </div>
        </template>

        <!-- Email Column -->
        <template #item.email="{ item }">
          <a 
            :href="`mailto:${item.email}`"
            class="text-sm text-primary text-decoration-underline"
            style="cursor: pointer;"
          >
            {{ item.email }}
          </a>
        </template>

        <!-- Telefono Column -->
        <template #item.numeroDiTelefono="{ item }">
          <span class="text-sm">{{ item.numeroDiTelefono }}</span>
        </template>

        <!-- Città Column -->
        <template #item.citta="{ item }">
          <span class="text-sm">{{ item.citta }}</span>
        </template>

        <!-- Provincia Column -->
        <template #item.provincia="{ item }">
          <span class="text-sm">{{ item.provincia }}</span>
        </template>

        <!-- Incentivo Column -->
        <template #item.incentivo="{ item }">
          <VChip
            :color="parseFloat(item.incentivo) > 1000 ? 'success' : 'primary'"
            variant="tonal"
            size="small"
          >
            {{ formatIncentivo(item.incentivo) }}
          </VChip>
        </template>

        <!-- Tipo Column -->
        <template #item.hasPanels="{ item }">
          <VChip
            :color="item.hasPanels === 'has' ? 'success' : 'warning'"
            variant="tonal"
            size="small"
          >
            {{ formatType(item.hasPanels) }}
          </VChip>
        </template>

        <!-- Data Creazione Column -->
        <template #item.created_at="{ item }">
          <span class="text-sm">{{ formatDate(item.created_at) }}</span>
        </template>

        <!-- Azioni Column -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            size="small"
            color="error"
            variant="text"
            @click="confirmDelete(item)"
          >
            <VIcon icon="tabler-trash" />
            <VTooltip
              activator="parent"
              location="top"
            >
              Elimina incentivo
            </VTooltip>
          </VBtn>
        </template>

        <!-- Header personalizzato per Data Creazione -->
        <template #header.created_at>
          <div class="d-flex align-center">
            <span>Data Creazione</span>
            <VIcon
              v-if="sortBy === 'created_at'"
              :icon="orderBy === 'asc' ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              size="16"
              class="ms-1"
            />
          </div>
        </template>

        <!-- Header personalizzato per Incentivo -->
        <template #header.incentivo>
          <div class="d-flex align-center">
            <span>Incentivo</span>
            <VIcon
              v-if="sortBy === 'incentivo'"
              :icon="orderBy === 'asc' ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              size="16"
              class="ms-1"
            />
          </div>
        </template>

        <!-- Slot per il testo quando non ci sono dati -->
        <template #no-data>
          <div class="text-center pa-4">
            <VIcon
              icon="tabler-database-off"
              size="48"
              class="mb-2 text-disabled"
            />
            <p class="text-disabled">
              Nessun incentivo trovato
            </p>
          </div>
        </template>
      </VDataTableServer>
    </VCardText>
  </VCard>

  <!-- Dialog di conferma eliminazione -->
  <GeneralAlertDialog
    v-model="deleteDialog"
    title="Conferma Eliminazione"
    :message="`Sei sicuro di voler eliminare l'incentivo di ${itemToDelete?.nominativo}?`"
    confirm-text="Elimina"
    cancel-text="Annulla"
    @confirm="deleteIncentivo"
    @cancel="cancelDelete"
  />

  <!-- Dialog di Successo -->
  <GeneralSuccessDialog
    v-model="showSuccessDialog"
    title="Incentivo eliminato con successo"
    :message="resultMessage"
    button-text="Ho capito"
  />

  <!-- Dialog di Errore -->
  <GeneralErrorDialog
    v-model="showErrorDialog"
    title="Errore durante l'eliminazione"
    :message="resultMessage"
    button-text="Ho capito"
  />

  <!-- Dialog di Errore Export Excel -->
  <GeneralErrorDialog
    v-model="showExportErrorDialog"
    title="Errore durante l'esportazione"
    :message="exportErrorMessage"
    button-text="Ho capito"
  />

  <!-- Dialog di Errore Ricaricamento -->
  <GeneralErrorDialog
    v-model="showReloadErrorDialog"
    title="Errore durante il ricaricamento"
    :message="reloadErrorMessage"
    button-text="Ho capito"
  />
</template>

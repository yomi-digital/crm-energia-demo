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

// Stati per il dialog di conferma
const deleteDialog = ref(false)
const itemToDelete = ref(null)

// Stati per i dialog di notifica
const showSuccessDialog = ref(false)
const showErrorDialog = ref(false)
const notificationMessage = ref('')

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

    // Chiudi il dialog di conferma
    deleteDialog.value = false
    itemToDelete.value = null

    // Controlla il status code per determinare successo/errore
    if (response.statusCode.value >= 200 && response.statusCode.value < 300) {
      // Mostra dialog di successo
      notificationMessage.value = `L'incentivo di ${nomeIncentivo} è stato eliminato con successo.`
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
      
      // Mostra dialog di errore
      notificationMessage.value = errorMessage
      showErrorDialog.value = true
    }
  } catch (error) {
    console.error('Errore nella chiamata API:', error)
    
    // Chiudi il dialog di conferma
    deleteDialog.value = false
    itemToDelete.value = null
    
    // Mostra dialog di errore
    notificationMessage.value = `Si è verificato un errore durante l'eliminazione dell'incentivo di ${nomeIncentivo}. Riprova più tardi.`
    showErrorDialog.value = true
  } finally{
    await fetchIncentivi()
  }
}

// Funzione per annullare l'eliminazione
const cancelDelete = () => {
  deleteDialog.value = false
  itemToDelete.value = null
}
</script>

<template>
  <VCard>
    <VCardText>
      <div class="d-flex align-center justify-space-between mb-4">
        <h1 class="text-h3">
          Alfacom Solar Lead
        </h1>
        <VBtn
          color="primary"
          variant="outlined"
          prepend-icon="tabler-refresh"
          @click="fetchIncentivi"
          :loading="isLoading"
        >
          Ricarica
        </VBtn>
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
        <!-- Nominativo Column -->
        <template #item.nominativo="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium">
              {{ item.nominativo }}
            </span>
          </div>
        </template>

        <!-- Email Column -->
        <template #item.email="{ item }">
          <span class="text-sm">{{ item.email }}</span>
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
  <VDialog
    v-model="deleteDialog"
    max-width="500"
  >
    <VCard>
      <VCardTitle class="d-flex align-center">
        <VIcon
          icon="tabler-alert-triangle"
          color="warning"
          class="me-2"
        />
        Conferma Eliminazione
      </VCardTitle>
      
      <VCardText>
        <p class="mb-4">
          Sei sicuro di voler eliminare l'incentivo di <strong>{{ itemToDelete?.nominativo }}</strong>?
        </p>
        <p class="text-sm text-medium-emphasis mb-0">
          Questa azione non può essere annullata.
        </p>
      </VCardText>
      
      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="outlined"
          @click="cancelDelete"
        >
          Annulla
        </VBtn>
        <VBtn
          color="error"
          @click="deleteIncentivo"
        >
          Elimina
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Dialog di Successo Eliminazione -->
  <VDialog
    v-model="showSuccessDialog"
    max-width="500"
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VIcon
          icon="tabler-check"
          color="success"
          size="60"
        />
        <h6 class="text-lg font-weight-medium mt-4">
          Incentivo eliminato con successo
        </h6>
        <p class="text-body-2 mt-2">
          {{ notificationMessage }}
        </p>
      </VCardText>
      
      <VCardText class="d-flex align-center justify-center">
        <VBtn
          color="success"
          @click="showSuccessDialog = false"
        >
          Ho capito
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <!-- Dialog di Errore Eliminazione -->
  <VDialog
    v-model="showErrorDialog"
    max-width="500"
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VIcon
          icon="tabler-alert-triangle"
          color="error"
          size="60"
        />
        <h6 class="text-lg font-weight-medium mt-4">
          Errore durante l'eliminazione
        </h6>
        <p class="text-body-2 mt-2">
          {{ notificationMessage }}
        </p>
      </VCardText>
      
      <VCardText class="d-flex align-center justify-center">
        <VBtn
          color="error"
          @click="showErrorDialog = false"
        >
          Ho capito
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

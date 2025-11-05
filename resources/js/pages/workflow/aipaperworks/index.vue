<script setup>
import { onMounted, onUnmounted } from 'vue'
definePage({
  meta: {
    action: 'access',
    subject: 'aipaperworks',
  },
})

// 👉 Store
const searchQuery = ref('')
const loadingStates = ref(new Map())

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedAgent = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key || null
  orderBy.value = options.sortBy[0]?.order || null
}

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser?.roles?.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

// Headers
const headers = [
  {
    title: '',
    key: 'actions',
    sortable: false,
    width: '100px',
  },
  {
    title: '#',
    key: 'id',
    sortable: false,
    width: '80',
  },
  {
    title: 'Agente',
    key: 'user_id',
    sortable: false,
  },
  {
    title: 'File',
    key: 'filepath',
    sortable: false,
  },
  {
    title: 'Stato',
    key: 'status',
  },
  {
    title: 'Data Creazione',
    key: 'created_at',
  },
]

const {
  data: aiPaperworksData,
  execute: fetchAIPaperworks,
} = await useApi(createUrl('/ai-paperworks', {
  query: {
    itemsPerPage,
    user_id: selectedAgent,
    page,
    sortBy,
    orderBy,
  },
}))

const paperworks = computed(() => aiPaperworksData.value?.entries || [])
const totalPaperworks = computed(() => aiPaperworksData.value?.totalEntries || 0)

// Polling automatico ogni 10 secondi per aggiornare le pratiche AI
let pollingInterval = null

const startPolling = () => {
  // Previeni polling multipli
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
  
  pollingInterval = setInterval(async () => {
    await fetchAIPaperworks()
  }, 10000) // 10 secondi
}

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

// Avvia il polling quando il componente viene montato
onMounted(() => {
  startPolling()
})

// Ferma il polling quando l'utente esce dalla pagina
onUnmounted(() => {
  stopPolling()
})

const agents = ref([])
const fetchAgents = async () => {
  agents.value = []
  const response = await $api('/agents?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.agents.length; i++) {
    agents.value.push({
      title: [response.agents[i].name, response.agents[i].last_name].join(' '),
      value: response.agents[i].id,
    })
  }
}
if (useAbility().can('view', 'users')) {
  fetchAgents()
}

const getStatusChipColor = (status) => {
  switch (status) {
    case 0:
      return 'warning'
    case 1:
      return 'info'
    case 2:
      return 'success'
    case 5:
      return 'success'
    case 8:
      return 'error'
    case 9:
      return 'error'
    default:
      return 'error'
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 0:
      return 'In attesa'
    case 1:
      return 'In elaborazione'
    case 2:
      return 'Processato'
    case 8:
      return 'Annullato'
    case 9:
      return 'Errore'
    case 5:
      return 'Confermato'
    default:
      return 'Errore'
  }
}

const processDocument = async (item) => {
  try {
    loadingStates.value.set(item.id, true)
    item.status = 1
    await $api(`/ai-paperworks/${item.id}/process`, {
      method: 'POST',
    })
    await fetchAIPaperworks()
  } catch (error) {
    console.error('Error processing document:', error)
  } finally {
    loadingStates.value.delete(item.id)
  }
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
          <VCol cols="4" v-if="$can('view', 'users')">
            <AppAutocomplete
              v-model="selectedAgent"
              label="Filtra per Agente"
              clearable
              :items="agents"
              placeholder="Seleziona un Agente"
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
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="paperworks"
        :items-length="totalPaperworks"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- ID -->
        <template #item.id="{ item }">
          <div class="text-high-emphasis text-body-1">
            {{ item?.id }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center">
            <VBtn
              :to="{ name: 'workflow-aipaperworks-id', params: { id: item.id } }"
              size="small"
              variant="text"
              color="primary"
            >
              Visualizza
            </VBtn>
          </div>
        </template>

        <!-- Agent -->
        <template #item.user_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.user && $can('view', 'users')"
                :to="{ name: 'admin-users-id', params: { id: item.user.id } }"
                class="font-weight-medium text-link"
              >
                {{ [item.user.name, item.user.last_name].join(' ') }}
              </RouterLink>
              <template v-else>
                {{ [item.user?.name, item.user?.last_name].join(' ') }}
              </template>
            </div>
          </div>
        </template>

        <!-- File -->
        <template #item.filepath="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.filepath ? item.filepath.split('/').pop() : '' }}
            </div>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="getStatusChipColor(item.status)"
            size="small"
            class="text-capitalize"
          >
            {{ getStatusText(item.status) }}
          </VChip>
          <VProgressCircular
            v-if="loadingStates.get(item.id)"
            indeterminate
            color="primary"
            size="20"
            class="ms-2"
          />
          <!-- <VBtn
            v-if="item.status === 0 && !loadingStates.get(item.id)"
            icon
            variant="text"
            size="small"
            color="primary"
            class="ms-2"
            @click="processDocument(item)"
          >
            <VIcon icon="tabler-player-play" />
          </VBtn> -->
        </template>

        <!-- Created At -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.created_at }}
            </div>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPaperworks"
          />
        </template>
      </VDataTableServer>
    </VCard>
  </section>
</template>

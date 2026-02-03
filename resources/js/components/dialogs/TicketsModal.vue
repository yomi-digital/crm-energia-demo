<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const userData = useCookie('userData').value
const isStrutturaOrAgente = computed(() => {
  if (!userData?.roles) return false
  return userData.roles.some(role => ['struttura', 'agente'].includes(role.name))
})

const ticketTableHeaders = computed(() => {
  const base = [
    { title: 'ID', key: 'id', width: '80' },
    { title: 'Pratica', key: 'paperwork_id', sortable: false },
    { title: 'Cliente', key: 'customer', sortable: false },
    { title: 'Oggetto', key: 'title', sortable: false },
    { title: 'Agente', key: 'agent', sortable: false },
    { title: 'Stato', key: 'status' },
    { title: 'Creato Da', key: 'created_by', sortable: false },
    { title: 'Data Creazione', key: 'created_at', sortable: false },
  ]
  if (isStrutturaOrAgente.value) {
    return [...base, { title: 'Azioni', key: 'actions', sortable: false, width: '100' }]
  }
  return base
})

const isOpen = ref(false)

watch(() => props.modelValue, (newVal) => {
  isOpen.value = newVal
  if (newVal) {
    fetchTickets()
  }
})

watch(isOpen, (newVal) => {
  if (!newVal) {
    emit('update:modelValue', false)
  }
})

const itemsPerPage = ref(10)
const page = ref(1)
const tickets = ref([])
const totalTickets = ref(0)
const isLoading = ref(false)

const fetchTickets = async () => {
  isLoading.value = true
  try {
    const response = await $api('/tickets', {
      params: {
        status: '1,2',
        page: page.value,
        itemsPerPage: itemsPerPage.value,
      },
    })
    tickets.value = response.tickets || []
    totalTickets.value = response.totalTickets || 0
  } catch (error) {
    console.error('Error fetching tickets:', error)
  } finally {
    isLoading.value = false
  }
}

const updateOptions = async ({ page: newPage, itemsPerPage: newItemsPerPage }) => {
  page.value = newPage
  itemsPerPage.value = newItemsPerPage
  await fetchTickets()
}

const ticketStatusText = (status) => {
  return ['Aperto', 'In Lavorazione', 'Risolto'][status - 1]
}

const close = () => {
  isOpen.value = false
}
</script>

<template>
  <VDialog
    v-model="isOpen"
    max-width="1200"
  >
    <VCard>
      <VCardItem class="d-flex justify-space-between align-center">
        <VCardTitle>Ticket Aperti</VCardTitle>
        <template #append>
          <VBtn
            icon="tabler-x"
            variant="text"
            color="default"
            @click="close"
          />
        </template>
      </VCardItem>
      
      <VCardText>
        <VDataTableServer
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          :items="tickets"
          :items-length="totalTickets"
          :loading="isLoading"
          :headers="ticketTableHeaders"
          class="text-no-wrap"
          @update:options="updateOptions"
        >
          <!-- Ticket ID -->
          <template #item.id="{ item }">
            <RouterLink
              :to="{ name: 'workflow-tickets-id', params: { id: item.id } }"
              class="font-weight-medium text-link"
            >
              {{ item.id }}
            </RouterLink>
          </template>

          <!-- Paperwork -->
          <template #item.paperwork_id="{ item }">
            <RouterLink
              :to="{ name: 'workflow-paperworks-id', params: { id: item.paperwork_id } }"
              class="font-weight-medium text-link"
            >
              {{ item.paperwork_id }}
            </RouterLink>
          </template>

          <!-- Customer -->
          <template #item.customer="{ item }">
            <RouterLink
              :to="{ name: 'workflow-customers-id', params: { id: item.paperwork.customer.id } }"
              class="font-weight-medium text-link"
            >
              {{ item.paperwork.customer.name ? item.paperwork.customer.name : item.paperwork.customer.business_name }}
            </RouterLink>
          </template>

          <!-- Title -->
          <template #item.title="{ item }">
            <div class="text-high-emphasis text-body-1">
              {{ item.title }}
            </div>
          </template>

          <!-- Status -->
          <template #item.status="{ item }">
            <div class="text-high-emphasis text-body-1">
              {{ ticketStatusText(item.status) }}
            </div>
          </template>

          <!-- Agent -->
          <template #item.agent="{ item }">
            <div class="text-high-emphasis text-body-1">
              {{ item.paperwork.user ? [item.paperwork.user.name, item.paperwork.user.last_name].join(' ') : 'N/A' }}
            </div>
          </template>

          <!-- Created By -->
          <template #item.created_by="{ item }">
            <div class="text-high-emphasis text-body-1">
              {{ [item.created_by.name, item.created_by.last_name].join(' ') }}
            </div>
          </template>

          <!-- Created At -->
          <template #item.created_at="{ item }">
            <div class="text-high-emphasis text-body-1">
              {{ item.created_at }}
            </div>
          </template>

          <!-- Azioni (solo per struttura/agente) -->
          <template
            v-if="isStrutturaOrAgente"
            #item.actions="{ item }"
          >
            <VBtn
              size="small"
              color="info"
              variant="tonal"
              class="compact-btn"
              :to="{ name: 'workflow-tickets-id', params: { id: item.id } }"
              title="Apri ticket"
            >
              Vedi
            </VBtn>
          </template>

          <!-- pagination -->
          <template #bottom>
            <TablePagination
              v-model:page="page"
              :items-per-page="itemsPerPage"
              :total-items="totalTickets"
            />
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>
  </VDialog>
</template>

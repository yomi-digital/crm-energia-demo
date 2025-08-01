<script setup>

definePage({
  meta: {
    action: 'access',
    subject: 'paperworks',
  },
})



// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedAgent = ref()
const selectedCustomer = ref()
const selectedCategory = ref()
const dateFrom = ref('')
const dateTo = ref('')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const selected = ref([])
const isBulkActionDialogOpen = ref(false)

const loggedInUser = useCookie('userData').value
// Check if in the roles array there is a role with name 'agente'
const isAgent = loggedInUser.roles.some(role => role.name === 'agente' || role.name === 'struttura')
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
const canViewPayout = useCookie('userData').value.roles.some(role => role.name === 'gestione' || role.name === 'amministrazione')

// Headers
let headers = [
  {
    title: '#',
    key: 'id',
  },
  {
    title: 'ID Pratica',
    key: 'order_code',
  },
  {
    title: 'Account/POD/PDR',
    key: 'account_pod_pdr',
  },
  {
    title: 'Agente',
    key: 'user_id',
    sortable: false,
  },
  {
    title: 'Cliente',
    key: 'customer_id',
    sortable: false,
  },
  {
    title: 'Data Inserimento',
    key: 'created_at',
  },
  {
    title: 'Data Invio',
    key: 'partner_sent_at',
  },
  {
    title: 'Stato Ordine',
    key: 'order_status',
  },
  {
    title: 'Prodotto',
    key: 'product_id',
    sortable: false,
  },
  {
    title: 'Categoria',
    key: 'category',
  },
  {
    title: 'Mandato',
    key: 'mandate_id',
    sortable: false,
  },
  {
    title: 'Esito Partner',
    key: 'partner_outcome',
  },
  {
    title: 'Data Esito Partner',
    key: 'partner_outcome_at',
  },
  {
    title: 'Pagato',
    key: 'paid',
  },
  {
    title: 'Compenso',
    key: 'pay',
    sortable: false,
  },
  {
    title: 'Azioni',
    key: 'actions',
    sortable: false,
  },
]

if (! canViewPayout) {
  headers = headers.filter(header => header.key !== 'paid')
  headers = headers.filter(header => header.key !== 'pay')
}

const {
  data: paperworksData,
  execute: fetchPaperworks,
} = await useApi(createUrl('/paperworks', {
  query: {
    q: searchQuery,
    itemsPerPage,
    user_id: selectedAgent,
    customer_id: selectedCustomer,
    category: selectedCategory,
    date_from: dateFrom,
    date_to: dateTo,
    page,
    sortBy,
    orderBy,
  },
}))

const paperworks = computed(() => paperworksData.value.paperworks)
const totalPaperworks = computed(() => paperworksData.value.totalPaperworks)

const getCustomerName = (customer) => {
  if (! customer) {
    return 'N/A'
  }
  if (customer.name) {
    return [customer.name, customer.last_name].join(' ')
  } else if (customer.business_name) {
    return customer.business_name
  } else {
    return '#' + customer.id
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

const customers = ref([])
const fetchCustomers = async () => {
  customers.value = []
  const response = await $api('/customers?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.customers.length; i++) {
    customers.value.push({
      title: getCustomerName(response.customers[i]),
      value: response.customers[i].id,
    })
  }
}
fetchCustomers()

const onSelectionChanged = (newSelection) => {
  selected.value = newSelection
}

const openBulkActionDialog = () => {
  isBulkActionDialogOpen.value = true
}

const closeBulkActionDialog = () => {
  isBulkActionDialogOpen.value = false
}

const handleBulkAction = (newStatus) => {
  fetchPaperworks()
}

const router = useRouter()

const openPaperwork = (event, item) => {
  if(!item.item?.id){
    alert('Item ID non trovato')
    return
  }
  router.push({ name: 'workflow-paperworks-id', params: { id: item.item?.id } })
}

const isDuplicating = ref({})

// Modal states
const isConfirmDuplicateDialogVisible = ref(false)
const isDuplicateSuccessDialogVisible = ref(false)
const paperworkToDuplicate = ref(null)
const duplicatedPaperworkId = ref(null)

const showConfirmDuplicate = (paperworkId) => {
  // Trova la pratica completa dall'array paperworks
  const paperwork = paperworks.value.find(p => p.id === paperworkId)
  paperworkToDuplicate.value = paperwork
  isConfirmDuplicateDialogVisible.value = true
}

const confirmDuplicate = async () => {
  const paperworkId = paperworkToDuplicate.value.id
  isConfirmDuplicateDialogVisible.value = false
  
  try {
    isDuplicating.value[paperworkId] = true
    
    const response = await $api('/paperworks/duplicate', {
      method: 'POST',
      body: {
        praticheIds: [paperworkId]
      }
    })
    
    if (response.result && response.result[0]) {
      const result = response.result[0]
      if (result.duplication === 'success') {
        // Mostra modal di successo
        duplicatedPaperworkId.value = result.new_id
        isDuplicateSuccessDialogVisible.value = true
        // Ricarica la tabella per mostrare la nuova pratica
        await fetchPaperworks()
      } else {
        alert(`Errore nella duplicazione: ${result.message}`)
      }
    }
  } catch (error) {
    console.error('Errore durante la duplicazione:', error)
    alert('Errore durante la duplicazione della pratica')
  } finally {
    isDuplicating.value[paperworkId] = false
  }
}

const onSuccessModalClose = () => {
  isDuplicateSuccessDialogVisible.value = false
  duplicatedPaperworkId.value = null
  paperworkToDuplicate.value = null
}

const viewDuplicatedPaperwork = () => {
  isDuplicateSuccessDialogVisible.value = false
  router.push({ name: 'workflow-paperworks-id', params: { id: duplicatedPaperworkId.value } })
  duplicatedPaperworkId.value = null
  paperworkToDuplicate.value = null
}

const categories = ref([
  { title: 'ALLACCIO', value: 'ALLACCIO' },
  { title: 'OTP', value: 'OTP' },
  { title: 'SUBENTRO', value: 'SUBENTRO' },
  { title: 'VOLTURA', value: 'VOLTURA' },
  { title: 'SWITCH', value: 'SWITCH' },
  { title: 'NUOVA LINEA', value: 'NUOVA LINEA' },
  { title: 'PORTABILITÀ', value: 'PORTABILITÀ' },
])



</script>

<template>
  <section>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
        <!-- Filtri per data -->
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField
              v-model="dateFrom"
              label="Da"
              type="date"
              clearable
              placeholder="Seleziona data inizio"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppTextField
              v-model="dateTo"
              label="A"
              type="date"
              clearable
              placeholder="Seleziona data fine"
            />
          </VCol>
        </VRow>

        <!-- Altri filtri -->
        <VRow class="mt-4">
          <VCol cols="4" v-if="$can('view', 'users')">
            <AppAutocomplete
              v-model="selectedAgent"
              label="Filtra per Agente"
              clearable
              :items="agents"
              placeholder="Seleziona un Agente"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedCustomer"
              label="Filtra per Cliente"
              clearable
              :items="customers"
              placeholder="Seleziona un Cliente"
            />
          </VCol>

          <VCol cols="4">
            <AppAutocomplete
              v-model="selectedCategory"
              label="Filtra per Categoria"
              clearable
              :items="categories"
              placeholder="Seleziona una Categoria"
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

          <!-- Add this new button for bulk actions -->
          <VBtn
            :disabled="selected.length === 0"
            color="primary"
            @click="openBulkActionDialog"
            v-if="isAdmin"
          >
            Aggiorna Stato Pratiche
          </VBtn>
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

          <!-- 👉 Export button -->
          <!-- <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Esporta
          </VBtn> -->

          <!-- 👉 Add paperwork button -->
          <VBtn
            :to="{ name: 'workflow-paperworks-create-wizard' }"
            v-if="$can('create', 'paperworks')"
            prepend-icon="tabler-plus"
          >
            Crea Pratica
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:select="selected"
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="paperworks"
        :items-length="totalPaperworks"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
        @update:model-value="onSelectionChanged"
        @click:row="openPaperwork"
      >
        <!-- Paperwork -->
        <template #item.id="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'workflow-paperworks-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                  :title="item.id"
                  @click.stop
                >
                  {{ item.id }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 Order Code -->
        <template #item.order_code="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.order_code }}
            </div>
          </div>
        </template>

        <!-- 👉 Account/POD/PDR -->
        <template #item.account_pod_pdr="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.account_pod_pdr }}
            </div>
          </div>
        </template>

        <!-- 👉 Agent -->
        <template #item.user_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                v-if="item.user && $can('view', 'users')"
                :to="{ name: 'admin-users-id', params: { id: item.user.id } }"
                class="font-weight-medium text-link"
                @click.stop
              >
                {{ [item.user.name, item.user.last_name].join(' ') }}
              </RouterLink>
              <template v-else>
                {{ [item.user?.name, item.user?.last_name].join(' ') }}
              </template>
            </div>
          </div>
        </template>

        <!-- 👉 Customer -->
        <template #item.customer_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                :to="{ name: 'workflow-customers-id', params: { id: item.customer.id } }"
                class="font-weight-medium text-link"
                :title="getCustomerName(item.customer)"
                @click.stop
              >
                {{ getCustomerName(item.customer) }}
              </RouterLink>
            </div>
          </div>
        </template>

        <!-- 👉 Created At -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(item.created_at)) }}
            </div>
          </div>
        </template>

        <!-- 👉 Partner Sent At -->
        <template #item.partner_sent_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.partner_sent_at }}
            </div>
          </div>
        </template>

        <!-- 👉 Order Status -->
        <template #item.order_status="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.order_status }}
            </div>
          </div>
        </template>


        <!-- 👉 Prodotto -->
        <template #item.product_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.product?.name }}
            </div>
          </div>
        </template>

        <!-- 👉 Categoria -->
        <template #item.category="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.category }}
            </div>
          </div>
        </template>

        <!-- 👉 Agency -->
        <template #item.mandate_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.mandate?.name }}
            </div>
          </div>
        </template>

        <!-- 👉 Partner Outcome -->
        <template #item.partner_outcome="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.partner_outcome }}
            </div>
          </div>
        </template>

        <!-- 👉 Partner Outcome At -->
        <template #item.partner_outcome_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.partner_outcome_at }}
            </div>
          </div>
        </template>

        <!-- 👉 Paid -->
        <template #item.paid="{ item }">
          <VChip
            :color="item.paid ? 'success' : 'error'"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.paid ? 'SI' : 'NO' }}
          </VChip>
        </template>

        <!-- 👉 Pay -->
        <template #item.pay="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.pay && typeof item.pay === 'number' && item.pay > 0 ? `€ ${item.pay}` : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- 👉 Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VBtn
              size="small"
              color="primary"
              variant="tonal"
              :loading="isDuplicating[item.id]"
              @click.stop="showConfirmDuplicate(item.id)"
              :title="`Duplica pratica ${item.id}`"
            >
              Duplica
            </VBtn>
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
      <!-- SECTION -->
    </VCard>

    <!-- Add the BulkActionDialog component -->
    <PaperworkUpdateStatusesBulkDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isBulkActionDialogOpen"
      :ids="selected"
      @submit="handleBulkAction"
    />

    <!-- 👉 Confirm Duplicate Dialog -->
    <ConfirmDuplicateDialog
      v-if="paperworkToDuplicate"
      v-model:isDialogVisible="isConfirmDuplicateDialogVisible"
      :paperworkData="paperworkToDuplicate"
      @confirm="confirmDuplicate"
    />

    <!-- 👉 Duplicate Success Dialog -->
    <DuplicateSuccessDialog
      v-model:isDialogVisible="isDuplicateSuccessDialogVisible"
      :duplicatedPaperworkId="duplicatedPaperworkId"
      @close="onSuccessModalClose"
      @viewPaperwork="viewDuplicatedPaperwork"
    />
  </section>
</template>

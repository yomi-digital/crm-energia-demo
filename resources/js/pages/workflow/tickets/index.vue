<script setup>
import TicketCategoryChip from '@/components/TicketCategoryChip.vue'

definePage({
  meta: {
    action: 'access',
    subject: 'tickets',
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

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'ID',
    key: 'id',
  },
  {
    title: 'Pratica',
    key: 'paperwork_id',
  },
  {
    title: 'Cliente',
    key: 'customer',
  },
  {
    title: 'Titolo',
    key: 'title',
  },
  {
    title: 'Categoria',
    key: 'category',
  },
  {
    title: 'Stato',
    key: 'status',
  },
  {
    title: 'Creato Da',
    key: 'created_by',
  },
  {
    title: 'Data Creazione',
    key: 'created_at',
  },
  // {
  //   title: '',
  //   key: 'actions',
  //   sortable: false,
  // },
]

const {
  data: ticketsData,
  execute: fetchTickets,
} = await useApi(createUrl('/tickets', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const tickets = computed(() => ticketsData.value.tickets)
const totalTickets = computed(() => ticketsData.value.totalTickets)

const ticketStatusText = (status) => {
  return ['Aperto', 'In Lavorazione', 'Risolto'][status - 1]
}

// Rimossa getCategoryDetails - ora gestita dal componente TicketCategoryChip
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
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="tickets"
        :items-length="totalTickets"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Paperwork -->
        <template #item.id="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'workflow-tickets-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                  :title="item.id"
                >
                  {{ item.id }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 Customer -->
        <template #item.customer="{ item }">
          <div class="d-flex align-center gap-x-2">
            <RouterLink
              v-if="item.paperwork?.customer?.id"
              :to="{ name: 'workflow-customers-id', params: { id: item.paperwork.customer.id } }"
              class="font-weight-medium text-link"
            >
              {{ item.paperwork.customer.name ? item.paperwork.customer.name : item.paperwork.customer.business_name }}
            </RouterLink>
            <div v-else class="text-high-emphasis text-body-1">
              N/A
            </div>
          </div>
        </template>

        <!-- 👉 Title -->
        <template #item.title="{ item }">
          <div class="d-flex align-center gap-x-2">
            <RouterLink
              :to="{ name: 'workflow-tickets-id', params: { id: item.id } }"
              class="font-weight-medium text-link"
              :title="item.title"
            >
              {{ item.title }}
            </RouterLink>
          </div>
        </template>

        <!-- 👉 Category -->
        <template #item.category="{ item }">
          <div class="d-flex align-center gap-x-2">
            <TicketCategoryChip 
              :category="item.category" 
              size="small" 
            />
          </div>
        </template>

        <!-- 👉 Order Code -->
        <template #item.paperwork_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                  :to="{ name: 'workflow-paperworks-id', params: { id: item.paperwork_id } }"
                  class="font-weight-medium text-link"
                  :title="item.paperwork_id"
                >
                  {{ item.paperwork_id }}
                </RouterLink>
            </div>
          </div>
        </template>




        <!-- Status -->
        <template #item.status="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ ticketStatusText(item.status) }}
            </div>
          </div>
        </template>

        <!-- Creato Da -->
        <template #item.created_by="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.created_by ? [item.created_by.name, item.created_by.last_name].join(' ') : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Data Creazione -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.created_at }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn>
            <VIcon icon="tabler-eye" />
          </IconBtn>

          <IconBtn>
            <VIcon icon="tabler-pencil" />
          </IconBtn>

          <IconBtn @click="deleteUser(item.id)">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>

          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem :to="{ name: 'apps-user-view-id', params: { id: item.id } }">
                  <template #prepend>
                    <VIcon icon="tabler-eye" />
                  </template>

                  <VListItemTitle>View</VListItemTitle>
                </VListItem>

                <VListItem link>
                  <template #prepend>
                    <VIcon icon="tabler-pencil" />
                  </template>
                  <VListItemTitle>Edit</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteUser(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" />
                  </template>
                  <VListItemTitle>Delete</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
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
      <!-- SECTION -->
    </VCard>
  </section>
</template>

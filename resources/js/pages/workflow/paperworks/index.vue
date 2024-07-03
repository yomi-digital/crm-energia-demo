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

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
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
  },
  {
    title: 'Cliente',
    key: 'customer_id',
  },
  {
    title: 'Data Inserimento',
    key: 'added_at',
  },
  {
    title: 'Stato Ordine',
    key: 'order_status',
  },
  {
    title: 'Prodotto',
    key: 'product_id',
  },
  {
    title: 'Categoria Brand',
    key: 'brand_category_id',
  },
  {
    title: 'Mandato',
    key: 'mandate_id',
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
    key: 'pda',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: paperworksData,
  execute: fetchPaperworks,
} = await useApi(createUrl('/paperworks', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const paperworks = computed(() => paperworksData.value.paperworks)
const totalPaperworks = computed(() => paperworksData.value.totalPaperworks)

const truncate = (text, length = 30) => {
  if (text.length > length) {
    return text.substring(0, length) + '...'
  }

  return text
}


const addNewUser = async userData => {
  await $api('/apps/users', {
    method: 'POST',
    body: userData,
  })

  // refetch User
  fetchPaperworks()
}

const deleteUser = async id => {
  await $api(`/apps/users/${ id }`, { method: 'DELETE' })

  // refetch User
  fetchPaperworks()
}

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
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <div class="d-flex mb-6">
      <VRow>
        <template
          v-for="(data, id) in widgetData"
          :key="id"
        >
          <VCol
            cols="12"
            md="3"
            sm="6"
          >
            <VCard>
              <VCardText>
                <div class="d-flex justify-space-between">
                  <div class="d-flex flex-column gap-y-1">
                    <div class="text-body-1 text-high-emphasis">
                      {{ data.title }}
                    </div>
                    <div class="d-flex gap-x-2 align-center">
                      <h4 class="text-h4">
                        {{ data.value }}
                      </h4>
                      <div
                        class="text-base"
                        :class="data.change > 0 ? 'text-success' : 'text-error'"
                      >
                        ({{ prefixWithPlus(data.change) }}%)
                      </div>
                    </div>
                    <div class="text-sm">
                      {{ data.desc }}
                    </div>
                  </div>
                  <VAvatar
                    :color="data.iconColor"
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon
                      :icon="data.icon"
                      size="26"
                    />
                  </VAvatar>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>
      </VRow>
    </div>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Plan -->

          <!-- 👉 Select Status -->

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

          <!-- 👉 Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Esporta
          </VBtn>

          <!-- 👉 Add user button -->
          <VBtn
            :to="{ name: 'workflow-paperworks-create' }"
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
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="paperworks"
        :items-length="totalPaperworks"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Paperwork -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'workflow-customers-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                  :title="item.id"
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
              {{ [item.user?.name, item.user?.last_name].join(' ') }}
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
              >
                {{ getCustomerName(item.customer) }}
              </RouterLink>
            </div>
          </div>
        </template>

        <!-- 👉 Added At -->
        <template #item.added_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.added_at }}
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

        <!-- 👉 Brand Category -->
        <template #item.brand_category_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.brand?.typology }}
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

        <!-- 👉 PDA -->
        <template #item.pda="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              &euro; {{ item.pda }}
            </div>
          </div>
        </template>


        <!-- Status -->
        <!-- <template #item.enabled="{ item }">
          <VIcon
              :size="22"
              :icon="item.enabled ? 'tabler-check' : 'tabler-x'"
              :color="resolveUserStatusVariant(item.enabled)"
            />
        </template> -->


        <!-- Team Leader -->
        <!-- <template #item.team_leader="{ item }">
          <VChip
            :color="item.team_leader ? 'success' : 'error'"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.team_leader ? 'SI' : 'NO' }}
          </VChip>
        </template> -->

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
            :total-items="totalPaperworks"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>

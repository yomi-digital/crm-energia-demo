<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'customers',
  },
})


// 👉 Store
const searchQuery = ref('')
const selectedRole = ref()
const selectedTeamLeader = ref()
const selectedStatus = ref()

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
  // {
  //   title: '',
  //   key: 'actions',
  //   sortable: false,
  // },
]

const {
  data: customersData,
  execute: fetchCustomers,
} = await useApi(createUrl('/customers', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const customers = computed(() => customersData.value.customers)
const totalCustomers = computed(() => customersData.value.totalCustomers)

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
  fetchCustomers()
}

const deleteUser = async id => {
  await $api(`/apps/users/${ id }`, { method: 'DELETE' })

  // refetch User
  fetchCustomers()
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
  </section>
</template>

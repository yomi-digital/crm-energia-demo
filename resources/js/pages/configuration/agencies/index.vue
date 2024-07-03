<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'agencies',
  },
})

import AddNewAgencyDrawer from '@/views/configuration/agencies/AddNewAgencyDrawer.vue';
import EditAgencyDrawer from '@/views/configuration/agencies/EditAgencyDrawer.vue';

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedAgency = ref()
const selectedAgencyRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Ragione Sociale',
    key: 'name',
  },
  {
    title: 'Indirizzo',
    key: 'address',
  },
  {
    title: 'P. IVA',
    key: 'vat_number',
  },
  {
    title: 'Email',
    key: 'email',
  },
  {
    title: 'Codice Univoco',
    key: 'unique_code',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: agenciesData,
  execute: fetchAgencies,
} = await useApi(createUrl('/agencies', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const agencies = computed(() => agenciesData.value.agencies)
const totalAgencies = computed(() => agenciesData.value.totalAgencies)

const isAddNewAgencyDrawerVisible = ref(false)
const isEditAgencyDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewAgency = async agencyData => {
  await $api('/agencies', {
    method: 'POST',
    body: agencyData,
  })

  fetchAgencies()
}

const deleteAgency = async id => {
  await $api(`/agencies/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchAgencies()
}

const updateAgency = async agencyData => {
  await $api(`/agencies/${ agencyData.id }`, {
    method: 'PUT',
    body: agencyData,
  })

  fetchAgencies()
}

const selectAgencyForRemove = agency => {
  selectedAgencyRemove.value = agency
  isRemoveDialogVisible.value = true
}

const editAgency = agency => {
  selectedAgency.value = agency
  isEditAgencyDrawerVisible.value = true
}
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

          <!-- 👉 Add agency button -->
          <VBtn
            v-if="$can('create', 'agencies')"
            prepend-icon="tabler-plus"
            @click="isAddNewAgencyDrawerVisible = true"
          >
            Aggiungi Agenzia
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="agencies"
        :items-length="totalAgencies"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Agency -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.name }}
              </h6>
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
          <IconBtn @click="editAgency(item)" v-if="$can('edit', 'agencies')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectAgencyForRemove(item)" v-if="$can('delete', 'agencies')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalAgencies"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Agency -->
    <AddNewAgencyDrawer
      v-if="$can('create', 'agencies')"
      v-model:isDrawerOpen="isAddNewAgencyDrawerVisible"
      @agency-data="addNewAgency"
    />
    <!-- 👉 Edit Agency -->
    <EditAgencyDrawer v-if="selectedAgency && $can('edit', 'agencies')"
      v-model:isDrawerOpen="isEditAgencyDrawerVisible"
      @agency-data="updateAgency"
      :agency="selectedAgency"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedAgencyRemove && $can('delete', 'agencies')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Mandato">
        <VCardText>
          Sei sicuro di voler eliminare il mandato <b>{{ selectedAgencyRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteAgency(selectedAgencyRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

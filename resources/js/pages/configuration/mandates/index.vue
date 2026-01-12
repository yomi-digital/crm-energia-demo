<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'mandates',
  },
})

import AddNewMandateDrawer from '@/views/configuration/mandates/AddNewMandateDrawer.vue';
import EditMandateDrawer from '@/views/configuration/mandates/EditMandateDrawer.vue';

// Verifica se l'utente è backoffice
const loggedInUser = useCookie('userData').value
const isBackoffice = loggedInUser?.roles?.some(role => role.name === 'backoffice')

// 👉 Store
const route = useRoute()
const router = useRouter()
const searchQuery = ref(route.query.q || '')

// Data table options
const itemsPerPage = ref(Number(route.query.itemsPerPage) || 25)
const page = ref(Number(route.query.page) || 1)
const sortBy = ref(route.query.sortBy)
const orderBy = ref(route.query.orderBy)
const selectedMandate = ref()
const selectedMandateRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Update URL on filter change
watch([searchQuery, itemsPerPage, page, sortBy, orderBy], () => {
  router.replace({
    query: {
      ...route.query,
      q: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
    }
  })
})

// Headers
const headers = [
  {
    title: 'Mandato',
    key: 'name',
  },
  {
    title: 'Note',
    key: 'notes',
  },
  {
    title: 'Inizio Incarico',
    key: 'start_date',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: mandatesData,
  execute: fetchMandates,
} = await useApi(createUrl('/mandates', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const mandates = computed(() => mandatesData.value.mandates)
const totalMandates = computed(() => mandatesData.value.totalMandates)

const isAddNewMandateDrawerVisible = ref(false)
const isEditMandateDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewMandate = async mandateData => {
  await $api('/mandates', {
    method: 'POST',
    body: mandateData,
  })

  fetchMandates()
}

const deleteMandate = async id => {
  await $api(`/mandates/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchMandates()
}

const updateMandate = async mandateData => {
  await $api(`/mandates/${ mandateData.id }`, {
    method: 'PUT',
    body: mandateData,
  })

  fetchMandates()
}

const selectMandateForRemove = mandate => {
  selectedMandateRemove.value = mandate
  isRemoveDialogVisible.value = true
}

const editMandate = mandate => {
  selectedMandate.value = mandate
  isEditMandateDrawerVisible.value = true
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

          <!-- 👉 Add mandate button -->
          <VBtn
            v-if="$can('create', 'mandates') && !isBackoffice"
            prepend-icon="tabler-plus"
            @click="isAddNewMandateDrawerVisible = true"
          >
            Aggiungi Mandato
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="mandates"
        :items-length="totalMandates"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Mandate -->
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

        <!-- 👉 Start Date -->
        <template #item.start_date="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.start_date }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editMandate(item)" v-if="$can('edit', 'mandates') && !isBackoffice">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectMandateForRemove(item)" v-if="$can('delete', 'mandates') && !isBackoffice">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalMandates"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Mandate -->
    <AddNewMandateDrawer
      v-if="$can('create', 'mandates') && !isBackoffice"
      v-model:isDrawerOpen="isAddNewMandateDrawerVisible"
      @mandate-data="addNewMandate"
    />
    <!-- 👉 Edit Mandate -->
    <EditMandateDrawer v-if="selectedMandate && $can('edit', 'mandates') && !isBackoffice"
      v-model:isDrawerOpen="isEditMandateDrawerVisible"
      @mandate-data="updateMandate"
      :mandate="selectedMandate"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedMandateRemove && $can('delete', 'mandates') && !isBackoffice"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Mandato">
        <VCardText>
          Sei sicuro di voler eliminare il mandato <b>{{ selectedMandateRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteMandate(selectedMandateRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

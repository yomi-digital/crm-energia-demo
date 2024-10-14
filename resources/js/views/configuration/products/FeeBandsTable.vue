<script setup>
const route = useRoute('configuration-products-id')
const searchQuery = ref('')
import FeebandDrawer from '@/views/configuration/products/FeebandDrawer.vue';

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const isFeebandDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const saveFeeband = data => {
  updateFeeband(data)
  isFeebandDrawerVisible.value = false
}

const deleteFeeband = async id => {
  await $api(`/products/${route.params.id}/feebands/${id}`, {
    method: 'DELETE',
  })
  fetchData()
  isRemoveDialogVisible.value = false
}

const isLoading = ref(false)
const selectedFeeband = ref({
    id: 0,
    start_date: '',
    end_date: '',
    fee_type: 'FISSO',
    management_fee: 0,
    top_partner_fee: 0,
    top_fee: 0,
    partner_fee: 0,
    collaborator_fee: 0,
    smart_fee: 0,
  })
const selectedFeebandRemove = ref(null)

const selectFeebandForRemove = brand => {
  selectedFeebandRemove.value = brand
  isRemoveDialogVisible.value = true
}

// 👉 headers
const headers = [
  {
    title: 'Data inizio',
    key: 'start_date',
  },
  {
    title: 'Tipo',
    key: 'fee_type',
  },
  {
    title: 'Gestione',
    key: 'management_fee',
  },
  {
    title: 'TOP Partner',
    key: 'top_partner_fee',
  },
  {
    title: 'TOP',
    key: 'top_fee',
  },
  {
    title: 'Partner',
    key: 'partner_fee',
  },
  {
    title: 'Smart',
    key: 'smart_fee',
  },
  {
    title: 'Collaboratore',
    key: 'collaborator_fee',
  },
  {
    title: '',
    key: 'actions',
  },
]

const {
  data: feebandsData,
  execute: fetchData,
} = await useApi(createUrl(`/products/${route.params.id}/feebands`, {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const feebands = computed(() => feebandsData.value.feebands)
const totalFeebands = computed(() => feebandsData.value.totalFeebands)

const updateFeeband = async data => {
  if (data.id) {
    await $api(`/products/${route.params.id}/feebands/${data.id}`, {
      method: 'PUT',
      body: data,
    })
  } else {
    await $api(`/products/${route.params.id}/feebands`, {
      method: 'POST',
      body: data,
    })
  }
  fetchData()
}

const addFeeband = () => {
  selectedFeeband.value = {
    id: 0,
    start_date: '',
    end_date: '',
    fee_type: 'FISSO',
    management_fee: 0,
    top_partner_fee: 0,
    top_fee: 0,
    partner_fee: 0,
    collaborator_fee: 0,
    smart_fee: 0,
  }
  isFeebandDrawerVisible.value = true
}

const editFeeband = feeband => {
  selectedFeeband.value = feeband
  isFeebandDrawerVisible.value = true
}
</script>

<template>
  <section v-if="feebands">
    <VCard id="invoice-list">
      <VCardText>
        <div class="d-flex align-center justify-space-between flex-wrap gap-4">
          <div class="text-h5">
            Fasce Compensi
          </div>
          <div class="d-flex align-center gap-x-4">
            <AppSelect
              :model-value="itemsPerPage"
              :items="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'All' },
              ]"
              style="inline-size: 6.25rem;"
              @update:model-value="itemsPerPage = parseInt($event, 10)"
            />

            <!-- 👉 Add feeband -->
            <VBtn
              prepend-icon="tabler-plus"
              @click="addFeeband"
            >
              Aggiungi
            </VBtn>
          </div>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION Datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :loading="isLoading"
        :items-length="totalFeebands"
        :headers="headers"
        :items="feebands"
        item-value="total"
        class="text-no-wrap text-sm rounded-0"
        @update:options="updateOptions"
      >
        <!-- Start date -->
        <template #item.start_date="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.start_date }}
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 End date -->
        <template #item.end_date="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.end_date }}
            </div>
          </div>
        </template>

        <!-- 👉 Type -->
        <template #item.fee_type="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.fee_type }}
            </div>
          </div>
        </template>

        <!-- 👉 Management fee -->
        <template #item.management_fee="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.management_fee }}
            </div>
          </div>
        </template>

        <template #item.top_partner_fee="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.top_partner_fee }}
            </div>
          </div>
        </template>

        <template #item.top_fee="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.top_fee }}
            </div>
          </div>
        </template>

        <template #item.partner_fee="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.partner_fee }}
            </div>
          </div>
        </template>

        <template #item.collaborator_fee="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.collaborator_fee }}
            </div>
          </div>
        </template>

        <template #item.smart_fee="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.smart_fee }}
            </div>
          </div>
        </template>

        <!-- 👉 Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editFeeband(item)">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectFeebandForRemove(item)">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>

        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalFeebands"
          />
        </template>
      </VDataTableServer>
      <!-- !SECTION -->
    </VCard>
    <FeebandDrawer
      v-model:isDrawerOpen="isFeebandDrawerVisible"
      :feeband="selectedFeeband"
      @add-feeband="saveFeeband"
      @update-feeband="saveFeeband"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedFeebandRemove"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Fascia Compenso">
        <VCardText>
          Sei sicuro di voler eliminare questa fascia compenso?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteFeeband(selectedFeebandRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>

</template>

<style lang="scss">
#invoice-list {
  .invoice-list-actions {
    inline-size: 8rem;
  }

  .invoice-list-search {
    inline-size: 12rem;
  }
}
</style>

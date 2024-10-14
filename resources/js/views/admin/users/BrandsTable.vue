<script setup>
const route = useRoute('admin-users-id')
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const isLoading = ref(false)
const isAddDialogVisible = ref(false)
const selectedBrandsAdd = ref([])
const selectedBrandsAddPayLevel = ref()
const selectedBrandsAddRace = ref(0)
const selectedBrandsAddBonus = ref(0)
const isRemoveDialogVisible = ref(false)
const selectedBrandsRemove = ref()

// 👉 headers
const headers = [
{
    title: 'Brand',
    key: 'name',
    sortable: false,
  },
  {
    title: 'Tipo',
    key: 'type',
    sortable: false,
  },
  {
    title: 'Settore',
    key: 'category',
    sortable: false,
  },
  {
    title: 'Compenso',
    key: 'pivot.pay_level',
    sortable: false,
  },
  {
    title: 'Gara',
    key: 'pivot.race',
    sortable: false,
  },
  {
    title: 'Bonus %',
    key: 'pivot.bonus',
    sortable: false,
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: brandsData,
  execute: fetchUserBrands,
} = await useApi(createUrl(`/users/${route.params.id}/brands`, {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const brands = computed(() => brandsData.value.brands)
const totalBrands = computed(() => brandsData.value.totalBrands)

const deleteBrand = async brand => {
  const response = await $api(`/users/${route.params.id}/brands/${brand.id}`, {
    method: 'DELETE',
  })
  isRemoveDialogVisible.value = false

  fetchUserBrands()
}

const selectBrandForRemove = brand => {
  selectedBrandsRemove.value = brand
  isRemoveDialogVisible.value = true
}

const addBrand = async () => {
  const response = await $api(`/users/${route.params.id}/brands`, {
    method: 'POST',
    body: {
      brands: selectedBrandsAdd.value,
      pay_level: selectedBrandsAddPayLevel.value,
      race: selectedBrandsAddRace.value,
      bonus: selectedBrandsAddBonus.value,
    },
  })
  isAddDialogVisible.value = false
  selectedBrandsAdd.value = []
  selectedBrandsAddPayLevel.value = null
  selectedBrandsAddRace.value = 0
  selectedBrandsAddBonus.value = null

  fetchUserBrands()
}

const allBrands = ref([])
const fetchAllBrands = async () => {
  allBrands.value = []
  const response = await $api('/brands?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.brands.length; i++) {
    allBrands.value.push({
      title: [response.brands[i].name, response.brands[i].type || 'N/A', response.brands[i].category || 'N/A'].join(' - '),
      value: response.brands[i].id,
    })
  }
}
await fetchAllBrands()

const payLevels = [
  {
    title: 'N/A',
    value: null,
  },
  {
    title: 'TOP Partner',
    value: 'TOP_PARTNER',
  },
  {
    title: 'TOP',
    value: 'TOP',
  },
  {
    title: 'Partner',
    value: 'PARTNER',
  },
  {
    title: 'Smart',
    value: 'SMART',
  },
  {
    title: 'Collaboratore',
    value: 'COLLABORATORE',
  },
]

const updateBrandPayLevel = async (brand, payLevel) => {
  console.log(brand, payLevel)
  const response = await $api(`/users/${route.params.id}/brands/${brand.id}`, {
    method: 'PATCH',
    body: {
      pay_level: payLevel,
    },
  })

  fetchUserBrands()
}
</script>

<template>
  <section v-if="brands">
    <VCard id="invoice-list">
      <VCardText>
        <div class="d-flex align-center justify-space-between flex-wrap gap-4">
          <div class="text-h5">
            Brands Abilitati
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

            <!-- 👉 Add brand -->
            <VBtn
              prepend-icon="tabler-link"
              @click="isAddDialogVisible = true"
            >
              Abilita Brand
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
        :items-length="totalBrands"
        :headers="headers"
        :items="brands"
        item-value="total"
        class="text-no-wrap text-sm rounded-0"
        @update:options="updateOptions"
      >
        <!-- Brand -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.name }}
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 Type -->
        <template #item.type="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.type }}
            </div>
          </div>
        </template>

        <!-- 👉 Category -->
        <template #item.category="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.category }}
            </div>
          </div>
        </template>

        <!-- 👉 Pay Level -->
        <template #item.pivot.pay_level="{ item }">
          <div class="d-flex align-center gap-x-2">
            <AppSelect
              :model-value="item.pivot.pay_level"
              :items="payLevels"
              @update:model-value="updateBrandPayLevel(item, $event)"
            />
            <!-- <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.pivot.pay_level }}
            </div> -->
          </div>
        </template>

        <!-- 👉 Race -->
        <template #item.pivot.race="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.pivot.race ? 'SI' : 'NO' }}
            </div>
          </div>
        </template>

        <!-- 👉 Bonus -->
        <template #item.pivot.bonus="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.pivot.bonus + '%' || '-' }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="selectBrandForRemove(item)">
            <VIcon
              color="error"
              icon="tabler-unlink"
            />
          </IconBtn>
        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalBrands"
          />
        </template>
      </VDataTableServer>
      <!-- !SECTION -->
    </VCard>

    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedBrandsRemove"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Brand Abilitato">
        <VCardText>
          Sei sicuro di voler eliminare <b>{{ [selectedBrandsRemove.name, selectedBrandsRemove.last_name].join(' ').trim() }}</b> come brand abilitato?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteBrand(selectedBrandsRemove)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isAddDialogVisible"
      width="700"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isAddDialogVisible = !isAddDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Abilita Brand">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <AppAutocomplete
                v-model="selectedBrandsAdd"
                label="Brand"
                :items="allBrands"
                clearable
                multiple
                chips
                closable-chips
                placeholder="Seleziona uno o più Brand"
              />
            </VCol>
            <VCol cols="12">
              <AppSelect
                v-model="selectedBrandsAddPayLevel"
                label="Compenso"
                :items="payLevels"
              />
            </VCol>
            <VCol cols="12">
              <AppSelect
                v-model="selectedBrandsAddRace"
                label="Gara"
                :items="[ { title: 'SI', value: 1 }, { title: 'NO', value: 0 } ]"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="selectedBrandsAddBonus"
                label="Bonus %"
                placeholder="10%"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn @click="addBrand">
            Abilita
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

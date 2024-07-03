<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'links',
  },
})

import AddNewLinkDrawer from '@/views/general/links/AddNewLinkDrawer.vue';
import EditLinkDrawer from '@/views/general/links/EditLinkDrawer.vue';

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedLink = ref()
const selectedLinkRemove = ref()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

function ucfirst(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

// Headers
const headers = [
  {
    title: 'Nome',
    key: 'name',
  },
  {
    title: 'URL',
    key: 'url',
  },
  {
    title: 'Brand',
    key: 'brand_id',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: linksData,
  execute: fetchLinks,
} = await useApi(createUrl('/links', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const links = computed(() => linksData.value.links)
const totalLinks = computed(() => linksData.value.totalLinks)

const isAddNewLinkDrawerVisible = ref(false)
const isEditLinkDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewLink = async linkData => {
  await $api('/links', {
    method: 'POST',
    body: linkData,
  })

  fetchLinks()
}

const deleteLink = async id => {
  await $api(`/links/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchLinks()
}

const updateLink = async linkData => {
  await $api(`/links/${ linkData.id }`, {
    method: 'PUT',
    body: linkData,
  })

  fetchLinks()
}

const selectLinkForRemove = link => {
  selectedLinkRemove.value = link
  isRemoveDialogVisible.value = true
}

const editLink = link => {
  selectedLink.value = link
  isEditLinkDrawerVisible.value = true
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

          <!-- 👉 Add link button -->
          <VBtn
            v-if="$can('create', 'links')"
            prepend-icon="tabler-plus"
            @click="isAddNewLinkDrawerVisible = true"
          >
            Aggiungi Link
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="links"
        :items-length="totalLinks"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Link -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                {{ item.name }}
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 URL -->
        <template #item.url="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <a :href="item.url" target="_blank">{{ item.url }}</a>
            </div>
          </div>
        </template>

        <!-- 👉 Brand -->
        <template #item.brand_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.brand?.name }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="editLink(item)" v-if="$can('edit', 'links')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectLinkForRemove(item)" v-if="$can('delete', 'links')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalLinks"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Link -->
    <AddNewLinkDrawer
      v-if="$can('create', 'links')"
      v-model:isDrawerOpen="isAddNewLinkDrawerVisible"
      @link-data="addNewLink"
    />
    <!-- 👉 Edit Link -->
    <EditLinkDrawer v-if="selectedLink && $can('edit', 'links')"
      v-model:isDrawerOpen="isEditLinkDrawerVisible"
      @link-data="updateLink"
      :link="selectedLink"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedLinkRemove && $can('delete', 'links')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Link">
        <VCardText>
          Sei sicuro di voler eliminare il link <b>{{ selectedLinkRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteLink(selectedLinkRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'documents',
  },
})

import AddNewDocumentDrawer from '@/views/general/documents/AddNewDocumentDrawer.vue';
import EditDocumentDrawer from '@/views/general/documents/EditDocumentDrawer.vue';

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedDocument = ref()
const selectedDocumentRemove = ref()

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
    title: 'Documento',
    key: 'name',
  },
  {
    title: 'Categoria',
    key: 'category',
  },
  {
    title: 'Brand',
    key: 'brand_id',
  },
  {
    title: 'Aggiunto',
    key: 'added_at',
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: documentsData,
  execute: fetchDocuments,
} = await useApi(createUrl('/documents', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const documents = computed(() => documentsData.value.documents)
const totalDocuments = computed(() => documentsData.value.totalDocuments)

const isAddNewDocumentDrawerVisible = ref(false)
const isEditDocumentDrawerVisible = ref(false)
const isRemoveDialogVisible = ref(false)

const addNewDocument = async documentData => {
  await $api('/documents', {
    method: 'POST',
    body: documentData,
  })

  fetchDocuments()
}

const deleteDocument = async id => {
  await $api(`/documents/${ id }`, { method: 'DELETE' })
  isRemoveDialogVisible.value = false

  fetchDocuments()
}

const updateDocument = async documentData => {
  await $api(`/documents/${ documentData.id }`, {
    method: 'PUT',
    body: documentData,
  })

  fetchDocuments()
}

const selectDocumentForRemove = document => {
  selectedDocumentRemove.value = document
  isRemoveDialogVisible.value = true
}

const downloadDocument = async doc => {
  const data = await $api(`/documents/${ doc.id }/download`, {
    method: 'GET',
    responseType: 'blob'
  })

  // Get the extension from document.path
  const extension = doc.url.split('.').pop()
  const fileName = `${ doc.name }.${ extension }`

  const blob = new Blob([data], { type: data.type })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', fileName)
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}

const editDocument = document => {
  selectedDocument.value = document
  isEditDocumentDrawerVisible.value = true
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

          <!-- 👉 Add document button -->
          <VBtn
            v-if="$can('crete', 'documents')"
            prepend-icon="tabler-plus"
            @click="isAddNewDocumentDrawerVisible = true"
          >
            Aggiungi Documento
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="documents"
        :items-length="totalDocuments"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Document -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-capitalize text-high-emphasis text-body-1">
                <!-- <RouterLink
                  :to="{ name: 'apps-user-view-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                > -->
                  {{ item.name }}
                <!-- </RouterLink> -->
              </h6>
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

        <!-- 👉 Brand -->
        <template #item.brand_id="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.brand?.name }}
            </div>
          </div>
        </template>

        <!-- 👉 Added At -->
        <template #item.added_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.added_at }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn v-if="$can('view', 'documents')">
            <a :href="`/api/documents/${item.id}/download?inline=true`" target="_blank">
              <VIcon icon="tabler-eye" />
            </a>
          </IconBtn>
          <IconBtn @click="downloadDocument(item)" v-if="$can('view', 'documents')">
            <VIcon icon="tabler-download" />
          </IconBtn>
          <IconBtn @click="editDocument(item)" v-if="$can('edit', 'documents')">
            <VIcon icon="tabler-pencil" />
          </IconBtn>
          <IconBtn @click="selectDocumentForRemove(item)" v-if="$can('delete', 'documents')">
            <VIcon color="error" icon="tabler-trash" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalDocuments"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New Document -->
    <AddNewDocumentDrawer
      v-if="$can('crete', 'documents')"
      v-model:isDrawerOpen="isAddNewDocumentDrawerVisible"
      @document-data="addNewDocument"
    />
    <!-- 👉 Edit Document -->
    <EditDocumentDrawer v-if="selectedDocument && $can('edit', 'documents')"
      v-model:isDrawerOpen="isEditDocumentDrawerVisible"
      @document-data="updateDocument"
      :document="selectedDocument"
    />
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedDocumentRemove && $can('delete', 'documents')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Documento">
        <VCardText>
          Sei sicuro di voler eliminare il documento <b>{{ selectedDocumentRemove.name }}</b>?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteDocument(selectedDocumentRemove.id)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

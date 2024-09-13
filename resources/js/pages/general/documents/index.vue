<script setup>
import AppSelect from '@/@core/components/app-form-elements/AppSelect.vue';

definePage({
  meta: {
    action: 'access',
    subject: 'documents',
  },
})


// 👉 Store
const searchQuery = ref('')

// Data table options
const selectedDocument = ref()
const selectedDocumentRemove = ref()

const folderName = ref('')
const isCreateFolderDialogVisible = ref(false)
const isCreateBrandFolderDialogVisible = ref(false)
const isUploadDialogVisible = ref(false)

const createFolder = async function () {
  await $api('/documents/new-folder', {
    method: 'POST',
    body: {
      name: folderName.value,
      path: currentPath.value,
    }
  })
  isCreateFolderDialogVisible.value = false
  folderName.value = ''
  refetchDocuments()
}

const fileData = ref([])
const selectedFiles = (files) => {
  fileData.value = files
  isUploadDialogVisible.value = false
  refetchDocuments()
}

const isLoadingDocuments = ref(false)

const currentPath = ref('')
const {
  data: documentsData,
  execute: fetchDocuments,
} = await useApi(createUrl('/documents', {
  query: {
    path: currentPath.value,
  },
}))

const refetchDocuments = async function () {
  isLoadingDocuments.value = true
  let data = await $api('/documents', {
    query: {
      path: currentPath.value,
    },
  })
  documentsData.value = data
  isLoadingDocuments.value = false
}

const brandsWithoutFolder = computed(() => {
  return documentsData.value.brands_without_folder
})

const documents = computed(() => {
  if (searchQuery.value) {
    return documentsData.value.documents.filter(document => {
      return document.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
  }
  return documentsData.value.documents
})

const isRemoveDialogVisible = ref(false)

const deleteDocument = async id => {
  await $api(`/documents/remove`, { method: 'DELETE', body: { path: selectedDocumentRemove.value.path, type: selectedDocumentRemove.value.type } })
  isRemoveDialogVisible.value = false

  refetchDocuments()
}

const selectDocumentForRemove = document => {
  selectedDocumentRemove.value = document
  isRemoveDialogVisible.value = true
}

const downloadDocument = async doc => {
  const data = await $api(`/documents/download`, {
    method: 'GET',
    query: {
      path: doc.path,
    },
    responseType: 'blob'
  })

  // Get the extension from document.path
  const fileName = doc.title

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

const breadcrumbs = ref([
  { title: 'Documenti', active: true, path: '' },
])

const navigateFm = item => {
  if (item.type !== 'dir') {
    return
  }
  breadcrumbs.value = item.breadcrumbs
  // Set active the last on the list
  breadcrumbs.value[breadcrumbs.value.length - 1].active = true
  currentPath.value = item.path
  searchQuery.value = ''
  refetchDocuments()
}

const navigateBreadcrumbs = item => {
  // remove this from breadcrumbs
  const index = breadcrumbs.value.indexOf(item)
  breadcrumbs.value = breadcrumbs.value.slice(0, index +1)
  // Set active the last on the list
  breadcrumbs.value[breadcrumbs.value.length - 1].active = true
  currentPath.value = item.path
  searchQuery.value = ''
  refetchDocuments()
}


</script>

<template>
  <section>
    <VCard class="mb-6">

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <VBreadcrumbs
            :items="breadcrumbs"
          >
            <template v-slot:item="{ item }">
              <VBreadcrumbsItem
                href="#"
                :disabled="item.active"
                @click.prevent="navigateBreadcrumbs(item)"
              >
                {{ item.title }}
              </VBreadcrumbsItem>
            </template>

          </VBreadcrumbs>
        </div>
        <VSpacer />

        <div class="d-flex align-center flex-wrap gap-4">
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Cerca in questa cartella"
            />
          </div>

          <template v-if="currentPath === ''">
            <VBtn
              color="primary"
              v-if="$can('create', 'documents')"
              prepend-icon="tabler-plus"
              @click="isCreateBrandFolderDialogVisible = true"
            >
              Crea Cartella Brand
            </VBtn>
          </template>

          <template v-if="currentPath !== ''">
            <VBtn
              color="primary"
              v-if="$can('create', 'documents')"
              prepend-icon="tabler-plus"
              @click="isCreateFolderDialogVisible = true"
            >
              Crea Cartella
            </VBtn>

            <VBtn
              variant="tonal"
              color="success"
              prepend-icon="tabler-upload"
              @click="isUploadDialogVisible = true"
            >
              Upload
            </VBtn>
          </template>
        </div>
      </VCardText>

      <VDivider />

      <VCardText>
        <VRow>
          <VCol cols="12" style="min-height: 300px;">

            <div style="padding: 0 10px">
              <VProgressLinear
                v-if="isLoadingDocuments"
                indeterminate
              />
            </div>

            <VList
              v-if="! isLoadingDocuments"
              nav
              :lines="false"
            >
              <VListItem
                v-for="item in documents"
                :key="item.title"
                :value="item.title"
                @click="navigateFm(item)"
              >
                <template #prepend>
                  <VIcon :color="item.type === 'dir' ? 'warning' : 'primary'" :icon="item.icon" />
                </template>

                <VListItemTitle>
                  {{ item.title }}
                </VListItemTitle>

                <template #append>
                  <VBtn
                    v-if="item.type === 'file'"
                    variant="text"
                    color="primary"
                    icon="tabler-download"
                    @click.stop="downloadDocument(item)"
                  />
                  <VBtn
                    v-if="$can('edit', 'documents')"
                    variant="text"
                    color="error"
                    icon="tabler-trash"
                    @click.stop="selectDocumentForRemove(item)"
                  />
                </template>
              </VListItem>
            </VList>

            <div v-if="! isLoadingDocuments && ! documents.length">Nessun risultato</div>

          </VCol>
        </VRow>
      </VCardText>

      <!-- SECTION -->
    </VCard>

    <!-- Create Brand folder -->
    <VDialog
      v-model="isCreateBrandFolderDialogVisible"
      width="500"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isCreateBrandFolderDialogVisible = !isCreateBrandFolderDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Crea Cartella Brand">
        <VCardText>
          <AppSelect
            v-model="folderName"
            :items="brandsWithoutFolder"
            label="Seleziona Brand"
            placeholder="Seleziona un brand"
            />
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="primary" @click="createFolder">
            Crea
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Create folder -->
    <VDialog
      v-model="isCreateFolderDialogVisible"
      width="500"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isCreateFolderDialogVisible = !isCreateFolderDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Crea Cartella">
        <VCardText>
          <p>Stai creando una cartella in: <strong>/{{ currentPath }}</strong></p>
          <AppTextField
            v-model="folderName"
            :rules="[requiredValidator]"
            label="Nome Cartella"
            placeholder="Documenti informativi"
          />
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="primary" @click="createFolder">
            Crea
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Upload document -->
    <VDialog
      v-model="isUploadDialogVisible"
      width="800"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isUploadDialogVisible = !isUploadDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Upload Documenti">
        <VCardText>
          <DropZone @dropped="selectedFiles" :scope="'documents/' + currentPath" />
        </VCardText>
      </VCard>
    </VDialog>

    <!-- RemoveDocument -->
    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedDocumentRemove && $can('delete', 'documents')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina">
        <VCardText v-if="selectedDocumentRemove.type === 'dir'">
          Sei sicuro di voler eliminare la cartella <b>{{ selectedDocumentRemove.title }}</b>?<br>
          Eliminando la cartella, tutti i file contenuti verranno eliminati.
        </VCardText>

        <VCardText v-if="selectedDocumentRemove.type === 'file'">
          Sei sicuro di voler eliminare il file <b>{{ selectedDocumentRemove.title }}</b>?<br>
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteDocument(selectedDocumentRemove)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

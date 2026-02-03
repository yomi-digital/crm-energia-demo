<script setup>
import SearchBrand from '@/components/SearchBrand.vue';

definePage({
  meta: {
    action: 'access',
    subject: 'documents',
  },
})

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
const canCreateBrandFolder = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'amministrazione' || role.name === 'backoffice')
const canDeleteFolders = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'amministrazione' || role.name === 'backoffice')
const canCreateFolders = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'amministrazione' || role.name === 'backoffice')

// 👉 Store
const searchQuery = ref('')

// Data table options
const selectedDocument = ref()
const selectedDocumentRemove = ref()

const folderName = ref('')
const isCreateFolderDialogVisible = ref(false)
const isCreateBrandFolderDialogVisible = ref(false)
const isUploadDialogVisible = ref(false)
const isCreatingFolder = ref(false)
const isDeletingDocument = ref(false)

const createFolder = async function () {
  isCreatingFolder.value = true
  try {
    await $api('/documents/new-folder', {
      method: 'POST',
      body: {
        name: folderName.value,
        path: currentPath.value,
      }
    })
    isCreateFolderDialogVisible.value = false
    isCreateBrandFolderDialogVisible.value = false
    folderName.value = ''
    await refetchDocuments()
  } catch (error) {
    console.error('Errore durante la creazione della cartella:', error)
  } finally {
    isCreatingFolder.value = false
  }
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

// Brand completi (nome, categoria, tipo) per raggruppamento in SearchBrand
const allBrands = ref([])

const loadBrands = async () => {
  const response = await $api('/brands', {
    query: {
      itemsPerPage: 999999,
      select: 1,
      enabled: 1,
    },
  })

  allBrands.value = response.brands || []
}

loadBrands()

// Solo i brand che non hanno ancora una cartella in Documenti
const brandsWithoutFolderDetailed = computed(() => {
  if (!allBrands.value.length)
    return []

  const names = new Set(brandsWithoutFolder.value)

  return allBrands.value.filter(brand => names.has(brand.name))
})

const documents = computed(() => {
  if (searchQuery.value) {
    return documentsData.value.documents.filter(document => {
      return document.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
  }
  return documentsData.value.documents
})

/** Raggruppamento per category (solo pagina iniziale, path vuoto). Da API o fallback da documents. */
const groupedDocuments = computed(() => {
  if (currentPath.value !== '') return null
  const docs = documentsData.value?.documents ?? []
  const fromApi = documentsData.value?.grouped_documents
  if (fromApi && Array.isArray(fromApi) && fromApi.length > 0) return fromApi
  // Fallback: raggruppa lato client per category (solo cartelle brand con category)
  const byCategory = new Map()
  for (const item of docs) {
    const cat = item.category ?? null
    const key = cat === null ? '__null__' : cat
    if (!byCategory.has(key)) byCategory.set(key, { category: cat, items: [] })
    byCategory.get(key).items.push(item)
  }
  const groups = Array.from(byCategory.entries())
    .map(([key, g]) => ({ category: key === '__null__' ? null : g.category, items: g.items }))
    .sort((a, b) => {
      if (a.category === null) return 1
      if (b.category === null) return -1
      return (a.category || '').localeCompare(b.category || '')
    })
  return groups.length ? groups : null
})

/** Se true, mostriamo la lista raggruppata per category (fake file system). */
const showGroupedByCategory = computed(() => !!groupedDocuments.value && !searchQuery.value)

/** Indice del pannello accordion aperto (null = tutti chiusi di default). */
const expandedCategoryIndex = ref(null)

const isRemoveDialogVisible = ref(false)

const deleteDocument = async id => {
  isDeletingDocument.value = true
  try {
    await $api(`/documents/remove`, { method: 'DELETE', body: { path: selectedDocumentRemove.value.path, type: selectedDocumentRemove.value.type } })
    isRemoveDialogVisible.value = false
    await refetchDocuments()
  } catch (error) {
    console.error('Errore durante l\'eliminazione:', error)
  } finally {
    isDeletingDocument.value = false
  }
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


const isRenameFolderDialogVisible = ref(false)
const selectedFolderToRename = ref(null)
const newFolderName = ref('')

// Snackbar state
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const openRenameDialog = (item) => {
  selectedFolderToRename.value = item
  newFolderName.value = item.title
  isRenameFolderDialogVisible.value = true
}

const renameFolder = async () => {
  try {
    await $api('/documents/rename', {
      method: 'PUT',
      body: {
        path: selectedFolderToRename.value.path,
        new_name: newFolderName.value
      }
    })
    
    isRenameFolderDialogVisible.value = false
    snackbarMessage.value = 'Cartella rinominata con successo'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true
    
    refetchDocuments()
  } catch (error) {
    console.error(error)
    isRenameFolderDialogVisible.value = false
    
    let errorMessage = 'Errore durante la rinomina della cartella'
    if (error.data && error.data.error) {
      errorMessage = error.data.error
    } else if (error.message) {
        errorMessage = error.message
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const breadcrumbs = ref([
  { title: 'Documenti', active: true, path: '' },
])

// Stack per tracciare la cronologia delle cartelle
const folderHistory = ref([{ path: '', breadcrumbs: [{ title: 'Documenti', active: true, path: '' }] }])
const currentHistoryIndex = ref(0)

// Funzione per navigare a una cartella specifica senza aggiungere alla cronologia
const navigateToFolder = (path, breadcrumbsList) => {
  currentPath.value = path
  breadcrumbs.value = breadcrumbsList.map(b => ({ ...b }))
  breadcrumbs.value[breadcrumbs.value.length - 1].active = true
  searchQuery.value = ''
  refetchDocuments()
}

const navigateFm = item => {
  if (item.type !== 'dir') {
    return
  }
  
  // Rimuovi tutte le voci successive nella cronologia se siamo tornati indietro
  if (currentHistoryIndex.value < folderHistory.value.length - 1) {
    folderHistory.value = folderHistory.value.slice(0, currentHistoryIndex.value + 1)
  }
  
  // Aggiungi la nuova cartella alla cronologia
  const newBreadcrumbs = item.breadcrumbs.map(b => ({ ...b }))
  folderHistory.value.push({
    path: item.path,
    breadcrumbs: newBreadcrumbs
  })
  currentHistoryIndex.value = folderHistory.value.length - 1
  
  // Aggiungi entry alla cronologia del browser
  window.history.pushState(
    { folderPath: item.path, historyIndex: currentHistoryIndex.value },
    '',
    window.location.href
  )
  
  navigateToFolder(item.path, item.breadcrumbs)
}

const navigateBreadcrumbs = item => {
  // Trova l'indice nella cronologia corrispondente a questo breadcrumb
  const targetIndex = folderHistory.value.findIndex(f => f.path === item.path)
  
  if (targetIndex !== -1) {
    // Se trovato nella cronologia, usa quello
    currentHistoryIndex.value = targetIndex
    const targetFolder = folderHistory.value[targetIndex]
    
    // Rimuovi tutte le voci successive
    folderHistory.value = folderHistory.value.slice(0, targetIndex + 1)
    
    // Aggiungi entry alla cronologia del browser
    window.history.pushState(
      { folderPath: item.path, historyIndex: targetIndex },
      '',
      window.location.href
    )
    
    navigateToFolder(item.path, targetFolder.breadcrumbs)
  } else {
    // Se non trovato, crea una nuova entry
    const index = breadcrumbs.value.indexOf(item)
    const newBreadcrumbs = breadcrumbs.value.slice(0, index + 1).map(b => ({ ...b }))
    
    // Rimuovi tutte le voci successive nella cronologia
    if (currentHistoryIndex.value < folderHistory.value.length - 1) {
      folderHistory.value = folderHistory.value.slice(0, currentHistoryIndex.value + 1)
    }
    
    folderHistory.value.push({
      path: item.path,
      breadcrumbs: newBreadcrumbs
    })
    currentHistoryIndex.value = folderHistory.value.length - 1
    
    // Aggiungi entry alla cronologia del browser
    window.history.pushState(
      { folderPath: item.path, historyIndex: currentHistoryIndex.value },
      '',
      window.location.href
    )
    
    navigateToFolder(item.path, newBreadcrumbs)
  }
}

// Gestione del tasto indietro del browser
onMounted(() => {
  // Aggiungi stato iniziale alla cronologia del browser
  window.history.replaceState(
    { folderPath: '', historyIndex: 0 },
    '',
    window.location.href
  )
  
  // Listener per il tasto indietro
  window.addEventListener('popstate', handlePopState)
})

onUnmounted(() => {
  window.removeEventListener('popstate', handlePopState)
})

const handlePopState = (event) => {
  // Verifica che siamo ancora nella pagina documenti
  if (!window.location.pathname.includes('/documents')) {
    return
  }
  
  // Se c'è uno stato salvato nella cronologia del browser
  if (event.state && event.state.historyIndex !== undefined) {
    const targetIndex = event.state.historyIndex
    
    // Verifica che l'indice sia valido
    if (targetIndex >= 0 && targetIndex < folderHistory.value.length) {
      currentHistoryIndex.value = targetIndex
      const targetFolder = folderHistory.value[targetIndex]
      navigateToFolder(targetFolder.path, targetFolder.breadcrumbs)
      return
    }
  }
  
  // Se non c'è uno stato valido, controlla se possiamo tornare indietro nella cronologia
  // Se siamo alla root (indice 0), lascia che il browser gestisca normalmente
  if (currentHistoryIndex.value === 0) {
    // Siamo alla root, il browser gestirà la navigazione normalmente
    return
  }
  
  // Altrimenti, torna alla cartella precedente nella cronologia
  if (currentHistoryIndex.value > 0) {
    currentHistoryIndex.value--
    const targetFolder = folderHistory.value[currentHistoryIndex.value]
    navigateToFolder(targetFolder.path, targetFolder.breadcrumbs)
    
    // Sincronizza lo stato del browser con la cronologia
    window.history.replaceState(
      { folderPath: targetFolder.path, historyIndex: currentHistoryIndex.value },
      '',
      window.location.href
    )
  }
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
              v-if="$can('create', 'documents') && canCreateBrandFolder"
              prepend-icon="tabler-plus"
              @click="isCreateBrandFolderDialogVisible = true"
            >
              Crea Cartella Brand
            </VBtn>
          </template>

          <template v-if="currentPath !== ''">
            <VBtn
              color="primary"
              v-if="$can('create', 'documents') && canCreateFolders"
              prepend-icon="tabler-plus"
              @click="isCreateFolderDialogVisible = true"
            >
              Crea Cartella
            </VBtn>

            <VBtn
              variant="tonal"
              color="success"
              prepend-icon="tabler-upload"
              v-if="isAdmin"
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

            <!-- Accordion per category (solo pagina iniziale, senza ricerca) – chiusi di default -->
            <VExpansionPanels
              v-if="! isLoadingDocuments && showGroupedByCategory"
              v-model="expandedCategoryIndex"
              variant="accordion"
              class="documents-category-accordion"
            >
              <VExpansionPanel
                v-for="(group, groupIndex) in groupedDocuments"
                :key="group.category ?? '__null__'"
                :value="groupIndex"
              >
                <VExpansionPanelTitle>
                  {{ group.category ?? 'Senza categoria' }}
                  <template #actions>
                    <VChip
                      size="small"
                      color="primary"
                      variant="tonal"
                    >
                      {{ group.items.length }}
                    </VChip>
                  </template>
                </VExpansionPanelTitle>
                <VExpansionPanelText>
                  <VList
                    nav
                    :lines="false"
                    class="py-0"
                  >
                    <VListItem
                      v-for="item in group.items"
                      :key="item.path"
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
                          v-if="item.type === 'dir' && currentPath !== '' && ($can('edit', 'documents') || canCreateFolders)"
                          variant="text"
                          color="info"
                          icon="tabler-pencil"
                          @click.stop="openRenameDialog(item)"
                        />
                        <VBtn
                          v-if="item.type === 'file'"
                          variant="text"
                          color="primary"
                          icon="tabler-eye"
                          :href="item.url"
                          target="_blank"
                        />
                        <VBtn
                          v-if="item.type === 'file'"
                          variant="text"
                          color="primary"
                          icon="tabler-download"
                          @click.stop="downloadDocument(item)"
                        />
                        <VBtn
                          v-if="$can('edit', 'documents') && (item.type === 'file' || canDeleteFolders)"
                          variant="text"
                          color="error"
                          icon="tabler-trash"
                          @click.stop="selectDocumentForRemove(item)"
                        />
                      </template>
                    </VListItem>
                  </VList>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>

            <!-- Lista piatta (path non vuoto o con ricerca) -->
            <VList
              v-else-if="! isLoadingDocuments"
              nav
              :lines="false"
            >
              <VListItem
                v-for="item in documents"
                :key="item.path || item.title"
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
                    v-if="item.type === 'dir' && currentPath !== '' && ($can('edit', 'documents') || canCreateFolders)"
                    variant="text"
                    color="info"
                    icon="tabler-pencil"
                    @click.stop="openRenameDialog(item)"
                  />
                  <VBtn
                    v-if="item.type === 'file'"
                    variant="text"
                    color="primary"
                    icon="tabler-eye"
                    :href="item.url"
                    target="_blank"
                  />
                  <VBtn
                    v-if="item.type === 'file'"
                    variant="text"
                    color="primary"
                    icon="tabler-download"
                    @click.stop="downloadDocument(item)"
                  />
                  <VBtn
                    v-if="$can('edit', 'documents') && (item.type === 'file' || canDeleteFolders)"
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
      :persistent="isCreatingFolder"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn 
        :disabled="isCreatingFolder"
        @click="isCreateBrandFolderDialogVisible = !isCreateBrandFolderDialogVisible" 
      />

      <!-- Dialog Content -->
      <VCard title="Crea Cartella Brand">
        <VCardText>
          <SearchBrand
            v-model="folderName"
            :items="brandsWithoutFolderDetailed"
            label="Seleziona Brand"
            placeholder="Seleziona un brand"
            item-title="name"
            item-value="name"
            :disabled="isCreatingFolder"
          />
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn 
            color="primary" 
            :loading="isCreatingFolder"
            :disabled="isCreatingFolder"
            @click="createFolder"
          >
            Crea
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Create folder -->
    <VDialog
      v-model="isCreateFolderDialogVisible"
      width="500"
      :persistent="isCreatingFolder"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn 
        :disabled="isCreatingFolder"
        @click="isCreateFolderDialogVisible = !isCreateFolderDialogVisible" 
      />

      <!-- Dialog Content -->
      <VCard title="Crea Cartella">
        <VCardText>
          <p>Stai creando una cartella in: <strong>/{{ currentPath }}</strong></p>
          <AppTextField
            v-model="folderName"
            :rules="[requiredValidator]"
            label="Nome Cartella"
            placeholder="Documenti informativi"
            :disabled="isCreatingFolder"
          />
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn 
            color="primary" 
            :loading="isCreatingFolder"
            :disabled="isCreatingFolder"
            @click="createFolder"
          >
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
      :persistent="isDeletingDocument"
      v-if="selectedDocumentRemove && $can('delete', 'documents')"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn 
        :disabled="isDeletingDocument"
        @click="isRemoveDialogVisible = !isRemoveDialogVisible" 
      />

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
          <VBtn 
            color="error" 
            :loading="isDeletingDocument"
            :disabled="isDeletingDocument"
            @click="deleteDocument(selectedDocumentRemove)"
          >
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Rename Folder Dialog -->
    <VDialog
      v-model="isRenameFolderDialogVisible"
      width="500"
    >
      <DialogCloseBtn @click="isRenameFolderDialogVisible = !isRenameFolderDialogVisible" />
      <VCard title="Rinomina Cartella">
        <VCardText>
          <AppTextField
            v-model="newFolderName"
            label="Nuovo Nome"
            placeholder="Inserisci il nuovo nome"
          />
        </VCardText>
        <VCardText class="d-flex justify-end">
          <VBtn color="primary" @click="renameFolder">
            Salva
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </section>
</template>

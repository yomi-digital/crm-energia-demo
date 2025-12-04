<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'communications',
  },
})

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
  // {
  //   title: '#',
  //   key: 'id',
  // },
  {
    title: 'Titolo',
    key: 'title',
  },
  {
    title: 'Documenti',
    key: 'documents',
    sortable: false,
  },
  {
    title: 'Inserita',
    key: 'created_at',
  },
  {
    title: 'Inviata',
    key: 'sent_at',
  },
  // {
  //   title: '',
  //   key: 'actions',
  //   sortable: false,
  // },
]

const {
  data: communicationsData,
  execute: fetchCommunications,
} = await useApi(createUrl('/communications', {
  query: {
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const communications = computed(() => communicationsData.value.communications)
const totalCommunications = computed(() => communicationsData.value.totalCommunications)

const downloadFile = async (communicationId, documentId) => {
  try {
    const response = await $api(`/communications/${communicationId}/documents/${documentId}/download`)
    if (response.downloadUrl) {
      const fileResponse = await fetch(response.downloadUrl);
            const blob = await fileResponse.blob();
            
            // 2. Crea un URL temporaneo per il blob
            const url = window.URL.createObjectURL(blob);
            
            // 3. Crea il link e cliccalo
            const link = document.createElement('a');
            link.href = url;
            
            // Cerca di ottenere il nome file dall'URL o usane uno di default
            const fileName = response.fileName || 'download'; 
            link.setAttribute('download', fileName);
            
            document.body.appendChild(link);
            link.click();
            
            // 4. Pulizia
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
    }
  } catch (e) {
    console.error(e)
  }
}

const downloadAllFiles = async (communicationId) => {
  try {
    const response = await $api(`/communications/${communicationId}/documents/download-all`)
    if (response.documents) {
      for (const doc of response.documents) {
        if (doc.downloadUrl) {
          const fileResponse = await fetch(doc.downloadUrl);
          const blob = await fileResponse.blob();
          
          // 2. Crea un URL temporaneo per il blob
          const url = window.URL.createObjectURL(blob);
          
          // 3. Crea il link e cliccalo
          const link = document.createElement('a');
          link.href = url;
          
          // Cerca di ottenere il nome file dall'URL o usane uno di default
          const fileName = doc.name || 'download'; 
          link.setAttribute('download', fileName);
          
          document.body.appendChild(link);
          link.click();
          
          // 4. Pulizia
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
          
          // Attendi 500ms prima del prossimo download
          await new Promise(resolve => setTimeout(resolve, 500));
        }
      }
    }
  } catch (e) {
    console.error(e)
  }
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
          <!-- 👉 Add communication button -->
          <VBtn
            :to="{ name: 'workflow-communications-create' }"
            v-if="$can('create', 'communications')"
            prepend-icon="tabler-plus"
          >
            Nuova Comunicazione
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="communications"
        :items-length="totalCommunications"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Communication -->
        <template #item.id="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'workflow-communications-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                  :title="item.id"
                >
                  {{ item.id }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <!-- 👉 Title -->
        <template #item.title="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              <RouterLink
                :to="{ name: 'workflow-communications-id', params: { id: item.id } }"
                class="font-weight-medium text-link"
                :title="item.id"
              >
                {{ item.title }}
              </RouterLink>
            </div>
          </div>
        </template>

        <!-- Documents -->
        <template #item.documents="{ item }">
          <div v-if="item.documents && item.documents.length" class="d-flex align-center gap-2">
            <VBtn
              v-if="item.documents.length > 1"
              icon="tabler-download"
              variant="text"
              color="primary"
              size="small"
              @click="downloadAllFiles(item.id)"
              :title="`Scarica tutti (${item.documents.length})`"
            />
            <VBtn
              v-else
              icon="tabler-file-download"
              variant="text"
              color="primary"
              size="small"
              @click="downloadFile(item.id, item.documents[0].id)"
              :title="item.documents[0].name"
            />
            <span class="text-caption text-medium-emphasis">
              {{ item.documents.length }} {{ item.documents.length === 1 ? 'file' : 'files' }}
            </span>
          </div>
          <span v-else>-</span>
        </template>

        <!-- 👉 Date -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.created_at }}
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn>
            <VIcon icon="tabler-pencil" />
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalCommunications"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>

<script>
// Definizione delle categorie ticket (esportabile per riutilizzo)
export const ticketCategories = [
  { title: 'Segnalazione Guasto', value: 'SEGNALAZIONE_GUASTO', color: '#f44336' },
  { title: 'Richiesta Informazioni', value: 'RICHIESTA_INFORMAZIONI', color: '#2196f3' },
  { title: 'Problema Tecnico', value: 'PROBLEMA_TECNICO', color: '#ff9800' },
  { title: 'Reclamo', value: 'RECLAMO', color: '#e91e63' },
  { title: 'Richiesta Documentazione', value: 'RICHIESTA_DOCUMENTAZIONE', color: '#9c27b0' },
  { title: 'Modifica Contratto', value: 'MODIFICA_CONTRATTO', color: '#673ab7' },
  { title: 'Disdetta', value: 'DISDETTA', color: '#607d8b' },
  { title: 'Altro', value: 'ALTRO', color: '#795548' },
]
</script>

<script setup>

const props = defineProps({
  paperworkId: {
    required: true,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const ticketData = ref({
  paperwork_id: props.paperworkId,
  title: '',
  description: '',
  category: 'ALTRO', // Default a ALTRO (categoria più neutra)
  attachments: [],
})

// Handler per gestire l'aggiunta di file
const handleFileChange = (files) => {
  console.log('Files ricevuti:', files)
  
  if (files && files.length > 0) {
    if (ticketData.value.attachments.length > 0) {
      const existingFiles = [...ticketData.value.attachments]
      const newFiles = Array.from(files)
      
      newFiles.forEach(newFile => {
        const isDuplicate = existingFiles.some(existingFile => 
          existingFile.name === newFile.name && 
          existingFile.size === newFile.size
        )
        if (!isDuplicate) {
          existingFiles.push(newFile)
        }
      })
      
      ticketData.value.attachments = existingFiles
    } else {
      // Se non ci sono file esistenti, usiamo direttamente i nuovi
      ticketData.value.attachments = Array.from(files)
    }
  }
  
  console.log('File finali dopo aggiunta:', ticketData.value.attachments)
}

// Handler per rimuovere un file specifico
const removeFile = (index) => {
  ticketData.value.attachments.splice(index, 1)
}

const onFormSubmit = async () => {
  // Create FormData to handle file uploads
  const formData = new FormData()
  formData.append('paperwork_id', ticketData.value.paperwork_id)
  formData.append('title', ticketData.value.title)
  formData.append('description', ticketData.value.description)
  formData.append('category', ticketData.value.category)
  
  // Append each attachment
  ticketData.value.attachments.forEach((file, index) => {
    formData.append(`attachments[${index}]`, file)
  })

  await $api('/tickets', {
      method: 'POST',
      body: formData,
  }).then(response => {
    onFormReset()
    emit('submit', response)
  })
}

const onFormReset = () => {
  // Reset form data
  ticketData.value = {
    paperwork_id: props.paperworkId,
    title: '',
    description: '',
    category: 'ALTRO', // Default a ALTRO (categoria più neutra)
    attachments: [],
  }
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Nuovo Ticket
        </h4>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- 👉 Title -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="ticketData.title"
                :rules="[requiredValidator]"
                label="Oggetto"
                placeholder=""
              />
            </VCol>

            <!-- 👉 Category -->
            <VCol
              cols="12"
              md="12"
            >
              <AppSelect
                v-model="ticketData.category"
                label="Categoria"
                :items="ticketCategories"
                item-title="title"
                item-value="value"
              />
            </VCol>

            <!-- 👉 Description -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextarea
                v-model="ticketData.description"
                :rules="[requiredValidator]"
                label="Descrizione"
                placeholder=""
              />
            </VCol>

            <!-- 👉 Attachments -->
            <VCol
              cols="12"
              md="12"
            >
              <VLabel class="text-body-1 mb-2">
                Allegati (Opzionale)
              </VLabel>
              <VFileInput
                :model-value="ticketData.attachments"
                @update:model-value="handleFileChange"
                placeholder="Carica da 1 a N file"
                multiple
                accept="*"
                :rules="[]"
                variant="outlined"
                density="compact"
              />
              
              <!-- Lista dei file selezionati con pulsanti di rimozione -->
              <div v-if="ticketData.attachments.length > 0" class="mt-3">
                <div class="text-caption text-medium-emphasis mb-2">
                  File selezionati ({{ ticketData.attachments.length }}):
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <VChip
                    v-for="(file, index) in ticketData.attachments"
                    :key="`${file.name}-${index}`"
                    size="small"
                    label
                    color="primary"
                    closable
                    @click:close="removeFile(index)"
                  >
                    {{ file.name }} ({{ (file.size / 1024).toFixed(1) }} KB)
                  </VChip>
                </div>
              </div>
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Crea
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="onFormReset"
              >
                Annulla
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

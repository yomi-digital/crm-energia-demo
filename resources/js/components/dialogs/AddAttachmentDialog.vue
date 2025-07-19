<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'upload-attachments',
])

const selectedFiles = ref([])
const uploadingAttachments = ref(false)

const closeDialog = () => {
  emit('update:isDialogVisible', false)
  selectedFiles.value = []
}

const handleFileSelect = (files) => {
  // Aggiungi i nuovi file a quelli esistenti invece di sovrascrivere
  if (files) {
    const newFiles = Array.isArray(files) ? files : [files]
    selectedFiles.value = [...selectedFiles.value, ...newFiles]
  }
}

// Rimuovo questa funzione perché il VFileInput con v-model gestisce già i file
// const handleFileSelect = (event) => {
//   const files = Array.from(event.target.files)
//   selectedFiles.value = [...selectedFiles.value, ...files]
// }

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
}

const uploadAttachments = async () => {
  if (selectedFiles.value.length === 0) return

  try {
    uploadingAttachments.value = true
    
    // Emetti l'evento con i file selezionati
    emit('upload-attachments', selectedFiles.value)
    
    // Chiudi il dialog e resetta i file
    closeDialog()
    
    console.log('Allegati pronti per l\'upload')
  } catch (error) {
    console.error('Errore nella preparazione degli allegati:', error)
    alert('Errore nella preparazione degli allegati')
  } finally {
    uploadingAttachments.value = false
  }
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    v-model="props.isDialogVisible"
    max-width="500"
    persistent
    @update:model-value="dialogModelValueUpdate"
  >
    <VCard>
      <VCardTitle class="py-4 px-6">
        <div class="d-flex justify-space-between align-center">
          <h3 class="text-h6 font-weight-bold">Aggiungi Allegati</h3>
          <VBtn
            icon
            variant="text"
            size="small"
            @click="closeDialog"
          >
            <VIcon>mdi-close</VIcon>
          </VBtn>
        </div>
      </VCardTitle>

      <VCardText class="py-4 px-6">
        <div class="mb-4">
          <VFileInput
            :model-value="[]"
            label="Seleziona file"
            placeholder="Trascina i file qui o clicca per selezionare"
            multiple
            accept="*/*"
            prepend-icon="mdi-file-plus"
            variant="outlined"
            @update:model-value="handleFileSelect"
          />
        </div>

        <!-- Lista file selezionati -->
        <div v-if="selectedFiles.length > 0" class="mb-4">
          <h4 class="text-subtitle-2 font-weight-bold mb-2">File selezionati ({{ selectedFiles.length }}):</h4>
          <div
            v-for="(file, index) in selectedFiles"
            :key="index"
            class="d-flex justify-space-between align-center pa-2 mb-2"
            style="background-color: #f5f5f5; border-radius: 4px;"
          >
            <div class="d-flex align-center">
              <VIcon class="mr-2" color="primary">mdi-file</VIcon>
              <span class="text-body-2">{{ file.name }}</span>
              <span class="text-caption ml-2">({{ (file.size / 1024).toFixed(1) }} KB)</span>
            </div>
            <VBtn
              icon
              variant="text"
              size="x-small"
              color="error"
              @click="removeFile(index)"
            >
              <VIcon>mdi-close</VIcon>
            </VBtn>
          </div>
        </div>
      </VCardText>

      <VCardActions class="py-4 px-6">
        <VSpacer />
        <VBtn
          variant="text"
          @click="closeDialog"
          :disabled="uploadingAttachments"
        >
          Annulla
        </VBtn>
        <VBtn
          color="primary"
          @click="uploadAttachments"
          :loading="uploadingAttachments"
          :disabled="selectedFiles.length === 0"
        >
          <VIcon start>mdi-upload</VIcon>
          Carica Allegati
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
/* Stili specifici se necessari */
</style> 

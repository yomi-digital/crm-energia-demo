<script setup>
const props = defineProps({
  attachments: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Allegati',
  },
  downloadingAttachments: {
    type: Set,
    default: () => new Set(),
  },
  showAddButton: {
    type: Boolean,
    default: true,
  },
  addButtonText: {
    type: String,
    default: 'Aggiungi Allegati',
  },
  ticketId: {
    type: [String, Number],
    required: true,
  },
})

const emit = defineEmits([
  'attachments-updated',
])

const showUploadDialog = ref(false)

const handleAddAttachments = () => {
  showUploadDialog.value = true
}

const handleUploadAttachments = async (files) => {
  try {
    const formData = new FormData()
    files.forEach((file, index) => formData.append('attachments[]', file))
    
    await $api(`/tickets/${props.ticketId}/attachments/add`, {
      method: 'POST',
      body: formData
    })

    // Emetti evento per aggiornare i dati nel componente padre
    emit('attachments-updated')
    
    console.log('Allegati caricati con successo')
  } catch (error) {
    console.error('Errore nel caricamento degli allegati:', error)
    alert('Errore nel caricamento degli allegati: ' + (error.response?.data?.message || error.message || 'Errore sconosciuto'))
  }
}
</script>

<template>
  <div>
    <!-- Header con titolo, count e bottone -->
    <div class="d-flex justify-space-between align-center mb-4">
      <div class="d-flex align-center gap-2">
        <h3 class="text-h6 font-weight-bold">{{ title }}</h3>
        <VChip
          v-if="attachments.length > 0"
          size="small"
          color="primary"
          variant="tonal"
        >
          {{ attachments.length }}
        </VChip>
      </div>
      <VBtn
        v-if="showAddButton"
        variant="outlined"
        color="primary"
        size="small"
        @click="handleAddAttachments"
      >
        {{ addButtonText }}
      </VBtn>
    </div>
    
    <!-- Tabella allegati -->
    <AppAttachmentsList
      :attachments="attachments"
      title=""
      :downloading-attachments="downloadingAttachments"
      :ticket-id="ticketId"
    />
  </div>

  <!-- Dialog per upload allegati -->
  <AddAttachmentDialog
    v-model:is-dialog-visible="showUploadDialog"
    @upload-attachments="handleUploadAttachments"
  />
</template>

<style scoped>
/* Stili specifici se necessari */
</style> 

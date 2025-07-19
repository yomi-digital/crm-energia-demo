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
  showCount: {
    type: Boolean,
    default: true,
  },
  downloadingAttachments: {
    type: Set,
    default: () => new Set(),
  },
  ticketId: {
    type: [String, Number],
    required: true,
  },
})

const emit = defineEmits(['download'])

const downloadAttachment = async (attachment) => {
  try {

    props.downloadingAttachments.add(attachment.id)
    
    const response = await $api(`/tickets/${props.ticketId}/attachments/${attachment.id}/download`, {
      method: 'GET',
      responseType: 'blob'
    })

    const mimeType = attachment.mime_type || 'application/octet-stream'
    const blob = new Blob([response], { type: mimeType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', attachment.name)
    link.style.display = 'none'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    alert('Errore nel download del file: ' + attachment.name)
  } finally {
    // Rimuovi l'allegato dalla lista dei download in corso
    props.downloadingAttachments.delete(attachment.id)
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}
</script>

<template>
  <div v-if="attachments && attachments.length > 0" class="attachments-section">
    <h3 class="text-h6 font-weight-bold mb-3">
      {{ title }}{{ showCount ? ` (${attachments.length})` : '' }}
    </h3>
    
    <VTable class="attachments-table">
      <thead>
        <tr>
          <th class="text-left">Nome File</th>
          <th class="text-left">Dimensione</th>
          <th class="text-center">Azioni</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="attachment in attachments"
          :key="attachment.id"
          class="attachment-row"
        >
          <td class="attachment-name-cell">
            <div class="d-flex align-center">
              <VIcon 
                icon="tabler-paperclip" 
                size="small" 
                class="mr-2 text-primary"
              />
              <span class="attachment-name">{{ attachment.name }}</span>
            </div>
          </td>
          <td class="text-body-2 text-medium-emphasis">
            {{ formatFileSize(attachment.size) }}
          </td>
          <td class="text-center">
            <VBtn
              icon
              size="small"
              color="primary"
              variant="text"
              :disabled="downloadingAttachments.has(attachment.id)"
              @click="downloadAttachment(attachment)"
            >
              <VIcon 
                :icon="downloadingAttachments.has(attachment.id) ? 'tabler-loader-2' : 'tabler-download'" 
                size="small"
                :class="{ 'rotating': downloadingAttachments.has(attachment.id) }"
              />
            </VBtn>
          </td>
        </tr>
      </tbody>
    </VTable>
  </div>
</template>

<style scoped>
.attachments-section {
  margin-bottom: 1.5rem;
}

.attachments-table {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
}

.attachments-table thead th {
  background-color: #f5f5f5;
  font-weight: 600;
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
}

.attachments-table tbody td {
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
}

.attachment-row:hover {
  background-color: #f8f9fa;
}

.attachment-name-cell {
  min-width: 200px;
}

.attachment-name {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
}

.rotating {
  animation: rotate 1s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Responsive design */
@media (max-width: 768px) {
  .attachments-table {
    font-size: 0.875rem;
  }
  
  .attachments-table thead th,
  .attachments-table tbody td {
    padding: 8px 12px;
  }
  
  .attachment-name {
    max-width: 150px;
  }
}
</style> 

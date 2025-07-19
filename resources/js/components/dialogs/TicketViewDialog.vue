<script setup>
const props = defineProps({
  ticketId: {
    required: true,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'attachments-updated',
])

const ticketData = ref({})
const comment = ref('')
const downloadingAttachments = ref(new Set())
const showUploadDialog = ref(false)

const fetchTicket = async () => {
  await $api(`/tickets/${props.ticketId}`).then(response => {
    ticketData.value = response
  })
}
await fetchTicket()

watch(() => props.ticketId, async () => {
  await fetchTicket()
})

const closeTicket = async () => {
  await $api(`/tickets/${props.ticketId}/close`, {
    method: 'PUT',
    body: {
      status: 3,
    },
  }).then(response => {
    fetchTicket()
    emit('submit', response)
  })
}

const onFormSubmit = async () => {
  await $api(`/tickets/${props.ticketId}/comments`, {
      method: 'POST',
      body: {
        comment: comment.value,
      },
  }).then(response => {
    comment.value = ''
    fetchTicket()
  })
}

const onFormReset = () => {
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

const downloadAttachment = async (attachment) => {
  try {
    downloadingAttachments.value.add(attachment.id)
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
    downloadingAttachments.value.delete(attachment.id)
  }
}

const openUploadDialog = () => {
  showUploadDialog.value = true
}

const handleUploadAttachments = async (files) => {
  try {
    const formData = new FormData()
    files.forEach((file, index) => formData.append('attachments[]', file))
    
    const response = await $api(`/tickets/${props.ticketId}/attachments/add`, {
      method: 'POST',
      body: formData
    })
    await fetchTicket()
    emit('attachments-updated')
  } catch (error) {
    alert('Errore nel caricamento degli allegati: ' + (error.response?.data?.message || error.message || 'Errore sconosciuto'))
  }
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="ticket-dialog">
      <VCardTitle class="py-4 px-6">
        <div class="d-flex justify-space-between align-center">
          <h2 class="text-h4 font-weight-bold">{{ ticketData.title }}</h2>
          <div>
            <VChip
              :color="ticketData.status === 3 ? 'success' : 'info'"
              class="text-uppercase mr-2"
            >
              {{ ticketData.status === 3 ? 'Risolto' : 'Aperto' }}
            </VChip>
            <VBtn
              v-if="ticketData.status !== 3 && isAdmin"
              color="success"
              @click="closeTicket"
            >
              Risolvi Ticket
            </VBtn>
          </div>
        </div>
        <div class="text-subtitle-1 mt-2">
          Creato da {{ [ticketData.created_by.name, ticketData.created_by.last_name].join(' ') }} 
          il {{ ticketData.created_at }}
        </div>
      </VCardTitle>

      <VDivider />

      <VCardText class="py-4 px-6">
        <div class="ticket-description mb-6">
          <h3 class="text-h6 font-weight-bold mb-2">Descrizione</h3>
          <div class="text-body-1">{{ ticketData.description }}</div>
        </div>

        <!-- Allegati -->
        <div class="d-flex justify-space-between align-center mb-4">
          <h3 class="text-h6 font-weight-bold">Allegati</h3>
          <VBtn
            color="primary"
            size="small"
            @click="openUploadDialog"
          >
            <VIcon start>mdi-plus</VIcon>
            Aggiungi Allegati
          </VBtn>
        </div>
        
        <AppAttachmentsList
          :attachments="ticketData.attachments"
          title=""
          :downloading-attachments="downloadingAttachments"
          @download="downloadAttachment"
        />

        <VDivider class="mb-6" />

        <div class="comments-section">
          <h3 class="text-h6 font-weight-bold mb-4">Commenti</h3>
          <div
            v-for="comment in ticketData.comments"
            :key="comment.id"
            class="comment mb-4"
          >
            <div class="d-flex justify-space-between align-center mb-1">
              <span class="font-weight-medium">{{ [comment.user.name, comment.user.last_name].join(' ') }}</span>
              <span class="text-caption">{{ comment.created_at }}</span>
            </div>
            <div class="text-body-2 comment-text">{{ comment.comment }}</div>
          </div>
        </div>

        <VDivider class="my-6" />

        <VForm @submit.prevent="onFormSubmit" class="reply-form">
          <VTextField
            v-model="comment"
            label="Aggiungi un commento"
            placeholder="Scrivi la tua risposta..."
            variant="outlined"
            rows="3"
            auto-grow
          />
          <div class="d-flex justify-end mt-4">
            <VBtn
              type="submit"
              color="primary"
              :disabled="!comment.trim()"
            >
              Invia
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>

  <!-- Componente per upload allegati -->
  <AddAttachmentDialog
    v-model:is-dialog-visible="showUploadDialog"
    @upload-attachments="handleUploadAttachments"
  />
</template>

<style scoped>
.ticket-dialog {
  border-radius: 12px;
}

.ticket-description {
  background-color: #f5f5f5;
  border-radius: 8px;
  padding: 16px;
}

.comment {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  background-color: #ffffff;
}

.comment-text {
  white-space: pre-wrap;
}

.reply-form {
  background-color: #f5f5f5;
  border-radius: 8px;
  padding: 16px;
}
</style>

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
])

const ticketData = ref({})
const comment = ref('')
// const isFormVisible = ref(false)

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
    console.log(response)
    // emit('update:isDialogVisible', false)
    // emit('submit', response)
    comment.value = ''
    // isFormVisible.value = false
    fetchTicket()
  })
}

const onFormReset = () => {
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
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
              :color="ticketData.status === 3 ? 'success' : 'warning'"
              class="text-uppercase mr-2"
            >
              {{ ticketData.status === 3 ? 'Risolto' : 'Aperto' }}
            </VChip>
            <VBtn
              v-if="ticketData.status !== 3"
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
              Invia Risposta
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
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

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
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-4 pa-2">
      <VCardText>
        <VBtn type="btn" class="float-right" v-if="ticketData.status != 3"
          color="info"
          @click="closeTicket"
        >
          Risolvi Ticket
        </VBtn>
        <div class="float-right" v-if="ticketData.status == 3"><VIcon icon="tabler-circle-check" size="30" color="success" /></div>
        <!-- 👉 Title -->
        <h4 class="text-h4 mb-2">
          Ticket: {{ ticketData.title }}
        </h4>
        <div style="font-size:.8em">Creato da <b>{{ [ticketData.created_by.name, ticketData.created_by.last_name].join(' ') }}</b> il <i>{{ ticketData.created_at }}</i></div>

        <div style="font-size: 1.3em" class="my-8 px-4">{{ ticketData.description }}</div>

        <!-- 👉 Comments -->
        <VDivider class="my-4" />

        <div>
          <div v-for="comment in ticketData.comments" class="comment">
            <div style="font-size:.8em" class="d-flex justify-space-between">
              <div><b>{{ [comment.user.name, comment.user.last_name].join(' ') }}</b></div>
              <div><i>{{ comment.created_at }}</i></div>
            </div>
            <div style="font-size: 1.2em" class="my-3">{{ comment.comment }}</div>
          </div>
        </div>

        <VDivider class="my-4" />

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- 👉 Comment -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextarea
                v-model="comment"
                rows="3"
                placeholder=""
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap gap-4"
            >
              <VBtn type="submit" color="primary">
                Invia Risposta
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
        <div class="mt-3">

        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.comment {
  padding: 10px 0;
  margin: 10px 0;
  border-bottom: 2px solid #ccc;
}
.comment:last-child {
  border-bottom: none;
}
</style>

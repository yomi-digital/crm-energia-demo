<script setup>
definePage({
  meta: {
    action: 'view',
    subject: 'tickets',
  },
})

const route = useRoute('workflow-tickets-id')

const comment = ref('')

const {
  data: ticketData,
  execute: fetchTicket,
} = await useApi(createUrl(`/tickets/${ route.params.id }`))

const formatDateTime = (inputDate) => {
  const date = new Date(inputDate);
  const formatter = new Intl.DateTimeFormat('it-IT', { dateStyle: 'full', timeStyle: 'short'});
  return formatter.format(date);
}
const closeTicket = async () => {
  await $api(`/tickets/${route.params.id}/close`, {
    method: 'PUT',
    body: {
      status: 3,
    },
  }).then(response => {
    fetchTicket()
  })
}

const commentTicket = async () => {
  await $api(`/tickets/${route.params.id}/comments`, {
      method: 'POST',
      body: {
        comment: comment.value,
      },
  }).then(response => {
    comment.value = ''
    fetchTicket()
  })
}

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
</script>

<template>
  <div>
    <div class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
      <div>
        <div class="d-flex gap-2 align-center mb-2 flex-wrap">
          <h5 class="text-h5">
            Ticket #{{ route.params.id }} - Pratica #{{ ticketData.paperwork_id }}
          </h5>
        </div>
      </div>
    </div>

    <VRow>
      <VCol
        cols="12"
        md="12"
      >
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

          </VCardText>
        </VCard>

        <VCard class="mt-6">
          <VCardText class="py-4 px-6">

            <div class="comments-section">
              <h3 class="text-h6 font-weight-bold mb-4">Commenti</h3>
              <span class="text-caption mb-4" v-if="!ticketData.comments.length">
                Nessun commento
              </span>
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

            <VForm @submit.prevent="commentTicket" class="reply-form">
              <AppTextarea
                v-model="comment"
                label="Aggiungi un commento"
                placeholder="Scrivi la tua risposta..."
                variant="outlined"
                rows="3"
                auto-grow
              />
              <div class="d-flex justify-start mt-4">
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
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import TicketCategoryChip from '@/components/TicketCategoryChip.vue'

definePage({
  meta: {
    action: 'view',
    subject: 'tickets',
  },
})

const route = useRoute('workflow-tickets-id')

const comment = ref('')
const showConfirmDialog = ref(false)
const downloadingAttachments = ref(new Set())

const {
  data: ticketData,
  execute: fetchTicket,
} = await useApi(createUrl(`/tickets/${ route.params.id }`))

const formatDateTime = (inputDate) => {
  const date = new Date(inputDate);
  const formatter = new Intl.DateTimeFormat('it-IT', { dateStyle: 'full', timeStyle: 'short'});
  return formatter.format(date);
}

const handleCloseTicket = () => {
  showConfirmDialog.value = true
}

const closeTicket = async () => {
  showConfirmDialog.value = false
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

// Rimossa getCategoryDetails - ora gestita dal componente TicketCategoryChip

// Rimossa la funzione downloadAttachment duplicata - ora gestita in AppAttachmentsList

// Rimossa la logica di upload duplicata - ora gestita in AppAttachmentZone
</script>

<template>
  <div>
    <div class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
      <div>
        <div class="d-flex gap-2 align-center mb-2 flex-wrap">
          <h5 class="text-h5">
            Ticket #{{ route.params.id }} - Pratica #{{ ticketData?.paperwork_id }}
          </h5>
        </div>
      </div>
    </div>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <VCard class="ticket-dialog">
          <VCardTitle class="py-4 px-6">
            <div class="d-flex justify-space-between align-center">
              <div>
                <h2 class="text-h4 font-weight-bold mb-2">{{ ticketData?.title }}</h2>
                <div class="d-flex align-center gap-2">
                  <TicketCategoryChip 
                    :category="ticketData?.category" 
                    size="small" 
                  />
                </div>
              </div>
              <div>
                <VChip
                  :color="ticketData?.status === 3 ? 'success' : 'info'"
                  class="text-uppercase mr-2"
                >
                  {{ ticketData?.status === 3 ? 'Risolto' : 'Aperto' }}
                </VChip>
                <VBtn
                  v-if="ticketData?.status !== 3 && isAdmin"
                  color="success"
                  @click="handleCloseTicket"
                >
                  Risolvi Ticket
                </VBtn>
              </div>
            </div>
            <div class="text-subtitle-1 mt-2">
              Creato da {{ [ticketData?.created_by?.name, ticketData?.created_by?.last_name].join(' ') }} 
              il {{ ticketData?.created_at }}
            </div>
          </VCardTitle>

          <VDivider />

          <VCardText class="py-4 px-6">
            <div class="ticket-description mb-6">
              <h3 class="text-h6 font-weight-bold mb-2">Descrizione</h3>
              <div class="text-body-1">{{ ticketData?.description }}</div>
            </div>

          </VCardText>
        </VCard>

        <!-- Allegati -->
        <VCard class="mt-6">
          <VCardText class="py-4 px-6">
            <AppAttachmentZone
              :attachments="ticketData.attachments"
              title="Allegati"
              :downloading-attachments="downloadingAttachments"
              :ticket-id="route.params.id"
              @attachments-updated="fetchTicket"
            />
          </VCardText>
        </VCard>

        <!-- Commenti -->
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

      <VCol
        cols="12"
        md="4"
      >
        <!-- Paperwork Section -->
        <VCard class="mb-6">
          <VCardText class="py-4 px-6">
            <div class="d-flex flex-column gap-2">
              <h5 class="text-h5 mb-4">
                Dettagli Pratica
              </h5>
              <div>
                <RouterLink
                  :to="{ name: 'workflow-paperworks-id', params: { id: ticketData?.paperwork?.id } }"
                >
                  #{{ ticketData?.paperwork?.id }}
                </RouterLink>
              </div>
              <div>
                <span class="font-weight-medium">ID Pratica:</span>
                {{ ticketData?.paperwork?.order_code || 'N/A' }}
              </div>
              <div>
                <span class="font-weight-medium">Prodotto:</span>
                <span class="ml-2">{{ ticketData?.paperwork?.product?.name }}</span>
              </div>
              <div>
                <span class="font-weight-medium">Account/POD/PDR:</span>
                <span class="ml-2">{{ ticketData?.paperwork?.account_pod_pdr || 'N/A' }}</span>
              </div>
              <div>
                <span class="font-weight-medium">Stato:</span>
                <VChip
                  :color="ticketData?.paperwork?.order_status === 'SOSPESO' ? 'warning' : 'info'"
                  size="small"
                  class="ml-2"
                >
                  {{ ticketData?.paperwork?.order_status }}
                </VChip>
              </div>
              <div v-if="ticketData?.paperwork?.order_substatus">
                <span class="font-weight-medium">Sottostato:</span>
                <VChip
                  color="error"
                  size="small"
                  class="ml-2"
                >
                  {{ ticketData?.paperwork?.order_substatus }}
                </VChip>
              </div>
              <div>
                <span class="font-weight-medium">Tipo:</span>
                <span class="ml-2">{{ ticketData?.paperwork?.type }}</span>
              </div>
              <div>
                <span class="font-weight-medium">Categoria:</span>
                <span class="ml-2">{{ ticketData?.paperwork?.category }}</span>
              </div>
            </div>
          </VCardText>
        </VCard>

        <!-- Customer Section -->
        <VCard>
          <VCardText class="py-4 px-6">
            <div class="d-flex flex-column gap-y-6">
              <h5 class="text-h5">
                Dettagli Cliente
              </h5>
              <div class="d-flex align-center gap-x-3">
                <div>
                  <h6 class="text-h6">
                    <RouterLink
                      :to="{ name: 'workflow-customers-id', params: { id: ticketData?.paperwork?.customer?.id } }"
                      class="font-weight-medium text-link"
                    >
                      <template v-if="ticketData?.paperwork?.customer?.name">
                        {{ [ticketData?.paperwork?.customer?.name, ticketData?.paperwork?.customer?.last_name].join(' ') }}
                      </template>
                      <template v-if="ticketData?.paperwork?.customer?.business_name">
                        {{ ticketData?.paperwork?.customer?.business_name }}
                      </template>
                    </RouterLink>
                  </h6>
                  <div class="text-body-1" v-if="ticketData?.paperwork?.customer?.tax_id_code">
                    CF: {{ ticketData?.paperwork?.customer?.tax_id_code }}
                  </div>
                  <div class="text-body-1" v-if="ticketData?.paperwork?.customer?.vat_number">
                    P.IVA: {{ ticketData?.paperwork?.customer?.vat_number }}
                  </div>
                </div>
              </div>

              <div class="d-flex flex-column gap-y-1">
                <div class="d-flex justify-space-between align-center">
                  <h6 class="text-h6">
                    Informazioni di contatto
                  </h6>
                </div>
                <span>Email: {{ ticketData?.paperwork?.customer?.email || 'N/A' }}</span>
                <span v-if="ticketData?.paperwork?.customer?.pec">PEC: {{ ticketData?.paperwork?.customer?.pec }}</span>
                <span>Telefono: {{ ticketData?.paperwork?.customer?.phone || 'N/A' }}</span>
                <span>Mobile: {{ ticketData?.paperwork?.customer?.mobile || 'N/A' }}</span>
              </div>

              <div class="d-flex flex-column gap-y-1">
                <div class="d-flex justify-space-between align-center">
                  <h6 class="text-h6">
                    Indirizzo
                  </h6>
                </div>
                <span>{{ [ticketData?.paperwork?.customer?.address, ticketData?.paperwork?.customer?.city, ticketData?.paperwork?.customer?.province, ticketData?.paperwork?.customer?.region, ticketData?.paperwork?.customer?.zip].filter(a => a).join(', ') }}</span>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VDialog
      v-model="showConfirmDialog"
      max-width="500"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-4">
          Conferma chiusura ticket
        </VCardTitle>
        <VCardText class="pa-4">
          Sei sicuro di voler chiudere questo ticket? Questa azione non può essere annullata.
        </VCardText>
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            color="grey-darken-1"
            variant="text"
            @click="showConfirmDialog = false"
          >
            Annulla
          </VBtn>
          <VBtn
            color="success"
            @click="closeTicket"
          >
            Conferma
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

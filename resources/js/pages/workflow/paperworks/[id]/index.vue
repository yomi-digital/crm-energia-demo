<script setup>
import CustomerInfoCard from '@/components/CustomerInfoCard.vue'
import CustomerInfoEditDialog from '@/components/dialogs/CustomerInfoEditDialog.vue'
import { useRoute, useRouter } from 'vue-router'

definePage({
  meta: {
    action: 'view',
    subject: 'paperworks',
  },
})

const route = useRoute('workflow-paperworks-id')
const router = useRouter()
const isConfirmDeleteDialogVisible = ref(false)
const isConfirmPartnerSentDialogVisible = ref(false)
const isPaperworkEditDialogVisible = ref(false)
const isTicketDialogVisible = ref(false)
const selectedTicket = ref(null)
const isTicketViewDialogVisible = ref(false)
const isUploadDialogVisible = ref(false)
const isUpdateStatusesDialogVisible = ref(false)
const isDeleting = ref(false)
const isCustomerInfoEditDialogVisible = ref(false)

const isAdmin = useCookie('userData').value.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
const canViewPayout = useCookie('userData').value.roles.some(role => role.name === 'gestione' || role.name === 'amministrazione')
const isAgent = useCookie('userData').value.roles.some(role => role.name === 'agente')

const {
  data: paperworkData,
  execute: fetchPaperwork,
} = await useApi(createUrl(`/paperworks/${ route.params.id }`))

const formatDateTime = (inputDate) => {
  const date = new Date(inputDate);
  const formatter = new Intl.DateTimeFormat('it-IT', { dateStyle: 'full', timeStyle: 'short'});
  return formatter.format(date);
}

const confirmPaperworkUpdateStatuses = async (data) => {
  fetchPaperwork()
}

const updatePaperwork = async (data) => {
  await $api(`/paperworks/${ route.params.id }`, {
    method: 'PUT',
    body: data,
  })
  fetchPaperwork()
}

const createdTicket = () => {
  fetchPaperwork()
}
const closedTicket = () => {
  fetchPaperwork()
}

const attachmentsUpdated = () => {
  fetchPaperwork()
}

const confirmCustomer = async (customerData) => {
  try {
    await $api(`/customers/${customerData.id}/confirm`, {
      method: 'PUT',
    })
    fetchPaperwork()
  } catch (error) {
    alert('Error confirming customer')
  }
}

const editCustomer = (customerData) => {
  // Open the edit dialog
  isCustomerInfoEditDialogVisible.value = true
}

const updateCustomerInfo = async (data) => {
  try {
    const response = await $api(`/customers/${data.id}`, {
      method: 'PUT',
      body: data,
    })
    fetchPaperwork()
  } catch (error) {
    alert('Error updating customer')
  }
}

const selectedFiles = async (files) => {
  isUploadDialogVisible.value = false
  await $api(`/paperworks/${ route.params.id }/documents`, {
    method: 'POST',
    body: {
      documents: files,
    }
  })
  fetchPaperwork()
}

const deletePaperwork = async () => {
  try {
    isDeleting.value = true
    await $api(`/paperworks/${ route.params.id }`, {
      method: 'DELETE',
    })
    // Redirect to paperworks list after successful deletion
    router.push('/workflow/paperworks')
  } catch (error) {
    console.error('Error deleting paperwork:', error)
  } finally {
    isDeleting.value = false
    isConfirmDeleteDialogVisible.value = false
  }
}

const prettifyField = (field) => {
  switch (field) {
    case 'order_status':
      return 'Stato Ordine'
    case 'order_substatus':
      return 'Sottostato Ordine'
    case 'partner_outcome':
      return 'Esito Partner'
    case 'partner_outcome_at':
      return 'Data Esito Partner'
    case 'partner_sent_at':
      return 'Data Invio'
    case 'order_code':
      return 'ID Pratica'
    case 'energy_type':
      return 'Tipo Utenza'
    case 'mobile_type':
      return 'Tipo Mobile'
    case 'account_pod_pdr':
      return 'Account / POD / PDR'
    case 'annual_consumption':
      return 'Consumo Annuo'
    case 'previous_provider':
      return 'Compagnia Fornitore Uscente'
    case 'type':
      return 'Tipo'
    case 'category':
      return 'Categoria'
    case 'contract_type':
      return 'Tipo Contratto'
    case 'confirmed_at':
      return 'Data Confermata'
    case 'confirmed_by_user_id':
      return 'Confermata da'
    case 'created_by_user_id':
      return 'Creata da'
    case 'updated_by_user_id':
      return 'Modificata da'
    case 'created_at':
      return 'Creata il'
    case 'updated_at':
      return 'Modificata il'
    default:
      return field
  }
}

const downloadDocument = async doc => {
  const data = await $api(`/paperworks/${ route.params.id }/documents/${ doc.id }/download`, {
    method: 'GET',
    query: {
      path: doc.path,
    },
    responseType: 'blob'
  })

  // Get the extension from document.path
  const fileName = doc.name

  const blob = new Blob([data], { type: data.type })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', fileName)
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}
</script>

<template>
  <div v-if="paperworkData">
    <div class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
      <div>
        <div class="d-flex gap-2 align-center mb-2 flex-wrap">
          <h5 class="text-h5">
            Pratica #{{ route.params.id }}
          </h5>
          <div class="d-flex gap-x-2">
            <VChip
              v-if="paperworkData.order_status"
              variant="tonal"
              color="success"
              label
              size="small"
            >
              {{ paperworkData.order_status }}
            </VChip>
            <VChip
              v-if="paperworkData.partner_outcome"
              variant="tonal"
              color="info"
              label
              size="small"
            >
            {{ paperworkData.partner_outcome }}
            </VChip>
          </div>
        </div>
        <div class="text-body-1">
          Creata {{ formatDateTime(paperworkData.created_at) }}
        </div>
      </div>

      <div>
        <VBtn
          v-if="isAdmin"
          color="info"
          @click="isUpdateStatusesDialogVisible = !isUpdateStatusesDialogVisible"
        >
          Aggiorna Stato
        </VBtn>&nbsp;

        <VBtn
          v-if="!isAgent"
          variant="tonal"
          color="error"
          @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible"
        >
          Elimina Pratica
        </VBtn>
      </div>
    </div>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <!-- 👉 Order Details -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title>
              <h5 class="text-h5">
                Dettagli Pratica
              </h5>
            </template>
            <template #append>
              <div v-if="isAdmin" class="text-base font-weight-medium text-primary cursor-pointer" @click="isPaperworkEditDialogVisible = true">
                Modifica
              </div>
            </template>
          </VCardItem>

          <VDivider />

          <VCardText>
            <div class="text-body-1">
              <span class="font-weight-medium">Prodotto: </span>
              <RouterLink
                :to="{ name: 'configuration-products-id', params: { id: paperworkData.product.id } }"
                class="font-weight-medium text-link"
                :title="paperworkData.product.name"
              >{{ paperworkData.product.name || 'N/A' }}</RouterLink>
            </div>
          </VCardText>

          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VCardText>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Tipologia Pratica:</span> {{ paperworkData.contract_type || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Categoria:</span> {{ paperworkData.type || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Tipo Contratto:</span> {{ paperworkData.category || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Tipo Utenza:</span> {{ paperworkData.energy_type || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Tipologia Mobile:</span> {{ paperworkData.mobile_type || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Account / POD / PDR:</span> {{ paperworkData.account_pod_pdr || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Consumo Annuo:</span> {{ paperworkData.annual_consumption || 'N/A' }}
                </div>
                <div class="text-body-1">
                  <span class="font-weight-medium">Compagnia Fornitore Uscente:</span> {{ paperworkData.previous_provider || 'N/A' }}
                </div>
              </VCardText>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VCardText>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">ID Pratica:</span> {{ paperworkData.order_code || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Stato Ordine:</span> {{ paperworkData.order_status || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2" v-if="paperworkData.order_status === 'KO' && paperworkData.order_substatus !== 'SOSPESO'">
                  <span class="font-weight-medium">Sottostato Ordine:</span> {{ paperworkData.order_substatus || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Data Invio:</span> {{ paperworkData.partner_sent_at || 'N/A' }}
                </div>
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Esito Partner:</span> {{ paperworkData.partner_outcome || 'N/A' }}
                </div>
                <div class="text-body-1">
                  <span class="font-weight-medium">Data Esito Partner:</span> {{ paperworkData.partner_outcome_at || 'N/A' }}
                </div>
              </VCardText>
            </VCol>
          </VRow>

          <VDivider />

          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VCardText>
                <div class="text-body-1">
                  <div class="font-weight-medium mb-2">Note</div>
                  <div>{{ paperworkData.notes || 'N/A' }}</div>
                </div>
              </VCardText>
            </VCol>
            <VCol
              v-if="isAdmin"
              cols="12"
              md="6"
            >
              <VCardText>
                <div class="text-body-1">
                  <div class="font-weight-medium mb-2">Note Alfacom</div>
                  <div>{{ paperworkData.owner_notes || 'N/A' }}</div>
                </div>
              </VCardText>
            </VCol>
          </VRow>
        </VCard>

        <!-- 👉 Paperwork Activity -->
        <VCard title="Eventi Pratica">
          <VCardText>
            <VTimeline
              truncate-line="both"
              line-inset="9"
              align="start"
              side="end"
              line-color="primary"
              density="compact"
            >
              <VTimelineItem
                dot-color="primary"
                size="x-small"
              >
                <div class="d-flex justify-space-between align-center">
                  <div class="app-timeline-title">
                    Pratica Creata
                  </div>
                  <div class="app-timeline-meta">
                    {{ formatDateTime(paperworkData.created_at) }}
                  </div>
                </div>
                <p class="app-timeline-text mb-0 mt-3">
                  La pratica è stata creata da {{ [paperworkData.created_by_user?.name, paperworkData.created_by_user?.last_name].join(' ') }}
                </p>
              </VTimelineItem>

              <VTimelineItem
                v-for="event in paperworkData.events"
                :key="event.id"
                dot-color="primary"
                size="x-small"
              >
                <div class="d-flex justify-space-between align-center">
                  <div class="app-timeline-title">
                    {{ event.event_type === 'updated' ? 'Pratica Modificata' : 'Pratica Creata' }} da {{ [event.user.name, event.user.last_name].join(' ') }}
                  </div>
                  <div class="app-timeline-meta">
                    {{ formatDateTime(event.created_at) }}
                  </div>
                </div>
                <ul class="changes-list">
                  <li v-for="change in event.properties.changes" :key="change.field" class="change-item">
                    <div class="change-field">{{ prettifyField(change.field) }}</div>
                    <div class="change-values">
                      <span class="old-value">{{ change.old || 'N/A' }}</span>
                      <span class="arrow">→</span>
                      <span class="new-value">{{ change.new || 'N/A' }}</span>
                    </div>
                  </li>
                </ul>
              </VTimelineItem>
            </VTimeline>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <!-- 👉 Agent -->
        <VCard class="mb-6">
          <VCardItem>
            <VCardTitle>Agente</VCardTitle>
          </VCardItem>

          <VCardText>

            <div class="d-flex gap-x-3">
              <div>
                <VAvatar
                  v-if="paperworkData.user.avatar"
                  :image="paperworkData.user.avatar"
                  size="48"
                  class="mb-3"
                />
                <VAvatar
                  v-else
                  color="primary"
                  size="48" 
                  class="mb-3"
                >
                  {{ paperworkData.user.name?.charAt(0).toUpperCase() }}{{ paperworkData.user.last_name?.charAt(0).toUpperCase() }}
                </VAvatar>
              </div>
              <div>
                <div class="text-body-1">
                  <RouterLink
                    :to="{ name: 'admin-users-id', params: { id: paperworkData.user.id } }"
                    class="font-weight-medium text-link"
                    :title="paperworkData.user.name"
                  >{{ [paperworkData.user.name, paperworkData.user.last_name].join(' ') }}</RouterLink>
                </div>
                <div class="text-body-1">
                  Email: {{ paperworkData.user.email || 'N/A' }}
                </div>
                <div class="text-body-1">
                  Telefono: {{ paperworkData.user.phone || 'N/A' }}
                </div>
                <div class="text-body-1" v-if="canViewPayout">
                  Compenso Stimato: € {{ paperworkData.payout || 'N/A' }}
                </div>
              </div>
            </div>
            
          </VCardText>
        </VCard>

        <!-- 👉 Agent -->
        <VCard class="mb-6">
          <VCardItem>
            <VCardTitle>Backoffice</VCardTitle>
          </VCardItem>

          <VCardText v-if="paperworkData.confirmed_by_user">

            <div class="d-flex gap-x-3">
              <div>
                <VAvatar
                  v-if="paperworkData.confirmed_by_user.avatar"
                  :image="paperworkData.confirmed_by_user.avatar"
                  size="48"
                  class="mb-3"
                />
                <VAvatar
                  v-else
                  color="primary"
                  size="48" 
                  class="mb-3"
                >
                  {{ paperworkData.confirmed_by_user.name?.charAt(0).toUpperCase() }}{{ paperworkData.confirmed_by_user.last_name?.charAt(0).toUpperCase() }}
                </VAvatar>
              </div>
              <div>
                <div class="text-body-1">
                  <RouterLink
                    :to="{ name: 'admin-users-id', params: { id: paperworkData.confirmed_by_user.id } }"
                    class="font-weight-medium text-link"
                    :title="paperworkData.confirmed_by_user.name"
                  >{{ [paperworkData.confirmed_by_user.name, paperworkData.confirmed_by_user.last_name].join(' ') }}</RouterLink>
                </div>
                <div class="text-body-1">
                  Email: {{ paperworkData.confirmed_by_user.email || 'N/A' }}
                </div>
                <div class="text-body-1">
                  Telefono: {{ paperworkData.confirmed_by_user.phone || 'N/A' }}
                </div>
              </div>
            </div>

          </VCardText>
        </VCard>


        <!-- 👉 Customer Details (COMMENTATO - Sostituito da CustomerInfoCard) -->
        <!--
        <VCard class="mb-6">
          <VCardText class="d-flex flex-column gap-y-6">
            <h5 class="text-h5">
              Dettagli Cliente
              <template v-if="! paperworkData.customer.confirmed_at">
                <VChip
                  v-if="! paperworkData.customer.confirmed_at"
                  variant="tonal"
                  color="warning"
                  size="small"
                  class="ml-2"
                >
                  NON CONFERMATO
                </VChip>
                <RouterLink
                  :to="{ name: 'workflow-customers-id', params: { id: paperworkData.customer.id } }"
                  class="text-sm"
                  :title="paperworkData.customer.name"
                >
                  Conferma <VIcon icon="tabler-external-link" />
                </RouterLink>
              </template>
            </h5>

            <div class="d-flex align-center gap-x-3">
              <div>
                <h6 class="text-h6">
                  <RouterLink
                    :to="{ name: 'workflow-customers-id', params: { id: paperworkData.customer.id } }"
                    class="font-weight-medium text-link"
                    :title="paperworkData.customer.name"
                  >
                    <template v-if="paperworkData.customer.name">{{ [paperworkData.customer.name, paperworkData.customer.last_name].join(' ') }}</template>
                    <template v-if="paperworkData.customer.business_name">{{ paperworkData.customer.business_name }}</template>
                  </RouterLink>
                </h6>
                <div class="text-body-1" v-if="paperworkData.customer.tax_id_code">
                  CF: {{ paperworkData.customer.tax_id_code }}
                </div>
                <div class="text-body-1" v-if="paperworkData.customer.vat_number">
                  P.IVA: {{ paperworkData.customer.vat_number }}
                </div>
              </div>
            </div>

            <div class="d-flex gap-x-3 align-center">
              <VAvatar
                variant="tonal"
                color="success"
              >
                <VIcon icon="tabler-shopping-cart" />
              </VAvatar>
              <h6 class="text-h6">
                {{ paperworkData.customer.paperworks.length }}
                <template v-if="paperworkData.customer.paperworks.length > 1">Pratiche</template>
                <template v-else>Pratica</template>
              </h6>
            </div>

            <div class="d-flex flex-column gap-y-1">
              <div class="d-flex justify-space-between align-center">
                <h6 class="text-h6">
                  Informazioni di contatto
                </h6>
              </div>
              <span>Email: {{ paperworkData.customer.email }}</span>
              <span v-if="paperworkData.customer.pec">PEC: {{ paperworkData.customer.pec }}</span>
              <span>Telefono: {{ paperworkData.customer.phone || 'N/A' }}</span>
              <span>Mobile: {{ paperworkData.customer.mobile || 'N/A' }}</span>
            </div>

            <div class="d-flex flex-column gap-y-1">
              <div class="d-flex justify-space-between align-center">
                <h6 class="text-h6">
                  Indirizzo
                </h6>
              </div>
              <span>{{ [paperworkData.customer.address, paperworkData.customer.city, paperworkData.customer.province, paperworkData.customer.region, paperworkData.customer.zip].filter(a => a).join(', ') }}</span>
            </div>
          </VCardText>
        </VCard>
        -->

        <!-- 👉 Customer Info Card -->
        <CustomerInfoCard 
          :customer-data="paperworkData.customer"
          class="mb-6"
          @confirm="confirmCustomer"
          @edit="editCustomer"
        />

        <!-- Documents -->
        <VCard class="mb-6">
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-2">
              <h5 class="text-h5">
                Documenti
              </h5>
              <VBtn
                color="primary"
                size="small"
                @click="isUploadDialogVisible = true"
              >
                <VIcon icon="tabler-upload" /> Upload</VBtn>
            </div>
            <div>
              <VList
                nav
                :lines="false"
                v-if="paperworkData.documents.length"
              >
                <VListItem
                  v-for="doc in paperworkData.documents"
                  :key="doc.id"
                  :value="doc.name"
                >
                  <template #prepend>
                    <VIcon icon="tabler-file" />
                  </template>

                  <template #append>
                    <VIcon icon="tabler-download" @click="downloadDocument(doc)" />
                  </template>

                  <VListItemTitle>
                    {{ doc.name }}
                  </VListItemTitle>
                </VListItem>
              </VList>
              <div v-else>
                Nessun documento trovato
              </div>
            </div>
          </VCardText>
        </VCard>

        <!-- Tickets -->
        <VCard>
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-2">
              <h5 class="text-h5">
                Ticket
              </h5>
              <VBtn
                color="primary"
                size="small"
                @click="isTicketDialogVisible = true"
              >
                <VIcon icon="tabler-mail" /> Apri Ticket</VBtn>
            </div>
            <div>
              <VList
                nav
                :lines="false"
                v-if="paperworkData.tickets.length"
              >
                <VListItem
                  v-for="ticket in paperworkData.tickets"
                  :key="ticket.id"
                  :value="ticket.title"
                  @click="selectedTicket = ticket.id; isTicketViewDialogVisible = true"
                >
                  <template #prepend>
                    <VIcon v-if="ticket.status != 3" icon="tabler-mail-opened" :class="ticket.status == 1 ? 'text-info' : 'text-success'" />
                    <VIcon v-if="ticket.status == 3" icon="tabler-check" class="text-success" />
                  </template>

                  <VListItemTitle>
                    <div style="font-size:1.2em;line-height: 1.5em;" class="d-flex align-center gap-2">
                      {{ ticket.title }}
                      <VChip
                        v-if="ticket.attachments_count > 0"
                        size="x-small"
                        color="primary"
                        variant="tonal"
                      >
                        <VIcon icon="tabler-paperclip" size="small" class="mr-1" />
                        {{ ticket.attachments_count }}
                      </VChip>
                    </div>
                    <div>Creato da <b>{{ [ticket.created_by.name, ticket.created_by.last_name].join(' ') }}</b> il <i>{{ ticket.created_at }}</i></div>
                  </VListItemTitle>
                </VListItem>
              </VList>
              <div v-else>
                Nessun ticket trovato
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Update statuses dialog -->
    <PaperworkUpdateStatusesDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isUpdateStatusesDialogVisible"
      :paperwork-data="paperworkData"
      @submit="confirmPaperworkUpdateStatuses"
    />

    <!-- 👉 Edit paperwor dialog -->
    <PaperworkEditDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isPaperworkEditDialogVisible"
      :paperwork-data="paperworkData"
      @submit="updatePaperwork"
    />
  </div>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Pratica ID {{ route.params.id }} non trovata!
    </VAlert>
  </div>

  <!-- 👉 Create ticket -->
  <TicketCreateDialog
    v-if="$can('create', 'tickets')"
    v-model:isDialogVisible="isTicketDialogVisible"
    :paperwork-id="route.params.id"
    @submit="createdTicket"
  />

  <!-- View Ticket -->
  <TicketViewDialog
    v-if="$can('view', 'tickets') && selectedTicket"
    v-model:isDialogVisible="isTicketViewDialogVisible"
    :ticket-id="selectedTicket"
    @submit="closedTicket"
    @attachments-updated="attachmentsUpdated"
  />

  <!-- Upload document -->
  <VDialog
    v-model="isUploadDialogVisible"
    width="800"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isUploadDialogVisible = !isUploadDialogVisible" />

    <!-- Dialog Content -->
    <VCard title="Upload Documenti Pratica">
      <VCardText>
        <DropZone @dropped="selectedFiles" :scope="'paperworks/' + route.params.id + '/documents'" />
      </VCardText>
    </VCard>
  </VDialog>

  <!-- Delete confirmation dialog -->
  <VDialog
    v-model="isConfirmDeleteDialogVisible"
    width="500"
    persistent
  >
    <VCard title="Conferma Eliminazione">
      <VCardText>
        <p>Sei sicuro di voler eliminare questa pratica? Questa azione non può essere annullata.</p>
        <p>Tutti i dati associati a questa pratica verranno eliminati permanentemente, inclusi documenti, eventi e ticket. Verrà inoltre eliminata dai report associati.</p>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="isConfirmDeleteDialogVisible = false"
          :disabled="isDeleting"
        >
          Annulla
        </VBtn>
        <VBtn
          color="error"
          @click="deletePaperwork"
          :loading="isDeleting"
          :disabled="isDeleting"
        >
          Elimina
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- 👉 Edit customer info dialog -->
  <CustomerInfoEditDialog
    v-if="$can('edit', 'customers')"
    v-model:isDialogVisible="isCustomerInfoEditDialogVisible"
    :customer-data="paperworkData.customer"
    @submit="updateCustomerInfo"
  />
</template>

<style scoped>
.changes-list {
  list-style-type: none;
  padding: 0;
  margin-top: 0.5rem;
}

.change-item {
  background-color: #f3f4f6;
  border-radius: 0.375rem;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
}

.change-field {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.change-values {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
}

.old-value {
  color: #ef4444;
  text-decoration: line-through;
}

.arrow {
  margin: 0 0.5rem;
  color: #6b7280;
}

.new-value {
  color: #10b981;
}

/* Dark mode styles */
@media (prefers-color-scheme: dark) {
  .change-item {
    background-color: #1f2937;
  }
  
  .old-value {
    color: #f87171;
  }
  
  .new-value {
    color: #34d399;
  }
}
</style>

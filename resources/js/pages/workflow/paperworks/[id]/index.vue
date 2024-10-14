<script setup>

const route = useRoute('workflow-paperworks-id')
const isConfirmDialogVisible = ref(false)
const isConfirmPartnerSentDialogVisible = ref(false)
const isPaperworkEditDialogVisible = ref(false)
const isTicketDialogVisible = ref(false)
const selectedTicket = ref(null)
const isTicketViewDialogVisible = ref(false)
const isUploadDialogVisible = ref(false)
const isUpdatePartnerDialogVisible = ref(false)

const {
  data: paperworkData,
  execute: fetchPaperwork,
} = await useApi(createUrl(`/paperworks/${ route.params.id }`))

const formatDateTime = (inputDate) => {
  const date = new Date(inputDate);
  const formatter = new Intl.DateTimeFormat('it-IT', { dateStyle: 'full', timeStyle: 'short'});
  return formatter.format(date);
}

const confirmPaperwork = async (confirm) => {
  if (! confirm) {
    // isConfirmDialogVisible.value = false
    return
  }
  await $api(`/paperworks/${ route.params.id }/confirm`, {
    method: 'PUT',
  })
  fetchPaperwork()
}
const confirmPaperworkPartnerSentOld = async (confirm) => {
  if (! confirm) {
    // isConfirmPartnerSentDialogVisible.value = false
    return
  }
  await $api(`/paperworks/${ route.params.id }/confirm-partner-sent`, {
    method: 'PUT',
  })
  fetchPaperwork()
}

const confirmPaperworkPartnerSent = async () => {
  fetchPaperwork()
}

const confirmPaperworkUpdatePartner = async (data) => {
  await $api(`/paperworks/${ route.params.id }/update-partner`, {
    method: 'PUT',
    body: data,
  })
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
</script>

<template>
  <div>
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
          color="success"
          v-if="! paperworkData.confirmed_at"
          @click="isConfirmDialogVisible = !isConfirmDialogVisible"
        >
          Conferma Pratica
        </VBtn>&nbsp;

        <VBtn
          color="success"
          v-if="paperworkData.confirmed_at && ! paperworkData.partner_sent_at"
          @click="isConfirmPartnerSentDialogVisible = !isConfirmPartnerSentDialogVisible"
        >
          Conferma Inserirmento
        </VBtn>&nbsp;

        <!-- <VBtn
          variant="tonal"
          color="error"
          @click="isConfirmDialogVisible = !isConfirmDialogVisible"
        >
          Elimina Pratica
        </VBtn> -->
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
                <VChip
                  variant="tonal"
                  :color="paperworkData.confirmed_at ? 'success' : 'error'"
                  label
                  size="small"
                >
                  {{ paperworkData.confirmed_at ? 'CONFERMATA da ' + [paperworkData.confirmed_by_user?.name, paperworkData.confirmed_by_user?.last_name].join(' ') : 'NON CONFERMATA' }}
                </VChip>
              </h5>
            </template>
            <template #append>
              <div class="text-base font-weight-medium text-primary cursor-pointer" @click="isPaperworkEditDialogVisible = true">
                Modifica
              </div>
            </template>
          </VCardItem>

          <VDivider />

          <VCardText>
            <div class="text-body-1">
              <span class="font-weight-medium">Prodotto:</span> {{ paperworkData.product.name || 'N/A' }}
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
                <div class="text-body-1 mb-2">
                  <span class="font-weight-medium">Data Inserimento:</span> {{ paperworkData.partner_sent_at || 'N/A' }}
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
        <VCard title="Timeline">
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
                :dot-color="paperworkData.confirmed_at ? 'primary' : 'secondary'"
                size="x-small"
              >
                <div class="d-flex justify-space-between align-center">
                  <span class="app-timeline-title">Pratica Confermata</span>
                  <span class="app-timeline-meta">{{ paperworkData.confirmed_at ? formatDateTime(paperworkData.confirmed_at) : '' }}</span>
                </div>
                <p class="app-timeline-text mb-0 mt-3">
                  <template v-if="paperworkData.confirmed_at">
                    La pratica è stata confermata da {{ [paperworkData.confirmed_by_user?.name, paperworkData.confirmed_by_user?.last_name].join(' ') }}
                  </template>
                  <template v-else>La pratica è in attesa di essere confermata</template>
                </p>
              </VTimelineItem>

              <VTimelineItem
                :dot-color="paperworkData.partner_sent_at ? 'primary' : 'secondary'"
                size="x-small"
              >
                <div class="d-flex justify-space-between align-center">
                  <span class="app-timeline-title">Pratica Inserita</span>
                  <span class="app-timeline-meta">{{ paperworkData.partner_sent_at ? formatDateTime(paperworkData.partner_sent_at) : '' }}</span>
                </div>
                <p class="app-timeline-text mb-0 mt-3">
                  <template v-if="paperworkData.partner_sent_at">
                    La pratica è stata inserita nel sistema partner
                  </template>
                  <template v-else>La pratica è in attesa di essere inserita nel sistema partner</template>
                </p>
              </VTimelineItem>

              <VTimelineItem
                :dot-color="paperworkData.partner_outcome_at ? 'success' : 'secondary'"
                size="x-small"
              >
                <div class="d-flex justify-space-between align-center">
                  <span class="app-timeline-title">Esito Partner</span>
                  <span class="app-timeline-meta">{{ paperworkData.partner_outcome_at ? formatDateTime(paperworkData.partner_outcome_at) : '' }}</span>
                </div>
                <p class="app-timeline-text mb-0 mt-3">
                  <template v-if="paperworkData.partner_outcome_at">
                    Il partner ha dato esito {{ paperworkData.partner_outcome }}
                  </template>
                  <template v-else>In attesa di esito da parte del partner</template>
                </p>
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
            <div class="text-body-1">
              {{ [paperworkData.user.name, paperworkData.user.last_name].join(' ') }}
            </div>
          </VCardText>
        </VCard>

        <!-- 👉 Customer Details  -->
        <VCard class="mb-6">
          <VCardText class="d-flex flex-column gap-y-6">
            <h5 class="text-h5">
              Dettagli Cliente
              <VChip
                v-if="! paperworkData.customer.confirmed_at"
                variant="tonal"
                color="warning"
                size="small"
                class="ml-2"
              >
                NON CONFERMATO
              </VChip>
            </h5>

            <div class="d-flex align-center gap-x-3">
              <div>
                <h6 class="text-h6">
                  <template v-if="paperworkData.customer.name">{{ [paperworkData.customer.name, paperworkData.customer.last_name].join(' ') }}</template>
                  <template v-if="paperworkData.customer.business_name">{{ paperworkData.customer.business_name }}</template>
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
                    <VIcon v-if="ticket.status != 3" icon="tabler-mail-opened" :class="ticket.status == 1 ? 'text-warning' : 'text-success'" />
                    <VIcon v-if="ticket.status == 3" icon="tabler-check" class="text-success" />
                  </template>

                  <VListItemTitle>
                    <div style="font-size:1.2em;line-height: 1.5em;">{{ ticket.title }}</div>
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

    <ConfirmDialog
      v-model:isDialogVisible="isConfirmDialogVisible"
      confirmation-question="Confermare la pratica?"
      cancel-msg="La pratica non è stata confermata."
      cancel-title="Operazione annullata"
      confirm-msg="La pratica è stata confermata."
      confirm-title="Confermata!"
      @confirm="confirmPaperwork"
    />

    <!-- <ConfirmDialog
      v-model:isDialogVisible="isConfirmPartnerSentDialogVisible"
      confirmation-question="Confermare insertimento pratica?"
      cancel-msg="L'inserimento della pratica non è stata confermato."
      cancel-title="Operazione annullata"
      confirm-msg="Inserimento pratica confermato."
      confirm-title="Confermato!"
      @confirm="confirmPaperworkPartnerSent"
    /> -->

    <!-- 👉 Edit paperwor dialog -->
    <PaperworkConfirmInsertDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isConfirmPartnerSentDialogVisible"
      :paperwork-id="paperworkData.id"
      @submit="confirmPaperworkPartnerSent"
    />

    <!-- 👉 Edit paperwor dialog -->
    <PaperworkUpdatePartnerDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isUpdatePartnerDialogVisible"
      :paperwork-data="paperworkData"
      @submit="confirmPaperworkUpdatePartner"
    />

    <!-- 👉 Edit paperwor dialog -->
    <PaperworkEditDialog
      v-if="$can('edit', 'paperworks')"
      v-model:isDialogVisible="isPaperworkEditDialogVisible"
      :paperwork-data="paperworkData"
      @submit="updatePaperwork"
    />
  </div>

  <!-- 👉 Create ticket -->
  <TicketCreateDialog
    v-if="$can('edit', 'users')"
    v-model:isDialogVisible="isTicketDialogVisible"
    :paperwork-id="route.params.id"
    @submit="createdTicket"
  />

  <!-- View Ticket -->
  <TicketViewDialog
    v-if="$can('edit', 'users') && selectedTicket"
    v-model:isDialogVisible="isTicketViewDialogVisible"
    :ticket-id="selectedTicket"
    @submit="closedTicket"
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
</template>

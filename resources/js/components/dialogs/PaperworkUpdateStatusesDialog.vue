<script setup>
import { watch } from 'vue'

const props = defineProps({
  paperworkData: {
    type: Object,
    required: true,
    // default: () => ({
    //   id: 0,
    //   name: '',
    //   last_name: '',
    //   business_name: '',
    //   tax_code_id: '',
    //   vat_number: '',
    //   email: '',
    //   phone: '',
    //   mobile: '',
    //   ateco_code: '',
    //   pec: '',
    //   unique_code: '',
    //   category: '',
    //   address: '',
    //   region: '',
    //   province: '',
    //   city: '',
    //   zip: '',
    // }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const onFormSubmit = async () => {
  await $api(`/paperworks/${ props.paperworkData.id }`, {
    method: 'PUT',
    body: {
      order_code: orderCode.value,
      order_status: orderStatus.value,
      order_substatus: orderSubStatus.value,
      partner_outcome: partnerOutcome.value || null,
      partner_outcome_at: partnerOutcomeAt.value || null,
      partner_sent_at: partnerSentAt.value || null,
      notify_agent: sendNotification.value,
      notes: notes.value,
      owner_notes: ownerNotes.value,
    },
  })
  emit('update:isDialogVisible', false)
  emit('submit', null)
}

const dialogModelStatusValueUpdate = (val) => {
  // emit('update:isDialogVisible', val)
}

// Funzione per formattare le date (ISO dal DB -> DD/MM/YYYY)
const formatDate = (dateString) => {
  if (!dateString) return null
  
  const date = new Date(dateString)
  if (isNaN(date.getTime())) {
    // Se non è una data ISO, prova il vecchio metodo per compatibilità
    if (typeof dateString === 'string' && dateString.includes('-')) {
      return dateString.split('-').reverse().join('/')
    }
    return dateString
  }
  
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  
  return `${day}/${month}/${year}`
}

const orderCode = ref(props.paperworkData.order_code)
const orderStatus = ref(props.paperworkData.order_status)
const orderSubStatus = ref(props.paperworkData.order_substatus)
const partnerOutcome = ref(props.paperworkData.partner_outcome)
const partnerOutcomeAt = ref(formatDate(props.paperworkData.partner_outcome_at))
const partnerSentAt = ref(formatDate(props.paperworkData.partner_sent_at))
const sendNotification = ref(false)
const notes = ref(props.paperworkData.notes || '')
const ownerNotes = ref(props.paperworkData.owner_notes || '')

// Valori iniziali per confrontare i cambiamenti
const initialOrderStatus = ref(props.paperworkData.order_status)
const initialOrderSubStatus = ref(props.paperworkData.order_substatus)
const initialPartnerOutcome = ref(props.paperworkData.partner_outcome)

// Computed per verificare se lo stato, il sottostato o l'esito partner sono stati modificati
const isStatusChanged = computed(() => {
  return orderStatus.value !== initialOrderStatus.value || 
         orderSubStatus.value !== initialOrderSubStatus.value ||
         partnerOutcome.value !== initialPartnerOutcome.value
})

// Watch per resettare il checkbox quando non ci sono più cambiamenti
watch(isStatusChanged, (hasChanged) => {
  if (!hasChanged) {
    sendNotification.value = false
  }
})

// Watch per resettare i valori quando il popup si apre
watch(() => props.isDialogVisible, (isVisible) => {
  if (isVisible) {
    // Reset dei valori quando il popup si apre
    orderCode.value = props.paperworkData.order_code
    orderStatus.value = props.paperworkData.order_status
    orderSubStatus.value = props.paperworkData.order_substatus
    partnerOutcome.value = props.paperworkData.partner_outcome
    partnerOutcomeAt.value = formatDate(props.paperworkData.partner_outcome_at)
    partnerSentAt.value = formatDate(props.paperworkData.partner_sent_at)
    notes.value = props.paperworkData.notes || ''
    ownerNotes.value = props.paperworkData.owner_notes || ''
    
    // Reset dei valori iniziali
    initialOrderStatus.value = props.paperworkData.order_status
    initialOrderSubStatus.value = props.paperworkData.order_substatus
    initialPartnerOutcome.value = props.paperworkData.partner_outcome
    
    // Reset del checkbox
    sendNotification.value = false
  }
})

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: `d/m/Y`,
    position: 'auto',
    enableTime: false,
    allowInput: true,
    monthSelectorType: 'dropdown', // Abilita dropdown per mese e anno
    static: false, // Fondamentale per i modal
    disable: [],
  }

  return config
})

const statuses = ref([
  // {
  //   title: 'CARICATO',
  //   value: 'CARICATO',
  // },
  {
    title: 'INSERITO',
    value: 'INSERITO',
  },
  {
    title: 'DA LAVORARE',
    value: 'DA LAVORARE',
  },
  {
    title: 'SOSPESO',
    value: 'SOSPESO',
  },
  {
    title: 'KO',
    value: 'KO',
  },
  {
    title: 'INVIATO OTP',
    value: 'INVIATO OTP',
  },
  {
    title: 'STORNO',
    value: 'STORNO',
  },
  // {
  //   title: 'OFFERTA CREATA',
  //   value: 'OFFERTA CREATA',
  // },
])

const orderSubStatuses = ref([
  {
    title: 'NON RISPONDE QC',
    value: 'NON RISPONDE QC',
  },
  {
    title: 'INDIRIZZO DA CENSIRE',
    value: 'INDIRIZZO DA CENSIRE',
  },
  {
    title: 'CLIENTE NON ACQUISIBILE',
    value: 'CLIENTE NON ACQUISIBILE',
  },
  {
    title: 'SWICH-OUT',
    value: 'SWICH-OUT',
  },
  {
    title: 'PORTIN - PORTOUT',
    value: 'PORTIN - PORTOUT',
  },
  {
    title: 'KO CREDITO',
    value: 'KO CREDITO',
  },
  {
    title: 'KO TECNICO',
    value: 'KO TECNICO',
  },
  {
    title: 'KO QC',
    value: 'KO QC',
  },
  {
    title: 'KO FRODI',
    value: 'KO FRODI',
  },
  {
    title: 'KO DISCONOSCIMENTO',
    value: 'KO DISCONOSCIMENTO',
  },
  {
    title: 'In Fornitura',
    value: 'In Fornitura',
  },
])

const partnerOutcomes = ref([
  {
    title: 'OK PAGABILE',
    value: 'OK PAGABILE',
  },
  {
    title: 'KO',
    value: 'KO',
  },
  {
    title: 'STORNO',
    value: 'STORNO',
  },
])

const onFormReset = () => {
  orderCode.value = ''
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 700"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelStatusValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="emit('update:isDialogVisible', false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Aggiorna Stato Pratica
        </h4>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <VCol
              cols="12"
              sm="12"
            >
              <AppTextField
                v-model="orderCode"
                label="ID Pratica"
                placeholder="000111222"
              />
            </VCol>
            
            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="orderStatus"
                label="Stato Ordine"
                :items="statuses"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="orderSubStatus"
                label="Sottostato Ordine"
                clearable
                :items="orderSubStatuses"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <div @update:model-value.stop>
                <AppDateTimePicker
                  :key="JSON.stringify(startDateTimePickerConfig)"
                  :config="startDateTimePickerConfig"
                  v-model="partnerSentAt"
                  label="Data Inserimento"
                />
              </div>
            </VCol>
          </VRow>
          <VRow>
            <VCol
              cols="12"
              sm="6"
            >
              <div @update:model-value.stop>
                <AppDateTimePicker
                  :key="JSON.stringify(startDateTimePickerConfig)"
                  :config="startDateTimePickerConfig"
                  v-model="partnerOutcomeAt"
                  label="Data Esito Partner"
                />
              </div>
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="partnerOutcome"
                label="Esito Partner"
                :items="partnerOutcomes"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              sm="12"
            >
              <VCheckbox
                v-model="sendNotification"
                label="Notifica agente cambio sottostato o esito partner"
                color="primary"
                :disabled="!isStatusChanged"
              />
            </VCol>

            <VDivider class="mt-4" />

            <!-- Notes fields -->
            <VCol cols="12" sm="6">
              <AppTextarea
                v-model="notes"
                label="Note"
                placeholder="Inserisci note..."
                rows="4"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextarea
                v-model="ownerNotes"
                label="Note Alfacom"
                placeholder="Inserisci note Alfacom..."
                rows="4"
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Conferma
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="onFormReset"
              >
                Annulla
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

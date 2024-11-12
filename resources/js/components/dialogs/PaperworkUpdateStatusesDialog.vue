<script setup>

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
      partner_outcome: partnerOutcome.value,
      partner_outcome_at: partnerOutcomeAt.value,
      partner_sent_at: partnerSentAt.value,
    },
  })
  emit('update:isDialogVisible', false)
  emit('submit', null)
}

const dialogModelStatusValueUpdate = (val) => {
  // emit('update:isDialogVisible', val)
}

// Convert from YYYY-MM-DD to DD/MM/YYYY
const formatDate = (date) => {
  // Should only be applied if the date is with format YYYY-MM-DD
  if (date.includes('-')) {
    return date.split('-').reverse().join('/')
  }
  return date
}

const orderCode = ref(props.paperworkData.order_code)
const orderStatus = ref(props.paperworkData.order_status)
const orderSubStatus = ref(props.paperworkData.order_substatus)
const partnerOutcome = ref(props.paperworkData.partner_outcome)
const partnerOutcomeAt = ref(formatDate(props.paperworkData.partner_outcome_at))
const partnerSentAt = ref(formatDate(props.paperworkData.partner_sent_at))

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: `d/m/Y`,
  }

  return config
})

const statuses = ref([
  {
    title: 'CARICATO',
    value: 'CARICATO',
  },
  {
    title: 'LAVORATO',
    value: 'LAVORATO',
  },
  {
    title: 'IN LAVORAZIONE',
    value: 'IN LAVORAZIONE',
  },
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
  {
    title: 'OFFERTA CREATA',
    value: 'OFFERTA CREATA',
  },
  {
    title: 'PREVENTIVO INVIATO',
    value: 'PREVENTIVO INVIATO',
  },
])

const orderSubStatuses = ref([
  {
    title: 'OFFERTA CREATA',
    value: 'OFFERTA CREATA',
  },
  {
    title: 'PREVENTIVO INVIATO',
    value: 'PREVENTIVO INVIATO',
  },
  {
    title: 'IN ATTESA DI QC',
    value: 'IN ATTESA DI QC',
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
    title: 'KO DINIEGO',
    value: 'KO DINIEGO',
  },
  {
    title: 'KO CONTESTAZIONE',
    value: 'KO CONTESTAZIONE',
  },
])

const partnerOutcomes = ref([
  {
    title: 'OK PAGABILE',
    value: 'OK PAGABILE',
  },
  {
    title: 'ATTIVO',
    value: 'ATTIVO',
  },
  {
    title: 'KO',
    value: 'KO',
  },
  {
    title: 'STORNO',
    value: 'STORNO',
  },
  {
    title: 'SOSPESO',
    value: 'SOSPESO',
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

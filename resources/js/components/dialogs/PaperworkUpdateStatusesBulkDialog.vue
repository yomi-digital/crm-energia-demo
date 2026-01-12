<script setup>

const props = defineProps({
  ids: {
    type: Array,
    required: true,
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
  await $api(`/paperworks/bulk-update-statuses`, {
    method: 'POST',
    body: {
      ids: props.ids,
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

const dialogModelValueUpdate = val => {
  // emit('update:isDialogVisible', val)
}

const orderStatus = ref('--- MANTIENI ---')
const orderSubStatus = ref('--- MANTIENI ---')
const partnerOutcome = ref('--- MANTIENI ---')
const partnerOutcomeAt = ref(null)
const partnerSentAt = ref(null)

// Funzione per formattare le date ISO dal DB in DD/MM/YYYY
const formatDate = (dateString) => {
  if (!dateString) return null
  
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return date
  
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  
  return `${day}/${month}/${year}`
}

const statuses = ref([
{
    title: '--- MANTIENI ---',
    value: '--- MANTIENI ---',
  },
  {
    title: '--- RIMUOVI ---',
    value: '--- RIMUOVI ---',
  },
  // {
  //   title: 'CARICATO',
  //   value: 'CARICATO',
  // },
  {
    title: 'INSERITO',
    value: 'INSERITO',
  },
  {
    title: 'INVIATO',
    value: 'INVIATO',
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
    title: '--- MANTIENI ---',
    value: '--- MANTIENI ---',
  },
  {
    title: '--- RIMUOVI ---',
    value: '--- RIMUOVI ---',
  },
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
])

const partnerOutcomes = ref([
  {
    title: '--- MANTIENI ---',
    value: '--- MANTIENI ---',
  },
  {
    title: '--- RIMUOVI ---',
    value: '--- RIMUOVI ---',
  },
  {
    title: '',
    value: '',
  },
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

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: `d/m/Y`,
    position: 'auto',
    enableTime: false,
    allowInput: true,
    monthSelectorType: 'dropdown', // Abilita dropdown per mese e anno
    static: false, // Fondamentale per i modal: evita il blocco dei clic
    disable: [],
  }

  return config
})

const onFormReset = () => {
  emit('update:isDialogVisible', false)
  orderCode.value = ''
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 700"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="emit('update:isDialogVisible', false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Aggiorna Stato Pratiche ({{ props.ids.length }} selezionate)
        </h4>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
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
                  v-model="partnerOutcomeAt"
                  label="Data Esito Partner"
                />
              </div>
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
                  label="Data invio partner"
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

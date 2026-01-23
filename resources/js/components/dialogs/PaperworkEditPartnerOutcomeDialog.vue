<script setup>
import { nextTick, watch } from 'vue'

const props = defineProps({
  paperworkData: {
    type: Object,
    required: true,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'partner-outcome-updated',
])

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

const partnerOutcome = ref(props.paperworkData.partner_outcome)
const partnerOutcomeAt = ref(formatDate(props.paperworkData.partner_outcome_at))
const isSaving = ref(false)
const datePickerKey = ref(0) // Key per forzare il re-render del date picker

// Watch per resettare i valori quando il popup si apre
watch(() => props.isDialogVisible, async (isVisible) => {
  if (isVisible) {
    // Reset dei valori quando il popup si apre
    partnerOutcome.value = props.paperworkData.partner_outcome
    partnerOutcomeAt.value = formatDate(props.paperworkData.partner_outcome_at)
    // Forza il re-render del date picker dopo che il dialog è visibile
    await nextTick()
    datePickerKey.value++
  }
})

watch(() => props.paperworkData, (newData) => {
  if (newData) {
    partnerOutcome.value = newData.partner_outcome
    partnerOutcomeAt.value = formatDate(newData.partner_outcome_at)
  }
}, { immediate: true })

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: `d/m/Y`,
    position: 'auto',
    enableTime: false,
    allowInput: true,
    monthSelectorType: 'dropdown', // Abilita dropdown per mese e anno
    static: false, // Fondamentale per i modal: evita il blocco dei clic
    disable: [],
    positionElement: undefined, // Forza il posizionamento corretto
    onOpen: [
      function(selectedDates, dateStr, instance) {
        // Forza position fixed per il calendario quando è dentro un dialog
        if (instance.calendarContainer) {
          instance.calendarContainer.style.position = 'fixed'
        }
      }
    ],
  }

  return config
})

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

const closeDialog = () => {
  emit('update:isDialogVisible', false)
}

const savePartnerOutcome = async () => {
  if (!props.paperworkData?.id) {
    return
  }

  isSaving.value = true
  
  try {
    await $api(`/paperworks/${props.paperworkData.id}`, {
      method: 'PUT',
      body: {
        partner_outcome: partnerOutcome.value || null,
        partner_outcome_at: partnerOutcomeAt.value || null,
      },
    })
    
    emit('partner-outcome-updated')
    closeDialog()
  } catch (error) {
    console.error('Errore durante l\'aggiornamento dell\'esito partner:', error)
    alert('Errore durante l\'aggiornamento dell\'esito partner')
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    @update:model-value="closeDialog"
    :width="$vuetify.display.smAndDown ? 'auto' : 500"
    persistent
  >
    <DialogCloseBtn @click="closeDialog" />

    <VCard class="pa-sm-8 pa-4">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-6">
          Modifica Esito Partner
        </h4>

        <div class="mb-4">
          <p class="text-body-1 mb-4">
            Pratica #{{ paperworkData?.id }}
          </p>

          <VRow>
            <!-- 👉 Esito Partner -->
            <VCol cols="12">
              <AppSelect
                v-model="partnerOutcome"
                label="Esito Partner"
                :items="partnerOutcomes"
                clearable
                placeholder="Seleziona esito partner"
              />
            </VCol>

            <!-- 👉 Data Esito Partner -->
            <VCol cols="12">
              <AppDateTimePicker
                :key="`date-picker-${datePickerKey}-${JSON.stringify(startDateTimePickerConfig)}`"
                :config="startDateTimePickerConfig"
                v-model="partnerOutcomeAt"
                label="Data Esito Partner"
                placeholder="Seleziona data esito partner"
              />
            </VCol>
          </VRow>
        </div>

        <!-- 👉 Actions -->
        <div class="d-flex justify-end gap-3 mt-6">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="closeDialog"
            :disabled="isSaving"
          >
            Annulla
          </VBtn>
          <VBtn
            color="primary"
            @click="savePartnerOutcome"
            :loading="isSaving"
          >
            Salva
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

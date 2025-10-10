<script setup>
const props = defineProps({
  aiPaperwork: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['onRetry'])

const handleRetry = () => {
  emit('onRetry')
}

const alertColor = computed(() => {
  return props.aiPaperwork.status === 8 ? 'warning' : 'error'
})

const alertIcon = computed(() => {
  return props.aiPaperwork.status === 8 ? 'tabler-ban' : 'tabler-alert-circle'
})

const alertTitle = computed(() => {
  if (props.aiPaperwork.status === 8) {
    return 'Documento Annullato'
  }
  return 'Errore durante l\'Elaborazione'
})

const alertMessage = computed(() => {
  if (props.aiPaperwork.status === 8) {
    return 'Questo documento è stato annullato. Puoi riprocessarlo se necessario.'
  }
  return 'Si è verificato un errore durante l\'elaborazione del documento. Puoi riprovare cliccando il pulsante qui sotto.'
})
</script>

<template>
  <VAlert
    :color="alertColor"
    variant="tonal"
    class="mb-4"
  >
    <div class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between">
      <div class="d-flex align-center gap-3 mb-3 mb-md-0">
        <VIcon
          :icon="alertIcon"
          :color="alertColor"
          size="32"
        />
        <div>
          <div class="text-body-1 font-weight-medium mb-1">
            {{ alertTitle }}
          </div>
          <div class="text-body-2 text-medium-emphasis">
            {{ alertMessage }}
          </div>
        </div>
      </div>
      <VBtn
        :color="alertColor"
        variant="elevated"
        prepend-icon="tabler-refresh"
        class="align-self-start align-self-md-auto"
        @click="handleRetry"
      >
        Riprocessa Documento
      </VBtn>
    </div>
  </VAlert>
</template>

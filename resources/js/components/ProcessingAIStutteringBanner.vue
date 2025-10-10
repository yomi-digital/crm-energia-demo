<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
  aiPaperwork: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['onReset'])

const currentTime = ref(new Date())

// Aggiorna il tempo corrente ogni 10 secondi per avere un timer reattivo
let interval = null
onMounted(() => {
  interval = setInterval(() => {
    currentTime.value = new Date()
  }, 10000) // Aggiorna ogni 10 secondi
})

onUnmounted(() => {
  if (interval) {
    clearInterval(interval)
  }
})

const minutesSinceUpdate = computed(() => {
  if (!props.aiPaperwork?.updated_at) return 0
  const now = currentTime.value
  const updated = new Date(props.aiPaperwork.updated_at)
  return Math.floor((now - updated) / 1000 / 60)
})

const isStuck = computed(() => minutesSinceUpdate.value >= 5)

const alertColor = computed(() => {
  return isStuck.value ? 'warning' : 'info'
})

const alertText = computed(() => {
  if (isStuck.value) {
    return `L'elaborazione sembra bloccata. Sono trascorsi ${minutesSinceUpdate.value} minuti dall'ultimo aggiornamento.`
  }
  const remainingMinutes = 5 - minutesSinceUpdate.value
  return `Elaborazione in corso... Sono trascorsi ${minutesSinceUpdate.value} ${minutesSinceUpdate.value === 1 ? 'minuto' : 'minuti'}.`
})

const alertSubtext = computed(() => {
  if (isStuck.value) {
    return 'Puoi resettare la pratica e riavviarla manualmente o attendere il processo automatico.'
  }
  const remainingMinutes = 5 - minutesSinceUpdate.value
  return `Se l'elaborazione non si completa entro ${remainingMinutes} ${remainingMinutes === 1 ? 'minuto' : 'minuti'}, potrai resettarla manualmente.`
})

const handleReset = () => {
  emit('onReset')
}
</script>

<template>
  <VAlert
    :color="alertColor"
    variant="tonal"
    class="mb-4"
  >
    <div class="d-flex align-center justify-space-between">
      <div class="d-flex align-center gap-3">
        <VProgressCircular
          indeterminate
          :color="alertColor"
          size="24"
        />
        <div>
          <div class="text-body-1 font-weight-medium mb-1">
            {{ alertText }}
          </div>
          <div class="text-body-2 text-medium-emphasis">
            {{ alertSubtext }}
          </div>
        </div>
      </div>
      <VBtn
        v-if="isStuck"
        color="warning"
        variant="elevated"
        prepend-icon="tabler-refresh"
        @click="handleReset"
      >
        Resetta Elaborazione
      </VBtn>
    </div>
  </VAlert>
</template>


<script setup>
const props = defineProps({
  selectedNotificationType: {
    type: String,
    required: false,
    default: '',
  },
})

const emit = defineEmits([
  'update:notification-type',
])

const filterOptions = [
  {
    value: 'Calendar',
    icon: 'tabler-calendar',
    tooltip: 'Calendario',
  },
  {
    value: 'Ticket',
    icon: 'tabler-mail-opened',
    tooltip: 'Ticket',
  },
  {
    value: 'Paperwork',
    icon: 'tabler-file-text',
    tooltip: 'Pratiche',
  },
]

const handleFilterClick = (value) => {
  // Se clicco sulla stessa icona già selezionata, la deseleziono (torna a "Tutte")
  if (props.selectedNotificationType === value) {
    emit('update:notification-type', '')
  } else {
    emit('update:notification-type', value)
  }
}
</script>

<template>
  <div class="d-flex justify-center gap-2">
    <VBtn
      v-for="option in filterOptions"
      :key="option.value"
      :variant="props.selectedNotificationType === option.value ? 'elevated' : 'text'"
      :color="props.selectedNotificationType === option.value ? 'primary' : 'default'"
      size="small"
      icon
      @click="handleFilterClick(option.value)"
    >
      <VIcon :icon="option.icon" />
      <VTooltip activator="parent" location="bottom">
        {{ props.selectedNotificationType === option.value && option.value !== '' ? 
            `Deseleziona ${option.tooltip}` : 
            option.tooltip }}
      </VTooltip>
    </VBtn>
  </div>
</template>

<style lang="scss" scoped>
// Stili specifici per il filtro se necessari
</style>

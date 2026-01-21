<script setup>
const props = defineProps({
  selectedNotificationType: {
    type: String,
    required: false,
    default: '',
  },
  notificationTypes: {
    type: Array,
    required: false,
    default: () => [],
  },
})

const emit = defineEmits([
  'update:notification-type',
])

// Mappa di default per le icone se non specificate nelle props
const defaultIcons = {
  'Calendar': 'tabler-calendar',
  'Ticket': 'tabler-mail-opened',
  'Paperwork': 'tabler-file-text',
  'Archived': 'tabler-archive',
}

// Usa notificationTypes se fornito, altrimenti usa i valori di default
const filterOptions = computed(() => {
  // Se notificationTypes è fornito e non è vuoto, usalo
  if (props.notificationTypes && Array.isArray(props.notificationTypes) && props.notificationTypes.length > 0) {
    const filtered = props.notificationTypes
      .filter(type => type && type.value !== '') // Escludi "Da leggere" che è il valore vuoto e valori null/undefined
      .map(type => ({
        value: type.value,
        icon: type.icon || defaultIcons[type.value] || 'tabler-bell',
        tooltip: type.title || type.tooltip || type.value,
      }))
    
    // Se dopo il filtro abbiamo ancora elementi, restituiscili
    return filtered.length > 0 ? filtered : getDefaultOptions()
  }
  
  // Fallback ai valori di default se notificationTypes non è fornito o è vuoto
  return getDefaultOptions()
})

// Funzione helper per ottenere le opzioni di default
const getDefaultOptions = () => [
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
  {
    value: 'Archived',
    icon: 'tabler-archive',
    tooltip: 'Archiviate',
  },
]

const hasArchivedOption = computed(() => {
  return filterOptions.value.some(option => option.value === 'Archived')
})

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
    <VBtn
      v-if="!hasArchivedOption"
      :variant="props.selectedNotificationType === 'Archived' ? 'elevated' : 'text'"
      :color="props.selectedNotificationType === 'Archived' ? 'primary' : 'default'"
      size="small"
      icon
      @click="handleFilterClick('Archived')"
    >
      <VIcon icon="tabler-archive" />
      <VTooltip activator="parent" location="bottom">
        {{ props.selectedNotificationType === 'Archived' ? 'Deseleziona Archiviate' : 'Archiviate' }}
      </VTooltip>
    </VBtn>
  </div>
</template>

<style lang="scss" scoped>
// Stili specifici per il filtro se necessari
</style>

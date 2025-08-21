<template>
  <div
    v-if="shouldShowAsChip"
    :class="chipClasses"
    class="status-chip"
  >
    {{ status.toUpperCase() }}
  </div>
  <div
    v-else
    class="text-high-emphasis text-body-1"
  >
    {{ status }}
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: 'default', // 'small', 'default', 'large'
    validator: value => ['small', 'default', 'large'].includes(value),
  },
  fallbackStyle: {
    type: String,
    default: 'chip', // 'chip', 'text'
    validator: value => ['chip', 'text'].includes(value),
  },
})

const chipClasses = computed(() => {
  const baseClasses = ['status-chip']
  
  // Aggiungi classe per la dimensione
  if (props.size !== 'default') {
    baseClasses.push(`status-chip--${props.size}`)
  }
  
  // Aggiungi classe per il colore basata sullo status
  const statusClass = getStatusClass(props.status)
  if (statusClass) {
    baseClasses.push(statusClass)
  }
  
  return baseClasses
})

const shouldShowAsChip = computed(() => {
  const statusClass = getStatusClass(props.status)
  return statusClass || props.fallbackStyle === 'chip'
})

const getStatusClass = (status) => {
  if (!status) return ''
  
  const normalizedStatus = status.toLowerCase().trim()
  
  // Mappatura degli stati ai colori
  const statusMap = {
    'storno': 'status-chip--red',
    'ko': 'status-chip--red',
    'ok pagabile': 'status-chip--green',
    'ok': 'status-chip--green',
    'accettato': 'status-chip--green',
    'confermato': 'status-chip--green',
    'inserito': 'status-chip--blue',
    'inviato otp': 'status-chip--light-blue',
    'sospeso': 'status-chip--yellow',
    'in attesa': 'status-chip--orange',
    'in elaborazione': 'status-chip--orange',
    'da lavorare': 'status-chip--brown',
  }
  
  return statusMap[normalizedStatus] || ''
}
</script>

<style lang="scss" scoped>
.status-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.2;
  text-align: center;
  white-space: nowrap;
  transition: all 0.2s ease-in-out;
  
  // Dimensioni
  &--small {
    padding: 4px 8px;
    font-size: 0.75rem;
    border-radius: 12px;
  }
  
  &--large {
    padding: 8px 16px;
    font-size: 1rem;
    border-radius: 20px;
  }
  
  // Colori per i diversi stati
  &--red {
    background-color: rgb(var(--v-theme-error));
    color: rgb(var(--v-theme-on-error));
  }
  
  &--green {
    background-color: rgb(var(--v-theme-success));
    color: rgb(var(--v-theme-on-success));
  }
  
  &--blue {
    background-color: rgb(var(--v-theme-primary));
    color: rgb(var(--v-theme-on-primary));
  }
  
  &--yellow {
    background-color: rgb(var(--v-theme-warning));
    color: rgb(var(--v-theme-on-warning));
  }
  
  &--orange {
    background-color: #ff9800;
    color: #ffffff;
  }
  
  &--light-blue {
    background-color: #42a5f5;
    color: #ffffff;
  }
  
  &--brown {
    background-color: #8d6e63;
    color: #ffffff;
  }
  
  // Fallback per stati non mappati
  &:not([class*="status-chip--"]) {
    background-color: rgba(var(--v-theme-on-surface), 0.12);
    color: rgba(var(--v-theme-on-surface), 0.87);
  }
}
</style>

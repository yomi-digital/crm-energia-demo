<script setup>
import { ticketCategories } from '@/components/dialogs/TicketCreateDialog.vue'

const props = defineProps({
  category: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: 'small',
    validator: (value) => ['x-small', 'small', 'default', 'large', 'x-large'].includes(value),
  },
  variant: {
    type: String,
    default: 'flat',
  },
  showIcon: {
    type: Boolean,
    default: true,
  },
})

// Funzione per ottenere i dettagli della categoria
const getCategoryDetails = (categoryValue) => {
  return ticketCategories.find(cat => cat.value === categoryValue) || { 
    title: categoryValue, 
    color: '#9e9e9e' 
  }
}

const categoryDetails = computed(() => getCategoryDetails(props.category))
</script>

<template>
  <VChip
    :color="categoryDetails.color"
    :size="size"
    :variant="variant"
    class="text-white"
  >
    <VIcon 
      v-if="showIcon" 
      icon="tabler-tag" 
      :size="size === 'x-small' ? 'x-small' : 'small'" 
      class="mr-1" 
    />
    {{ categoryDetails.title }}
  </VChip>
</template>

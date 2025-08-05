<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  success: {
    type: Boolean,
    required: true,
  },
  originalBrandId: {
    type: [Number, String],
    required: true,
  },
  destinationBrandId: {
    type: [Number, String],
    required: true,
  },
  aiPaperworkId: {
    type: [Number, String],
    required: true,
  },
  errorMessage: {
    type: String,
    default: 'Si è verificato un errore durante il trasferimento',
  },
  originalBrandName: {
    type: String,
    required: false,
    default: '',
  },
  destinationBrandName: {
    type: String,
    required: false,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue', 'close'])



const closeModal = () => {
  emit('update:modelValue', false)
  emit('close')
}

const successIcon = computed(() => props.success ? 'tabler-check' : 'tabler-x')
const successColor = computed(() => props.success ? 'success' : 'error')
const title = computed(() => props.success ? 'Trasferimento completato' : 'Trasferimento fallito')
</script>

<template>
  <VDialog
    :model-value="modelValue"
    @update:model-value="(value) => emit('update:modelValue', value)"
    max-width="500"
    persistent
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VIcon
          :icon="successIcon"
          :color="successColor"
          size="60"
        />
        <h6 class="text-lg font-weight-medium mt-4">
          {{ title }}
        </h6>
        
        <!-- Success Message -->
        <div v-if="success" class="mt-4">
          <p class="text-body-2 mb-3">
            La pratica AI <strong>#{{ aiPaperworkId }}</strong> è stata trasferita con successo.
          </p>
          
          <div class="transfer-details pa-3 bg-grey-lighten-4 rounded">
            <div class="d-flex align-center justify-center gap-2 mb-2">
              <VChip
                v-if="originalBrandName"
                size="small"
                color="warning"
                variant="tonal"
              >
                {{ originalBrandName }}
              </VChip>
              <span v-else class="text-grey">Brand #{{ originalBrandId }}</span>
              
              <VIcon icon="tabler-arrow-right" size="16" />
              
              <VChip
                v-if="destinationBrandName"
                size="small"
                color="success"
                variant="tonal"
              >
                {{ destinationBrandName }}
              </VChip>
              <span v-else class="text-grey">Brand #{{ destinationBrandId }}</span>
            </div>
            <p class="text-caption text-grey-darken-1 mb-0">
              La pratica è ora visibile al team responsabile del brand di destinazione.
            </p>
          </div>
        </div>
        
        <!-- Error Message -->
        <div v-else class="mt-4">
          <p class="text-body-2 text-error">
            {{ errorMessage }}
          </p>
          <p class="text-caption text-grey-darken-1">
            Pratica AI #{{ aiPaperworkId }}
          </p>
        </div>
      </VCardText>

      <VCardText class="d-flex align-center justify-center">
        <VBtn
          :color="successColor"
          @click="closeModal"
        >
          {{ success ? 'Perfetto' : 'Chiudi' }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.transfer-details {
  border-left: 4px solid rgb(var(--v-theme-success));
}
</style>

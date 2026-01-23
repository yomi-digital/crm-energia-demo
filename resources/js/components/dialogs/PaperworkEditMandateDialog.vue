<script setup>
import { watch } from 'vue'

const props = defineProps({
  paperworkData: {
    type: Object,
    required: true,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  mandates: {
    type: Array,
    required: true,
    default: () => [],
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'mandate-updated',
])

const selectedMandate = ref(props.paperworkData.mandate_id || null)
const isSaving = ref(false)

// Toast variables
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

// Watch per resettare i valori quando il popup si apre
watch(() => props.isDialogVisible, (isVisible) => {
  if (isVisible) {
    // Reset dei valori quando il popup si apre
    selectedMandate.value = props.paperworkData.mandate_id || null
  }
})

watch(() => props.paperworkData, (newData) => {
  if (newData) {
    selectedMandate.value = newData.mandate_id || null
  }
}, { immediate: true })

const closeDialog = () => {
  emit('update:isDialogVisible', false)
}

const saveMandate = async () => {
  if (!props.paperworkData?.id) {
    return
  }

  isSaving.value = true
  
  try {
    await $api(`/paperworks/${props.paperworkData.id}`, {
      method: 'PUT',
      body: {
        mandate_id: selectedMandate.value || null,
      },
    })
    
    snackbarMessage.value = 'Mandato aggiornato con successo'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true
    
    emit('mandate-updated')
    
    // Close dialog after a short delay to show the toast
    setTimeout(() => {
      closeDialog()
    }, 500)
  } catch (error) {
    console.error('Errore durante l\'aggiornamento del mandato:', error)
    
    // Extract error message from API response
    let errorMessage = 'Errore durante l\'aggiornamento del mandato'
    
    if (error?.data) {
      // Check for validation errors (422)
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      // Check for error message
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      // Check for error field
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
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
          Modifica Mandato
        </h4>

        <div class="mb-4">
          <p class="text-body-1 mb-4">
            Pratica #{{ paperworkData?.id }}
          </p>

          <VRow>
            <!-- 👉 Mandato -->
            <VCol cols="12">
              <AppAutocomplete
                v-model="selectedMandate"
                label="Mandato"
                :items="mandates"
                clearable
                placeholder="Seleziona un mandato"
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
            @click="saveMandate"
            :loading="isSaving"
          >
            Salva
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- 👉 Toast Notification -->
    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </VDialog>
</template>

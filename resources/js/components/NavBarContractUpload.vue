<script setup>
import DropZoneContracts from '@/components/DropZoneContracts.vue'
import { ref } from 'vue'

const dialog = ref(false)
const isUploading = ref(false)
const dropZoneRef = ref()
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

const uploadContract = async (files) => {
  if (!files.length) return
  
  isUploading.value = true
  alert.value.show = false
  
  const formData = new FormData()
  formData.append('contract', files[0])
  
  try {
    const response = await $api('/upload-contract', {
      method: 'POST',
      body: formData,
    })

    if (!response.id) {
      throw new Error('Upload failed')
    }

    // Show success message
    alert.value = {
      show: true,
      type: 'success',
      message: 'File caricato con successo!'
    }
    
    // Clear dropzone and close dialog after a short delay
    dropZoneRef.value.clearDropzone()
    setTimeout(() => {
      dialog.value = false
      alert.value.show = false
    }, 1500)
    
  } catch (error) {
    console.error('Upload failed:', error)
    alert.value = {
      show: true,
      type: 'error',
      message: 'Errore durante il caricamento del file'
    }
  } finally {
    isUploading.value = false
  }
}
</script>

<template>
  <div>
    <IconBtn @click="dialog = true" id="ai-contract-upload-btn">
      <span class="font-weight-bold">AI</span>
    </IconBtn>

    <VDialog
      v-model="dialog"
      width="600"
    >
      <VCard>
        <VCardTitle>Carica Contratto con AI</VCardTitle>
        <VCardText>
          <VAlert
            v-if="alert.show"
            :type="alert.type"
            :text="alert.message"
            class="mb-4"
          />

          <p class="text-body-1 mb-4">
            Carica il contratto completo di documenti di identità e bollette precedenti.<br>L'AI analizzera' il file e caricherà i dati nel sistema.
          </p>

          <DropZoneContracts
            @dropped="uploadContract"
            @error="(msg) => alert = { show: true, type: 'warning', message: msg }"
            :disabled="isUploading"
            accept=".pdf"
            single-file
            ref="dropZoneRef"
          />
          
          <VProgressLinear
            v-if="isUploading"
            indeterminate
            class="mt-4"
          />
        </VCardText>
        
        <VCardActions>
          <VSpacer />
          <VBtn
            color="primary"
            variant="text"
            @click="dialog = false"
          >
            Chiudi
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template> 

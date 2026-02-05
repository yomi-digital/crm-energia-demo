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
})

const emit = defineEmits([
  'update:isDialogVisible',
  'notes-updated',
])

const notes = ref(props.paperworkData?.notes || '')
const ownerNotes = ref(props.paperworkData?.owner_notes || '')
const isSaving = ref(false)

// Verifica se l'utente è admin per modificare le note Demo
const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser?.roles?.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

// Sincronizza i valori quando cambia paperworkData
watch(() => props.paperworkData, (newData) => {
  if (newData) {
    notes.value = newData.notes || ''
    ownerNotes.value = newData.owner_notes || ''
  }
}, { immediate: true, deep: true })

const closeDialog = () => {
  emit('update:isDialogVisible', false)
}

const saveNotes = async () => {
  if (!props.paperworkData?.id) {
    return
  }

  isSaving.value = true
  try {
    const body = {
      notes: notes.value || null,
    }
    
    // Solo admin può modificare owner_notes
    if (isAdmin) {
      body.owner_notes = ownerNotes.value || null
    }

    await $api(`/paperworks/${props.paperworkData.id}`, {
      method: 'PUT',
      body,
    })

    emit('notes-updated')
    closeDialog()
  } catch (error) {
    console.error('Errore nel salvataggio delle note:', error)
    alert('Errore nel salvataggio delle note')
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    @update:model-value="closeDialog"
    :width="$vuetify.display.smAndDown ? 'auto' : 700"
  >
    <DialogCloseBtn @click="closeDialog" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-4">
          Note Pratica #{{ paperworkData?.id }}
        </h4>

        <!-- 👉 Form -->
        <VForm @submit.prevent="saveNotes">
          <div class="mt-6">
            <!-- Note Generali -->
            <div class="mb-6">
              <h6 class="text-h6 mb-2">Note Generali</h6>
              <AppTextarea
                v-model="notes"
                label="Note Generali"
                placeholder="Inserisci note generali per questa pratica..."
                rows="6"
              />
            </div>

            <!-- Note Demo (solo per admin) -->
            <div v-if="isAdmin" class="mb-6">
              <h6 class="text-h6 mb-2">Note Demo</h6>
              <AppTextarea
                v-model="ownerNotes"
                label="Note Demo"
                placeholder="Inserisci note Demo per questa pratica..."
                rows="6"
              />
            </div>
          </div>

          <!-- 👉 Buttons -->
          <div class="d-flex justify-center gap-4 mt-6">
            <VBtn
              color="secondary"
              variant="tonal"
              @click="closeDialog"
              :disabled="isSaving"
            >
              Annulla
            </VBtn>
            <VBtn
              type="submit"
              color="primary"
              :loading="isSaving"
            >
              Salva
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template> 

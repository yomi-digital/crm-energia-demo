<script setup>
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
])

const closeDialog = () => {
  emit('update:isDialogVisible', false)
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    @update:model-value="closeDialog"
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
  >
    <DialogCloseBtn @click="closeDialog" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-4">
          Note Pratica #{{ paperworkData?.id }}
        </h4>

        <!-- 👉 Notes Content -->
        <div class="mt-6">
          <!-- Note Generali -->
          <div v-if="paperworkData?.notes" class="mb-4">
            <h6 class="text-h6 mb-2">Note Generali</h6>
            <VCard variant="outlined" class="pa-4">
              <div class="text-body-1">
                {{ paperworkData.notes }}
              </div>
            </VCard>
          </div>

          <!-- Note Alfacom -->
          <div v-if="paperworkData?.owner_notes" class="mb-4">
            <h6 class="text-h6 mb-2">Note Alfacom</h6>
            <VCard variant="outlined" class="pa-4">
              <div class="text-body-1">
                {{ paperworkData.owner_notes }}
              </div>
            </VCard>
          </div>

          <!-- Nessuna nota -->
          <div v-if="!paperworkData?.notes && !paperworkData?.owner_notes" class="text-center pa-4">
            <VIcon icon="tabler-note-off" size="48" color="disabled" class="mb-2" />
            <div class="text-body-1 text-medium-emphasis">
              Nessuna nota disponibile per questa pratica
            </div>
          </div>
        </div>

        <!-- 👉 Close Button -->
        <div class="d-flex justify-center mt-6">
          <VBtn
            color="primary"
            @click="closeDialog"
          >
            Chiudi
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template> 

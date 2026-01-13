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
  'agent-updated',
])

const selectedAgent = ref(props.paperworkData?.user_id || null)
const isSaving = ref(false)
const agents = ref([])
const isLoadingAgents = ref(false)

watch(() => props.paperworkData, (newData) => {
  if (newData) {
    selectedAgent.value = newData.user_id || null
  }
}, { immediate: true })

watch(() => props.isDialogVisible, async (isVisible) => {
  if (isVisible && agents.value.length === 0) {
    await fetchAgents()
  }
})

const fetchAgents = async () => {
  isLoadingAgents.value = true
  try {
    const response = await $api('/agents?itemsPerPage=99999999&select=1&structures=1&gestione=1&backoffice=1')
    agents.value = response.agents.map(agent => ({
      title: [agent.name, agent.last_name].join(' '),
      value: agent.id,
    }))
  } catch (error) {
    console.error('Errore durante il caricamento degli agenti:', error)
  } finally {
    isLoadingAgents.value = false
  }
}

const closeDialog = () => {
  emit('update:isDialogVisible', false)
}

const saveAgent = async () => {
  if (!props.paperworkData?.id) {
    return
  }

  isSaving.value = true
  
  try {
    await $api(`/paperworks/${props.paperworkData.id}`, {
      method: 'PUT',
      body: {
        user_id: selectedAgent.value,
      },
    })
    
    emit('agent-updated')
    closeDialog()
  } catch (error) {
    console.error('Errore durante l\'aggiornamento dell\'agente:', error)
    alert('Errore durante l\'aggiornamento dell\'agente')
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
  >
    <DialogCloseBtn @click="closeDialog" />

    <VCard class="pa-sm-8 pa-4">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-6">
          Modifica Agente
        </h4>

        <div class="mb-4">
          <p class="text-body-1 mb-4">
            Pratica #{{ paperworkData?.id }}
          </p>

          <!-- 👉 Agent Select -->
          <AppAutocomplete
            v-model="selectedAgent"
            label="Seleziona Agente"
            :items="agents"
            item-title="title"
            item-value="value"
            placeholder="Seleziona un agente"
            clearable
            :loading="isLoadingAgents"
          />
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
            @click="saveAgent"
            :loading="isSaving"
          >
            Salva
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

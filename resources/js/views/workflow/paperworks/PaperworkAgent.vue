<script setup>
const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)

const hasAgentError = computed(() => !formData.value.id)

watch(formData, () => {
  emit('update:formData', formData.value)
})

const agents = ref([])

const fetchAgents = async () => {
  agents.value = []
  const response = await $api('/agents?itemsPerPage=99999999&select=1&structures=1&gestione=1')
  for (let i = 0; i < response.agents.length; i++) {
    agents.value.push({
      title: [response.agents[i].name, response.agents[i].last_name].join(' '),
      value: response.agents[i].id,
    })
  }
}
await fetchAgents()
watch(() => formData.value.id, () => {
  const selectedAgent = agents.value.find(agent => agent.value === formData.value.id)
  formData.value.name = selectedAgent.title
})
</script>

<template>
  <VForm>
    <VRow>
      <VCol
        cols="12"
        sm="8"
      >
        <AppAutocomplete
          v-model="formData.id"
          label="Agente"
          :items="agents"
          placeholder="Seleziona un agente"
          :error="hasAgentError"
          :error-messages="hasAgentError ? 'Seleziona un agente' : ''"
          :rules="[v => !!v || 'Seleziona un agente']"
        />
      </VCol>
    </VRow>
  </VForm>
</template>

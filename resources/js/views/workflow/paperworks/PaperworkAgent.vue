<script setup>
const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)

watch(formData, () => {
  emit('update:formData', formData.value)
})

const agents = ref([])

const fetchAgents = async () => {
  agents.value = []
  const response = await $api('/agents?itemsPerPage=99999999&select=1')
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

const agencies = ref([])
const fetchAgencies = async () => {
  agencies.value = []
  const response = await $api('/agencies?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.agencies.length; i++) {
    agencies.value.push({
      title: [response.agencies[i].name, response.agencies[i].last_name].join(' '),
      value: response.agencies[i].id,
    })
  }
}
await fetchAgencies()
watch(() => formData.value.mandate_id, () => {
  const selectedAgency = agencies.value.find(agency => agency.value === formData.value.mandate_id)
  formData.value.mandate_name = selectedAgency.title
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
        />
      </VCol>

      <VCol
        cols="12"
        sm="8"
      >
        <AppAutocomplete
          v-model="formData.mandate_id"
          label="Agenzia di Fatturazione"
          :items="agencies"
          placeholder="Seleziona un'agenzia"
        />
      </VCol>
    </VRow>
  </VForm>
</template>

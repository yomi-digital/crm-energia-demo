<script setup>
const props = defineProps({
  userData: {
    type: Object,
    required: false,
    default: () => ({
      id: 0,
      role: {
        id: 0,
      },
      name: '',
      last_name: '',
      email: '',
      agent_code: '',
      manager_id: '',
      structure_id: '',
      commercial_profile: '',
      area: '',
      team_leader: 0,
      extractor: 0,
      enabled: 0,
    }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const userData = ref(structuredClone(toRaw(props.userData)))

watch(props, () => {
  userData.value = structuredClone(toRaw(props.userData))
})

const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', userData.value)
}

const onFormReset = () => {
  userData.value = structuredClone(toRaw(props.userData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

function ucfirst(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const roles = []
await $api('/roles').then(response => {
  for (let i = 0; i < response.roles.length; i++) {
    roles.push({
      title: ucfirst(response.roles[i].name),
      value: response.roles[i].id,
    })
  }
})

const users = ref([])
const fetchUsers = async () => {
  users.value = []
  const response = await $api('/users?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.users.length; i++) {
    users.value.push({
      title: [response.users[i].name, response.users[i].last_name].join(' '),
      value: response.users[i].id,
    })
  }
}
await fetchUsers()

const structures = ref([])
const fetchStructures = async () => {
  structures.value = []
  const response = await $api('/structures?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.structures.length; i++) {
    structures.value.push({
      title: [response.structures[i].name, response.structures[i].last_name].join(' '),
      value: response.structures[i].id,
    })
  }
}
await fetchStructures()
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Modifica Dettagli Account
        </h4>
        <!-- <p class="text-body-1 text-center mb-6">
          Updating user details will receive a privacy audit.
        </p> -->

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- 👉 Role -->
            <VCol
              cols="12"
              md="12"
            >
              <AppSelect
                v-model="userData.role.id"
                label="Ruolo"
                placeholder="Seleziona un ruolo"
                :rules="[requiredValidator]"
                :items="roles"
              />
            </VCol>

            <!-- 👉 Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.name"
                :rules="[requiredValidator]"
                label="Nome"
                placeholder="Mario"
              />
            </VCol>

            <!-- 👉 Last Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.last_name"
                :rules="[requiredValidator]"
                label="Cognome"
                placeholder="Rossi"
              />
            </VCol>

            <!-- 👉 Email -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="userData.email"
                :rules="[requiredValidator, emailValidator]"
                label="Email"
                placeholder="johndoe@email.com"
              />
            </VCol>

            <!-- 👉 Agent Code -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.agent_code"
                :rules="[requiredValidator]"
                label="Codice Agente"
                placeholder="12345"
              />
            </VCol>

            <!-- 👉 Area -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.area"
                label="Area"
                placeholder="Catania"
              />
            </VCol>

            <!-- 👉 Manager -->
            <VCol
              cols="12"
              md="12"
            >
              <AppAutocomplete
                v-model="userData.manager_id"
                label="Capo Area"
                :items="users"
                clearable
                placeholder="Seleziona un Capo Area"
              />
            </VCol>

            <!-- 👉 Structure -->
            <VCol
              cols="12"
              md="12"
            >
              <AppAutocomplete
                v-model="userData.structure_id"
                label="Struttura"
                :items="structures"
                clearable
                placeholder="Seleziona una Struttura"
              />
            </VCol>

            <!-- 👉 Team Leader -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="userData.team_leader"
                label="Team Leader"
                placeholder="Seleziona"
                :rules="[requiredValidator]"
                :items="[{ title: 'SI', value: 1 }, { title: 'NO', value: 0 }]"
              />
            </VCol>

            <!-- 👉 Extractor -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="userData.extractor"
                label="Estrattore"
                placeholder="Seleziona"
                :rules="[requiredValidator]"
                :items="[{ title: 'Si', value: 1 }, { title: 'NO', value: 0 }]"
              />
            </VCol>

            <!-- 👉 Enabled -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="userData.enabled"
                label="Abilitato"
                placeholder="Seleziona"
                :rules="[requiredValidator]"
                :items="[{ title: 'SI', value: 1 }, { title: 'NO', value: 0 }]"
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Salva
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="onFormReset"
              >
                Annulla
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

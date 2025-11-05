<script setup>
import { useCalendarStore } from '@/views/general/calendar/useCalendarStore';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { VForm } from 'vuetify/components/VForm';

// 👉 store
const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  event: {
    type: null,
    required: true,
  },
})

const loggedInUser = useCookie('userData').value
// Check if in the roles array there is a role with name 'agente'
const isAgent = loggedInUser.roles.some(role => role.name === 'agente' || role.name === 'struttura')
const isTelemarketing = loggedInUser.roles.some(role => role.name === 'telemarketing' || role.name === 'team leader')
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

const emit = defineEmits([
  'update:isDrawerOpen',
  'addEvent',
  'updateEvent',
  'removeEvent',
])

const store = useCalendarStore()
const refForm = ref()

// 👉 Event
const event = ref(JSON.parse(JSON.stringify(props.event)))

const resetEvent = () => {
  event.value = JSON.parse(JSON.stringify(props.event))
  nextTick(() => {
    refForm.value?.resetValidation()
  })
}

watch(() => props.isDrawerOpen, resetEvent)

const removeEvent = () => {
  emit('removeEvent', String(event.value.id))

  // Close drawer
  emit('update:isDrawerOpen', false)
}

const handleSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {

      // If id exist on id => Update event
      if ('id' in event.value)
        emit('updateEvent', event.value)

      // Else => add new event
      else
        emit('addEvent', event.value)

      // Close drawer
      emit('update:isDrawerOpen', false)
    }
  })
}

const typeOptions = [
  {
    name: 'TELEFONIA',
  },
  {
    name: 'ENERGIA',
  },
]

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


// 👉 Form
const onCancel = () => {
  // Close drawer
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    resetEvent()
    refForm.value?.resetValidation()
  })
}

const startDateTimePickerConfig = computed(() => {
  const config = {
    enableTime: !event.value.allDay,
    dateFormat: `Y-m-d${ event.value.allDay ? '' : ' H:i' }`,
  }

  if (event.value.end)
    config.maxDate = event.value.end

  return config
})

const endDateTimePickerConfig = computed(() => {
  const config = {
    enableTime: !event.value.allDay,
    dateFormat: `Y-m-d${ event.value.allDay ? '' : ' H:i' }`,
  }

  if (event.value.start)
    config.minDate = event.value.start

  return config
})

const dialogModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const onStartDateChange = value => {
  // console.log(value)
  // let end = new Date(value)
  // end.setMinutes(end.getMinutes() + 60)
  // event.value.end = end
}
</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="800"
    :border="0"
    class="scrollable-content"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- 👉 Header -->
    <AppDrawerHeaderSection
      :title="event.id ? 'Modifica Appuntamento' : 'Aggiungi Appuntamento'"
      @cancel="$emit('update:isDrawerOpen', false)"
    >
      <!-- <template #beforeClose>
        <IconBtn
          v-show="event.id"
          @click="removeEvent"
        >
          <VIcon
            size="18"
            icon="tabler-trash"
          />
        </IconBtn>
      </template> -->
    </AppDrawerHeaderSection>

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- SECTION Form -->
          <VForm
            ref="refForm"
            @submit.prevent="handleSubmit"
          >
            <VRow>
              <!-- 👉 Title -->
              <VCol cols="12">
                <AppTextField
                  v-model="event.title"
                  label="Titolo Evento"
                  placeholder="Appuntamento con Mario Rossi"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Calendar -->
              <VCol cols="12">
                <AppSelect
                  v-model="event.extendedProps.calendar"
                  label="Stato"
                  placeholder="Stato Trattativa"
                  :rules="[requiredValidator]"
                  :items="store.availableCalendars"
                  :item-title="item => item.label"
                  :item-value="item => item.label"
                >
                  <template #selection="{ item }">
                    <div
                      v-show="event.extendedProps.calendar"
                      class="align-center"
                      :class="event.extendedProps.calendar ? 'd-flex' : ''"
                    >
                      <VIcon
                        :color="item.raw.color"
                        icon="tabler-circle-filled"
                        size="8"
                        class="me-2"
                      />
                      <span>{{ item.raw.label }}</span>
                    </div>
                  </template>

                  <template #item="{ item, props: itemProps }">
                    <VListItem v-bind="itemProps">
                      <template #prepend>
                        <VIcon
                          size="8"
                          icon="tabler-circle-filled"
                          :color="item.raw.color"
                        />
                      </template>
                    </VListItem>
                  </template>
                </AppSelect>
              </VCol>

              <!-- 👉 Start date -->
              <VCol cols="6">
                <AppDateTimePicker
                  :key="JSON.stringify(startDateTimePickerConfig)"
                  @update:model-value="onStartDateChange"
                  v-model="event.start"
                  :rules="[requiredValidator]"
                  label="Data Inizio"
                  placeholder="Seleziona una data"
                  :config="startDateTimePickerConfig"
                />
              </VCol>

              <!-- 👉 End date -->
              <VCol cols="6">
                <AppDateTimePicker
                  :key="JSON.stringify(endDateTimePickerConfig)"
                  v-model="event.end"
                  :rules="[requiredValidator]"
                  label="Data Fine"
                  placeholder="Seleziona una data"
                  :config="endDateTimePickerConfig"
                />
              </VCol>

              <!-- 👉 All day -->
              <!-- <VCol cols="12">
                <VSwitch
                  v-model="event.allDay"
                  label="All day"
                />
              </VCol> -->

              <VCol cols="12">
                <h5 class="text-h5">Utente</h5>
              </VCol>

              <!-- 👉 Nome -->
              <VCol cols="12">
                <AppTextField
                  v-model="event.extendedProps.user_name"
                  label="Nome / Ragione Sociale"
                  placeholder="Mario Rossi"
                  :rules="[requiredValidator]"
                  type="url"
                />
              </VCol>

              <!-- 👉 Telefono -->
              <VCol cols="6">
                <AppTextField
                  v-model="event.extendedProps.user_phone"
                  label="Telefono"
                  placeholder="02 3456789"
                  :rules="[requiredValidator]"
                  type="url"
                />
              </VCol>

              <!-- 👉 Mobile -->
              <VCol cols="6">
                <AppTextField
                  v-model="event.extendedProps.user_mobile"
                  label="Mobile"
                  placeholder="333 1234567"
                  :rules="[requiredValidator]"
                  type="url"
                />
              </VCol>

              <!-- 👉 Address -->
              <VCol cols="12">
                <AppTextField
                  v-model="event.extendedProps.user_address"
                  label="Indirizzo"
                  placeholder="Via Roma 1"
                  :rules="[requiredValidator]"
                  type="url"
                />
              </VCol>

              <!-- 👉 City -->
              <VCol cols="12">
                <AppTextField
                  v-model="event.extendedProps.user_city"
                  label="Città"
                  placeholder="Catania"
                  :rules="[requiredValidator]"
                  type="url"
                />
              </VCol>

              <VCol cols="12">
                <h5 class="text-h5">Informazioni Aggiuntive</h5>
              </VCol>

              <!-- 👉 Referente -->
              <VCol cols="12">
                <AppTextField
                  v-model="event.extendedProps.referent"
                  label="Referente"
                  placeholder="Mario Rossi"
                  :rules="[requiredValidator]"
                  type="url"
                />
              </VCol>

              <!-- 👉 Tipo -->
              <VCol cols="12">
                <AppSelect
                  v-model="event.extendedProps.type"
                  label="Tipo Appuntamento"
                  placeholder="Seleziona un tipo di appuntamento"
                  :items="typeOptions"
                  :item-title="item => item.name"
                  :item-value="item => item.name"
                  eager
                />
              </VCol>

              <VCol cols="12">
                <AppAutocomplete
                  v-model="event.extendedProps.agent_id"
                  label="Agente"
                  placeholder="Seleziona un Agente"
                  :items="agents"
                />
              </VCol>


              <VCol cols="12">
                <h5 class="text-h5">Note</h5>
              </VCol>

              <!-- 👉 Note Call Center -->
              <VCol cols="12" v-if="isTelemarketing || isAdmin">
                <AppTextarea
                  v-model="event.extendedProps.notes_call_center"
                  label="Note Call Center"
                  placeholder=""
                />
              </VCol>

              <!-- 👉 Note Agente -->
              <VCol cols="12" v-if="isAgent || isAdmin">
                <AppTextarea
                  v-model="event.extendedProps.notes_agent"
                  label="Note Agente"
                  placeholder=""
                />
              </VCol>

              <!-- 👉 Note Alfacom -->
              <VCol cols="12" v-if="isAdmin">
                <AppTextarea
                  v-model="event.extendedProps.notes"
                  label="Note Alfacom"
                  placeholder=""
                />
              </VCol>

              <!-- 👉 Form buttons -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Salva
                </VBtn>
                <VBtn
                  variant="outlined"
                  color="secondary"
                  @click="onCancel"
                >
                  Annulla
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        <!-- !SECTION -->
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>

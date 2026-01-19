<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'calendar',
  },
})
import {
  blankEvent,
  useCalendar,
} from '@/views/general/calendar/useCalendar';
import { useCalendarStore } from '@/views/general/calendar/useCalendarStore';
import FullCalendar from '@fullcalendar/vue3';

// Components
import CalendarEventHandler from '@/views/general/calendar/CalendarEventHandler.vue';
import { ref, watch, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';

// 👉 Store
const store = useCalendarStore()


// 👉 Event
const event = ref(structuredClone(blankEvent))
const isEventHandlerSidebarActive = ref(false)
const isEventHandlerDialogActive = ref(false)
const isRemoveDialogVisible = ref(false)
const selectedType = ref('')
const selectedCity = ref('')
const selectedUser = ref('')
const selectedTelemarketing = ref('')
const selectedAgent = ref('')
const selectedCalendarUser = ref('')

watch(isEventHandlerSidebarActive, val => {
  if (!val)
    event.value = structuredClone(blankEvent)
})

const { isLeftSidebarOpen } = useResponsiveLeftSidebar()

// 👉 Router
const route = useRoute()
const router = useRouter()

// 👉 useCalendar
const { refCalendar, calendarOptions, addEvent, updateEvent, removeEvent, jumpToDate, calendarApi, extractEventDataFromEventApi } = useCalendar(event, isEventHandlerDialogActive, isEventHandlerSidebarActive, isLeftSidebarOpen)

// SECTION Sidebar

// 👉 Check all
const checkAll = computed({

  /*GET: Return boolean `true` => if length of options matches length of selected filters => Length matches when all events are selected
SET: If value is `true` => then add all available options in selected filters => Select All
Else if => all filters are selected (by checking length of both array) => Empty Selected array  => Deselect All
*/
  get: () => store.selectedCalendars.length === store.availableCalendars.length,
  set: val => {
    if (val)
      store.selectedCalendars = store.availableCalendars.map(i => i.label)
    else if (store.selectedCalendars.length === store.availableCalendars.length)
      store.selectedCalendars = []
  },
})

const jumpToDateFn = date => {
  jumpToDate(date)
}

const modifyEvent = () =>  {
  isEventHandlerDialogActive.value = false
  isEventHandlerSidebarActive.value = true
}
const confirmRemoveEvent = () => {
  isEventHandlerDialogActive.value = false
  isRemoveDialogVisible.value = true
}

const confirmationRemoveEvent = () => {
  removeEvent(event.value.id)
  isRemoveDialogVisible.value = false
}

const resetBlankEvent = () => {
  event.value = structuredClone(blankEvent)
}

const cities = []
const fetchCities = async (query) => {
  const response = await $api('/calendar-cities')
  for (const city of response) {
    cities.push({
      title: city.user_city,
      value: city.user_city,
    })
  }
}
await fetchCities()

const users = []
const telemarketing = []
const agents = []
const fetchUsers = async (query) => {
  const response = await $api('/users?itemsPerPage=99999999&select=1')
  for (const user of response.users) {
    users.push({
      title: [user.name, user.last_name].join(' ').trim() + ' (' + user.role?.name + ')',
      value: user.id,
    })
    if (user.role?.name === 'telemarketing') {
      telemarketing.push({
        title: [user.name, user.last_name].join(' ').trim(),
        value: user.id,
      })
    }
    if (user.role?.name === 'agente' || user.role?.name === 'struttura') {
      agents.push({
        title: [user.name, user.last_name].join(' ').trim(),
        value: user.id,
      })
    }
  }
}
if (useAbility().can('access', 'users')) {
  await fetchUsers()
}

const calendarUsers = []
const fetchCalendarUsers = async (query) => {
  const response = await $api('/calendar-users')
  for (const user of response) {
    calendarUsers.push({
      title: user.user_name,
      value: user.user_name,
    })
  }
}
await fetchCalendarUsers()

const loggedInUser = useCookie('userData').value
// Check if in the roles array there is a role with name 'agente'
const isAgent = loggedInUser.roles.some(role => role.name === 'agente')
const isStructure = loggedInUser.roles.some(role => role.name === 'struttura')
const isTelemarketing = loggedInUser.roles.some(role => role.name === 'telemarketing' || role.name === 'team leader')
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

// 👉 Gestione apertura appuntamento da notifica
const openAppointmentFromQuery = async () => {
  const appointmentId = route.query.appointment
  if (!appointmentId) return

  // Aspetta che il calendario sia montato e gli eventi siano caricati
  await nextTick()
  
  // Funzione per tentare di aprire l'appuntamento
  const tryOpenAppointment = (maxAttempts = 10, attempt = 0) => {
    if (attempt >= maxAttempts) {
      console.warn('Impossibile trovare l\'appuntamento con ID:', appointmentId)
      // Rimuovi comunque il parametro dalla query string
      const { appointment, ...restQuery } = route.query
      router.replace({ query: restQuery })
      return
    }

    if (calendarApi.value) {
      const calendarEvent = calendarApi.value.getEventById(String(appointmentId))
      if (calendarEvent) {
        // Estrai i dati dell'evento e apri il dialog
        event.value = extractEventDataFromEventApi(calendarEvent)
        isEventHandlerDialogActive.value = true
        
        // Rimuovi il parametro dalla query string per evitare di riaprire il dialog al cambio di route
        const { appointment, ...restQuery } = route.query
        router.replace({ query: restQuery })
        return
      }
    }
    
    // Se l'evento non è ancora caricato, riprova dopo un breve delay
    setTimeout(() => tryOpenAppointment(maxAttempts, attempt + 1), 300)
  }
  
  // Inizia il tentativo di apertura
  tryOpenAppointment()
}

// Esegui quando il componente è montato
onMounted(() => {
  openAppointmentFromQuery()
})
</script>

<template>
  <div>
    <VExpansionPanels class="mb-4">
      <VExpansionPanel>
        <VExpansionPanelTitle>
          Filtri
        </VExpansionPanelTitle>
        <VExpansionPanelText>
          <VRow>
            <VCol cols="4" v-if="$can('access', 'users') && (! isAgent && ! isStructure)">
              <AppAutocomplete
                v-model="store.selectedOperators"
                label="Filtra per Operatore"
                clearable
                multiple
                chips
                closable-chips
                :items="telemarketing"
                placeholder="Seleziona un Operatore"
              />
            </VCol>
            <VCol cols="4" v-if="$can('access', 'users') && (! isAgent || isStructure)">
              <AppAutocomplete
                v-model="store.selectedAgents"
                label="Filtra per Agente"
                clearable
                multiple
                chips
                closable-chips
                :items="agents"
                placeholder="Seleziona un Agente"
              />
            </VCol>
            <VCol cols="3">
              <AppSelect
                v-model="store.selectedType"
                label="Filtra per Tipo di Appuntamento"
                placeholder="Filtra per tipo di appuntamento"
                :items="[{ title: 'Energia', value: 'ENERGIa' }, { title: 'Telefonia', value: 'TELEFONIA' }]"
                clearable
              />
            </VCol>
            <VCol cols="5">
              <AppAutocomplete
                v-model="store.selecterUserName"
                label="Filtra per Ragione Sociale"
                clearable
                :items="calendarUsers"
                placeholder="Seleziona una Ragione Sociale"
              />
            </VCol>
            <VCol cols="4">
              <AppAutocomplete
                v-model="store.selectedCity"
                label="Filtra per Città"
                clearable
                :items="cities"
                placeholder="Seleziona una città"
              />
            </VCol>
          </VRow>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>

    <VCard>
      <!-- `z-index: 0` Allows overlapping vertical nav on calendar -->
      <VLayout style="z-index: 0;">
        <!-- 👉 Navigation drawer -->
        <VNavigationDrawer
          v-model="isLeftSidebarOpen"
          width="292"
          absolute
          touchless
          location="start"
          class="calendar-add-event-drawer"
          :temporary="$vuetify.display.mdAndDown"
        >
          <div style="margin: 1.5rem;">
            <VBtn
              block
              prepend-icon="tabler-plus"
              @click="isEventHandlerSidebarActive = true"
              v-if="$can('create', 'calendar')"
            >
              Aggiungi Appuntamento
            </VBtn>
          </div>

          <VDivider />

          <div class="d-flex align-center justify-center pa-2">
            <AppDateTimePicker
              :model-value="new Date().toJSON().slice(0, 10)"
              :config="{ inline: true }"
              class="calendar-date-picker"
              @update:model-value="jumpToDateFn"
            />
          </div>

          <VDivider />
          <div class="pa-6">
            <h6 class="text-lg font-weight-medium mb-4">
              Filtra per Stato
            </h6>

            <div class="d-flex flex-column calendars-checkbox">
              <VCheckbox
                v-model="checkAll"
                label="Tutti"
              />
              <VCheckbox
                v-for="calendar in store.availableCalendars"
                :key="calendar.label"
                v-model="store.selectedCalendars"
                :value="calendar.label"
                :color="calendar.color"
                :label="calendar.label"
              />
            </div>
          </div>
        </VNavigationDrawer>

        <VMain>
          <VCard flat>
            <FullCalendar
              ref="refCalendar"
              :options="calendarOptions"
            />
          </VCard>
        </VMain>
      </VLayout>
    </VCard>
    <CalendarEventHandler
      v-model:isDrawerOpen="isEventHandlerSidebarActive"
      :event="event"
      @add-event="addEvent"
      @update-event="updateEvent"
      @remove-event="removeEvent"
    />
  </div>
  <CalendarEventDialog
    v-if="event.id"
    v-model:isDialogVisible="isEventHandlerDialogActive"
    :event-data="event"
    @modify-event="modifyEvent"
    @remove-event="confirmRemoveEvent"
    @reset-blank-event="resetBlankEvent"
  />

  <VDialog
    v-model="isRemoveDialogVisible"
    width="500"
    v-if="$can('delete', 'calendar')"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

    <!-- Dialog Content -->
    <VCard title="Elimina Appuntamento">
      <VCardText>
        Sei sicuro di voler eliminare l'appuntamento <b>{{ event.title }}</b>?
      </VCardText>

      <VCardText class="d-flex justify-end">
        <VBtn color="error" @click="confirmationRemoveEvent">
          Elimina
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
@use "@core-scss/template/libs/full-calendar";

.calendars-checkbox {
  .v-label {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
    opacity: var(--v-high-emphasis-opacity);
  }
}

.calendar-add-event-drawer {
  &.v-navigation-drawer:not(.v-navigation-drawer--temporary) {
    border-end-start-radius: 0.375rem;
    border-start-start-radius: 0.375rem;
  }
}

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }

  & ~ .flatpickr-calendar .flatpickr-weekdays {
    margin-block: 0 4px;
  }
}

@media screen and (max-width: 1279px) {
  .calendar-add-event-drawer {
    border-width: 0;
  }
}
</style>

<style lang="scss" scoped>
.v-layout {
  overflow: visible !important;

  .v-card {
    overflow: visible;
  }
}
</style>

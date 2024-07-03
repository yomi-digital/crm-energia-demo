<script setup>
import {
blankEvent,
useCalendar,
} from '@/views/general/calendar/useCalendar';
import { useCalendarStore } from '@/views/general/calendar/useCalendarStore';
import FullCalendar from '@fullcalendar/vue3';

// Components
import CalendarEventHandler from '@/views/general/calendar/CalendarEventHandler.vue';
import { watch } from 'vue';

// 👉 Store
const store = useCalendarStore()


// 👉 Event
const event = ref(structuredClone(blankEvent))
const isEventHandlerSidebarActive = ref(false)
const isEventHandlerDialogActive = ref(false)
const isRemoveDialogVisible = ref(false)

watch(isEventHandlerSidebarActive, val => {
  if (!val)
    event.value = structuredClone(blankEvent)
})

const { isLeftSidebarOpen } = useResponsiveLeftSidebar()

// 👉 useCalendar
const { refCalendar, calendarOptions, addEvent, updateEvent, removeEvent, jumpToDate } = useCalendar(event, isEventHandlerDialogActive, isEventHandlerSidebarActive, isLeftSidebarOpen)

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
</script>

<template>
  <div>
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

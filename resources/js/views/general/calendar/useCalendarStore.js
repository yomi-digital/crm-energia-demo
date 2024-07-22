export const useCalendarStore = defineStore('calendar', {
  // arrow function recommended for full type inference
  state: () => ({
    availableCalendars: [
      {
        color: 'error',
        label: 'NON VALIDO',
      },
      {
        color: 'success',
        label: 'VALIDO',
      },
      {
        color: 'success',
        label: 'CHIUSO',
      },
      {
        color: 'primary',
        label: 'DA FISSARE',
      },
      {
        color: 'info',
        label: 'SOSPESO',
      },
      {
        color: 'warning',
        label: 'TRATTATIVA',
      },
      {
        color: '',
        label: 'N/A',
      },
    ],
    selectedCalendars: ['NON VALIDO', 'VALIDO', 'CHIUSO', 'DA FISSARE', 'SOSPESO', 'TRATTATIVA', 'N/A'],
    selectedOperators: [],
    selectedAgents: [],
    selectedCity: '',
    selectedType: '',
    selecterUserName: '',
  }),
  actions: {
    async fetchEvents(start, end) {
      const { data, error } = await useApi(createUrl('/calendar', {
        query: {
          calendars: this.selectedCalendars.join(','),
          operators: this.selectedOperators.join(','),
          agents: this.selectedAgents.join(','),
          city: this.selectedCity,
          type: this.selectedType,
          user_name: this.selecterUserName,
          start: start,
          end: end,
        },
      }))

      if (error.value)
        return error.value

      return data.value
    },
    async addEvent(event) {
      await $api('/calendar', {
        method: 'POST',
        body: event,
      })
    },
    async updateEvent(event) {
      return await $api(`/calendar/${event.id}`, {
        method: 'PUT',
        body: event,
      })
    },
    async removeEvent(eventId) {
      console.log(eventId)
      return await $api(`/calendar/${eventId}`, {
        method: 'DELETE',
      })
    },
  },
})

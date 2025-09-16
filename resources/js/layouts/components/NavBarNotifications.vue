<script setup>

const router = useRouter()
const notifications = ref([])
const selectedNotificationType = ref('')
let pollingInterval = null

const notificationTypes = [
  { title: 'Tutte', value: '' },
  { title: 'Calendario', value: 'Calendar' },
  { title: 'Ticket', value: 'Ticket' },
  { title: 'Pratiche', value: 'Paperwork' },
]

const fetchNotifications = async () => {
  const params = {}
  if (selectedNotificationType.value) {
    params.notification_type = selectedNotificationType.value
  }
  
  const response = await $api('/auth/notifications', {
    params
  })
  notifications.value = response.data
}

// Caricamento iniziale
fetchNotifications();

// Polling ogni 5 secondi per aggiornare le notifiche
pollingInterval = setInterval(() => {
  fetchNotifications()
}, 5000)

// Watch per ricaricare le notifiche quando cambia il filtro
watch(selectedNotificationType, () => {
  fetchNotifications()
})

// Cleanup del timer quando il componente viene smontato
onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})

const removeNotification = notificationId => {
  notifications.value.forEach((item, index) => {
    if (notificationId === item.id)
      notifications.value.splice(index, 1)
  })
}

const markRead = async notificationId => {
  // The below should be a post request
  await $api(`/auth/notifications/${notificationId}/read`, {
    method: 'POST',
  })
  await fetchNotifications()
}

const markUnRead = async notificationId => {
  await $api(`/auth/notifications/${notificationId}/unread`, {
    method: 'POST',
  })
  await fetchNotifications()
}

const handleNotificationClick = notification => {
  if (!notification.isSeen) {
    markRead([notification.id])
  }

  if (notification.link) {
    router.push(notification.link)
  }
}
</script>

<template>
  <Notifications
    :notifications="notifications"
    :notification-types="notificationTypes"
    :selected-notification-type="selectedNotificationType"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
    @update:notification-type="selectedNotificationType = $event"
  />
</template>

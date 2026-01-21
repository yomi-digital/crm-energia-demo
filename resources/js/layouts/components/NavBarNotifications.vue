<script setup>
import { onMounted, onUnmounted } from 'vue'

const router = useRouter()
const notifications = ref([])
const selectedNotificationType = ref('')
let pollingInterval = null
const isPollingActive = ref(false)

const notificationTypes = [
  { title: 'Da leggere', value: '' },
  { title: 'Calendario', value: 'Calendar' },
  { title: 'Ticket', value: 'Ticket' },
  { title: 'Pratiche', value: 'Paperwork' },
  { title: 'Archiviate', value: 'Archived' },
]

const fetchNotifications = async () => {
  const params = {}
  
  if (selectedNotificationType.value === 'Archived') {
    params.is_archived = 'true'
  } else if (selectedNotificationType.value) {
    params.notification_type = selectedNotificationType.value
  }
  
  const response = await $api('/auth/notifications', {
    params
  })
  notifications.value = response.data
}

const startPolling = () => {
  // Previeni polling multipli
  if (isPollingActive.value || pollingInterval) {
    return
  }
  
  isPollingActive.value = true
  pollingInterval = setInterval(() => {
    fetchNotifications()
  }, 20000)
}

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
    isPollingActive.value = false
  }
}

// Caricamento iniziale e avvio polling quando il componente viene montato
onMounted(async () => {
  await fetchNotifications()
  startPolling()
})

// Watch per ricaricare le notifiche quando cambia il filtro
watch(selectedNotificationType, () => {
  fetchNotifications()
})

// Cleanup del timer quando il componente viene smontato
onUnmounted(() => {
  stopPolling()
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
    markRead(notification.id)
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

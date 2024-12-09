<script setup>

const router = useRouter()
const notifications = ref([])

const fetchNotifications = async () => {
  const response = await $api('/auth/notifications')
  notifications.value = response.data
}
fetchNotifications();

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
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
  />
</template>

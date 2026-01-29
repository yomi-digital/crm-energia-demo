<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import NotificationFilter from './NotificationFilter.vue'

const props = defineProps({
  notifications: {
    type: Array,
    required: true,
  },
  notificationTypes: {
    type: Array,
    required: false,
    default: () => [],
  },
  selectedNotificationType: {
    type: String,
    required: false,
    default: '',
  },
  badgeProps: {
    type: Object,
    required: false,
    default: undefined,
  },
  location: {
    type: null,
    required: false,
    default: 'bottom end',
  },
  markAllRead: {
    type: Function,
    required: false,
    default: null,
  },
})

const emit = defineEmits([
  'read',
  'unread',
  'remove',
  'click:notification',
  'update:notification-type',
  'close',
  'readall',
])

const isAllMarkRead = computed(() => props.notifications.some(item => item.isSeen === false))
const hasArchivedFilter = computed(() => props.notificationTypes?.some(type => type?.value === 'Archived'))
const isArchivedSelected = computed(() => props.selectedNotificationType === 'Archived')

const toggleArchived = () => {
  emit('update:notification-type', isArchivedSelected.value ? '' : 'Archived')
}

const markAllReadOrUnread = () => {
  const allNotificationsIds = props.notifications.map(item => item.id)
  if (!isAllMarkRead.value)
    emit('unread', allNotificationsIds)
  else
    emit('read', allNotificationsIds)
}

const markAllAsRead = () => {
  console.log('markAllAsRead chiamato nel componente Notifications (via prop)')
  if (props.markAllRead) {
    props.markAllRead()
  } else {
    // Fallback per compatibilità se la prop non è passata
    emit('readall')
  }
}

const totalUnseenNotifications = computed(() => {
  return props.notifications.filter(item => item.isSeen === false).length
})

const toggleReadUnread = (isSeen, Id) => {
  if (isSeen)
    emit('unread', [Id])
  else
    emit('read', [Id])
}

const isMenuOpen = ref(false)

const handleMenuUpdate = (value) => {
  const wasOpen = isMenuOpen.value
  isMenuOpen.value = value
  // Quando il menu viene chiuso (da true a false), resetta i filtri
  if (wasOpen && !value) {
    emit('close')
  }
}
</script>

<template>
  <IconBtn id="notification-btn">
    <VBadge
      v-bind="props.badgeProps"
      :model-value="totalUnseenNotifications > 0"
      color="error"
      :content="totalUnseenNotifications > 0 ? totalUnseenNotifications : ''"
      :dot="false"
      offset-x="2"
      offset-y="3"
    >
      <VIcon
        size="24"
        icon="tabler-bell"
      />
    </VBadge>

    <VMenu
      :model-value="isMenuOpen"
      @update:model-value="handleMenuUpdate"
      activator="parent"
      width="380px"
      :location="props.location"
      offset="12px"
      :close-on-content-click="false"
    >
      <VCard class="d-flex flex-column">
        <!-- 👉 Header -->
        <VCardItem class="notification-section">
          <VCardTitle class="text-h6">
            Notifiche
          </VCardTitle>

          <template #append>
            <VChip
              v-show="props.notifications.some(n => !n.isSeen)"
              size="small"
              color="primary"
              class="me-2"
            >
              {{ totalUnseenNotifications }} Nuove
            </VChip>
            <VBtn
              v-if="hasArchivedFilter"
              size="small"
              variant="tonal"
              color="secondary"
              class="me-2"
              @click="toggleArchived"
            >
              {{ isArchivedSelected ? 'Da leggere' : 'Archiviate' }}
            </VBtn>
            <VBtn
              v-show="props.notifications.some(n => !n.isSeen) && !isArchivedSelected"
              size="small"
              variant="text"
              color="primary"
              @click.stop="markAllAsRead"
            >
              Segna tutte come lette
            </VBtn>
          </template>
        </VCardItem>

        <VDivider />

        <!-- 👉 Filtro tipo notifica -->
        <VCardItem v-if="props.notificationTypes.length" class="notification-filter-section">
          <NotificationFilter
            :selected-notification-type="props.selectedNotificationType"
            :notification-types="props.notificationTypes"
            @update:notification-type="emit('update:notification-type', $event)"
          />
        </VCardItem>

        <VDivider v-if="props.notificationTypes.length" />

        <!-- 👉 Notifications list -->
        <PerfectScrollbar
          :options="{ wheelPropagation: false }"
          style="max-block-size: 23.75rem;"
        >
          <VList class="notification-list rounded-0 py-0">
            <template
              v-for="(notification, index) in props.notifications"
              :key="notification.title"
            >
              <VDivider v-if="index > 0" />
              <VListItem
                link
                lines="one"
                min-height="66px"
                class="list-item-hover-class"
                @click="$emit('click:notification', notification)"
              >
                <!-- Slot: Prepend -->
                <!-- Handles Avatar: Image, Icon, Text -->
                <div class="d-flex align-start gap-3">
                  <VAvatar
                    size="40"
                    :color="notification.color && notification.icon ? notification.color : undefined"
                    :image="notification.img || undefined"
                    :icon="notification.icon || undefined"
                    :variant="notification.img ? undefined : 'tonal' "
                  >
                    <span v-if="notification.text">{{ avatarText(notification.text) }}</span>
                  </VAvatar>

                  <div>
                    <p class="text-sm font-weight-medium mb-1">
                      {{ notification.title }}
                    </p>
                    <p
                      class="text-body-2 mb-2"
                      style=" letter-spacing: 0.4px !important; line-height: 18px;"
                    >
                      {{ notification.subtitle }}
                    </p>
                    <p
                      class="text-sm text-disabled mb-0"
                      style=" letter-spacing: 0.4px !important; line-height: 18px;"
                    >
                      {{ notification.time }}
                    </p>
                  </div>
                  <VSpacer />

                  <div class="d-flex flex-column align-end">
                    <VIcon
                      size="10"
                      icon="tabler-circle-filled"
                      :color="!notification.isSeen ? 'primary' : '#a8aaae'"
                      :class="`${notification.isSeen ? 'visible-in-hover' : ''}`"
                      class="mb-2"
                      @click.stop="toggleReadUnread(notification.isSeen, notification.id)"
                    />

                    <!-- <VIcon
                      size="20"
                      icon="tabler-x"
                      class="visible-in-hover"
                      @click="$emit('remove', notification.id)"
                    /> -->
                  </div>
                </div>
              </VListItem>
            </template>

            <VListItem
              v-show="!props.notifications.length"
              class="text-center text-medium-emphasis"
              style="block-size: 56px;"
            >
              <VListItemTitle>Nessuna notifica trovata!</VListItemTitle>
            </VListItem>
          </VList>
        </PerfectScrollbar>

        <VDivider />

        <!-- 👉 Footer -->
        <!-- <VCardText
          v-show="props.notifications.length"
          class="pa-4"
        >
          <VBtn
            block
            size="small"
          >
            Visualizza tutte le notifiche
          </VBtn>
        </VCardText> -->
      </VCard>
    </VMenu>
  </IconBtn>
</template>

<style lang="scss">
.notification-section {
  padding-block: 0.75rem;
  padding-inline: 1rem;
}

.notification-filter-section {
  padding-block: 0.5rem;
  padding-inline: 1rem;
}

.list-item-hover-class {
  .visible-in-hover {
    display: none;
  }

  &:hover {
    .visible-in-hover {
      display: block;
    }
  }
}

.notification-list.v-list {
  .v-list-item {
    border-radius: 0 !important;
    margin: 0 !important;
    padding-block: 0.75rem !important;
  }
}

// Badge Style Override for Notification Badge
#notification-btn {
  .v-badge__badge {
    /* stylelint-disable-next-line liberty/use-logical-spec */
    min-width: 18px;
    padding: 2px 6px;
    block-size: auto;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.2;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
</style>

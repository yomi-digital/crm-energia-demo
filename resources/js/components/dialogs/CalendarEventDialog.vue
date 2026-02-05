<script setup>
const props = defineProps({
  eventData: {
    type: Object,
    required: false,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'modifyEvent',
  'removeEvent',
  'resetBlankEvent',
  'update:isDialogVisible',
])

const eventData = ref(structuredClone(toRaw(props.eventData)))

watch(props, () => {
  eventData.value = structuredClone(toRaw(props.eventData))
  console.log(eventData.value)
})

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
  emit('resetBlankEvent')
}
</script>

<style lang="scss" scoped>
.v-list-item-title .text-body-1 {
  white-space: normal;
}
</style>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-4 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          {{ eventData.title }}
        </h4>

        <VList class="card-list mt-2">
          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Stato
                <div class="text-body-1">
                  {{ eventData.extendedProps.calendar || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Nome / Ragione Sociale
                <div class="text-body-1">
                  {{ eventData.extendedProps.user_name || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Agente
                <div class="text-body-1">
                  <RouterLink
                    v-if="$can('view', 'users') && eventData.extendedProps.agent"
                    :to="{ name: 'admin-users-id', params: { id: eventData.extendedProps.agent.id } }"
                    class="font-weight-medium text-link"
                    :title="[eventData.extendedProps.agent.name, eventData.extendedProps.agent.last_name].join(' ').trim()"
                  >
                    {{ [eventData.extendedProps.agent.name, eventData.extendedProps.agent.last_name].join(' ').trim() }}
                  </RouterLink>
                  <template v-else>
                    {{ eventData.extendedProps.agent ? [eventData.extendedProps.agent.name, eventData.extendedProps.agent.last_name].join(' ') : 'N/A' }}
                  </template>
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Inserito da
                <div class="text-body-1">
                  <RouterLink
                    v-if="$can('view', 'users') && eventData.extendedProps.operator"
                    :to="{ name: 'admin-users-id', params: { id: eventData.extendedProps.operator.id } }"
                    class="font-weight-medium text-link"
                    :title="[eventData.extendedProps.operator.name, eventData.extendedProps.operator.last_name].join(' ').trim()"
                  >
                    {{ [eventData.extendedProps.operator.name, eventData.extendedProps.operator.last_name].join(' ').trim() }}
                  </RouterLink>
                  <template v-else>
                    {{ eventData.extendedProps.operator ? [eventData.extendedProps.operator.name, eventData.extendedProps.operator.last_name].join(' ') : 'N/A' }}
                  </template>
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Data
                <div class="text-body-1">
                  {{ eventData.start.toLocaleDateString() }}
                  {{ eventData.start.toLocaleTimeString().substring(0, 5) }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Città
                <div class="text-body-1">
                  {{ eventData.extendedProps.user_city }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Indirizzo
                <div class="text-body-1">
                  {{ eventData.extendedProps.user_address }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Telefono
                <div class="text-body-1">
                  {{ eventData.extendedProps.user_phone || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Cellulare
                <div class="text-body-1">
                  {{ eventData.extendedProps.user_mobile || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Referente
                <div class="text-body-1">
                  {{ eventData.extendedProps.referent || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Note Call Center
                <div class="text-body-1" style="white-space:normal">
                  {{ eventData.extendedProps.notes_call_center || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Note Agente
                <div class="text-body-1">
                  {{ eventData.extendedProps.notes_agent || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Note Demo
                <div class="text-body-1">
                  {{ eventData.extendedProps.notes || 'N/A' }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

        </VList>


        <VRow>
          <!-- 👉 Submit and Cancel -->
          <VCol
            cols="12"
            class="d-flex flex-wrap justify-center gap-4 mt-6"
          >
            <VBtn @click.prevent="$emit('modifyEvent')">
              Modifica
            </VBtn>
            <VBtn @click.prevent="$emit('removeEvent', String(eventData.id))" color="error">
              Elimina
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>
</template>

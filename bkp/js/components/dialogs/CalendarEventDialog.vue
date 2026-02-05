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
                  {{ eventData.extendedProps.agent ? [eventData.extendedProps.agent.name, eventData.extendedProps.agent.last_name].join(' ') : 'N/A' }}
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
                  {{ eventData.extendedProps.user_phone }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Cellulare
                <div class="text-body-1">
                  {{ eventData.extendedProps.user_mobile }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Referente
                <div class="text-body-1">
                  {{ eventData.extendedProps.referent }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Note Call Center
                <div class="text-body-1" style="white-space:normal">
                  {{ eventData.extendedProps.notes_call_center }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Note Agente
                <div class="text-body-1">
                  {{ eventData.extendedProps.notes_agent }}
                </div>
              </h6>
            </VListItemTitle>
          </VListItem>

          <VListItem style="padding-bottom: 5px !important">
            <VListItemTitle>
              <h6 class="text-h6 mt-2">
                Note Demo
                <div class="text-body-1">
                  {{ eventData.extendedProps.notes }}
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

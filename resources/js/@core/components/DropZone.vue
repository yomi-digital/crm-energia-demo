<script setup>
import {
    useDropZone,
    useFileDialog,
    useObjectUrl,
} from '@vueuse/core';

const emit = defineEmits([
  'dropped',
])

const props = defineProps({
  scope: {
    type: String,
    required: true,
  },
})

const dropZoneRef = ref()
const fileData = ref([])
const { open, onChange } = useFileDialog({ accept: '*/*' })

function normalizeFiles(files) {
  if (!files)
    return []
  return Array.isArray(files) ? files : Array.from(files)
}

function onDrop(DroppedFiles) {
  const files = normalizeFiles(DroppedFiles)
  if (!files.length)
    return

  // Aggiungi i file alla lista immediatamente per mostrare il feedback visivo
  files.forEach(file => {
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
    })
  })

  uploadFiles(files).then(() => {
    emit('dropped', fileData.value)
  })
}

async function uploadFiles(files) {
  const uploadTasks = files.map(file => (async () => {
    const formData = new FormData()
    formData.append('scope', props.scope)
    formData.append('file', file)

    try {
      const response = await $api('/uploads', {
        method: 'POST',
        body: formData,
      })

      // Trova l'elemento corrispondente in fileData e aggiorna con il path
      const fileItem = fileData.value.find(item => item.file === file)
      if (fileItem) {
        fileItem.path = response.path
      }
    } catch (error) {
      // Trova l'elemento corrispondente e segna l'errore
      const fileItem = fileData.value.find(item => item.file === file)
      if (fileItem) {
        fileItem.error = error
      }
    }
  })())

  await Promise.allSettled(uploadTasks)
}

onChange(async selectedFiles => {
  const files = normalizeFiles(selectedFiles)
  if (!files.length)
    return

  // Aggiungi i file alla lista immediatamente
  files.forEach(file => {
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
    })
  })

  await uploadFiles(files)
  emit('dropped', fileData.value)
})
useDropZone(dropZoneRef, onDrop)
</script>

<template>
  <div class="flex">
    <div class="w-full h-auto relative">
      <div
        ref="dropZoneRef"
        class="cursor-pointer"
        @click="() => open()"
      >
        <div
          v-if="fileData.length === 0"
          class="d-flex flex-column justify-center align-center gap-y-2 pa-12 drop-zone rounded"
        >
          <IconBtn
            variant="tonal"
            class="rounded-sm"
          >
            <VIcon icon="tabler-upload" />
          </IconBtn>
          <h4 class="text-h4">
            Trascina il tuo file qui.
          </h4>
          <!-- <span class="text-disabled">o</span>

          <VBtn
            variant="tonal"
            size="small"
          >
            Cerca file
          </VBtn> -->
        </div>

        <div
          v-else
          class="d-flex justify-center align-center gap-3 pa-8 drop-zone flex-wrap"
        >
          <VRow class="match-height w-100">
            <template
              v-for="(item, index) in fileData"
              :key="index"
            >
              <VCol
                cols="12"
                sm="12"
              >
                <VCard
                  :ripple="false"
                  border
                >
                  <VCardText
                    class="d-flex flex-column"
                    @click.stop
                  >
                    <VImg
                      :src="item.url"
                      width="200px"
                      height="150px"
                      class="w-100 mx-auto"
                    />
                    <div class="mt-2">
                      <span class="clamp-text text-wrap">
                        {{ item.file.name }}
                      </span>
                      <span>
                        {{ item.file.size / 1000 }} KB
                      </span>
                    </div>
                  </VCardText>
                  <VCardActions>
                    <VBtn
                      variant="text"
                      block
                      @click.stop="fileData.splice(index, 1)"
                    >
                      Remove File
                    </VBtn>
                  </VCardActions>
                </VCard>
              </VCol>
            </template>
          </VRow>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.drop-zone {
  border: 1px dashed rgba(var(--v-theme-on-surface), var(--v-border-opacity));
}
</style>

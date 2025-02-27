<script setup>
import {
  useDropZone,
  useFileDialog,
  useObjectUrl,
} from '@vueuse/core';

const props = defineProps({
  singleFile: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'dropped',
  'clear-dropzone'
])

const dropZoneRef = ref()
const fileData = ref([])
const { open, onChange } = useFileDialog({ accept: '*/*' })

function onDrop(DroppedFiles) {
  if (props.disabled) return
  
  // If singleFile is true, only process the first file
  const filesToProcess = props.singleFile ? [DroppedFiles[0]] : DroppedFiles
  
  filesToProcess?.forEach(async file => {
    const fileIndex = fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
      status: 'uploading',
    }) - 1

    emit('dropped', [file]); // Emit just the file for single file upload
  })
}

// Add method to clear the dropzone
const clearDropzone = () => {
  fileData.value = []
}

// Expose clearDropzone method to parent
defineExpose({
  clearDropzone
})

// Modify onChange to respect singleFile prop
onChange(async selectedFiles => {
  if (!selectedFiles || props.disabled) return
  
  const filesToProcess = props.singleFile ? [selectedFiles[0]] : selectedFiles
  emit('dropped', filesToProcess)
})

useDropZone(dropZoneRef, onDrop)
</script>

<template>
  <div 
    class="flex"
    :class="{ 'opacity-75': disabled }"
  >
    <div class="w-full h-auto relative">
      <div
        ref="dropZoneRef"
        class="cursor-pointer"
        @click="() => open()"
      >
        <div
          v-if="fileData.length === 0"
          class="d-flex flex-column justify-center align-center gap-y-2 pa-4 drop-zone rounded"
        >
          <IconBtn
            variant="tonal"
            size="small"
            class="rounded-sm"
          >
            <VIcon icon="tabler-upload" />
          </IconBtn>
          <span class="text-body-1">
            Trascina il tuo file qui.
          </span>
        </div>

        <div
          v-else
          class="d-flex justify-center align-center gap-3 pa-4 drop-zone flex-wrap"
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
                    class="d-flex flex-column pa-2"
                    @click.stop
                  >
                    <VImg
                      :src="item.url"
                      width="100px"
                      height="75px"
                      class="mx-auto"
                    />
                    <div class="mt-1">
                      <span class="text-caption text-wrap d-block">
                        {{ item.file.name }}
                      </span>
                      <span class="text-caption">
                        {{ item.file.size / 1000 }} KB
                      </span>
                    </div>
                  </VCardText>
                  <VCardActions class="pa-2">
                    <VBtn
                      variant="text"
                      density="compact"
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
  min-height: 100px; // Set a minimum height
  max-height: 150px; // Set a maximum height
}
</style>

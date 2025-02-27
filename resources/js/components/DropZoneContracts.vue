<script setup>
import { ref } from 'vue'

const props = defineProps({
  accept: {
    type: String,
    default: '.pdf',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  singleFile: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['dropped', 'error'])

const dropZone = ref(null)
const isDragging = ref(false)

const onDrop = (e) => {
  e.preventDefault()
  isDragging.value = false

  if (props.disabled) return

  const files = [...e.dataTransfer.files]
  if (props.singleFile && files.length > 1) {
    emit('error', 'È possibile caricare solo un file alla volta')
    return
  }

  // Filter by accepted file types
  const acceptedTypes = props.accept.split(',').map(type => type.trim())
  const validFiles = files.filter(file => {
    return acceptedTypes.some(type => {
      if (type.startsWith('.')) {
        return file.name.toLowerCase().endsWith(type.toLowerCase())
      }
      return file.type.match(new RegExp(type.replace('*', '.*')))
    })
  })

  if (validFiles.length !== files.length) {
    emit('error', 'Alcuni file non sono nel formato corretto')
  }

  if (validFiles.length > 0) {
    emit('dropped', validFiles)
  }
}

const onDragOver = (e) => {
  e.preventDefault()
  if (!props.disabled)
    isDragging.value = true
}

const onDragLeave = () => {
  isDragging.value = false
}

const onClick = () => {
  if (props.disabled) return
  
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = props.accept
  if (!props.singleFile) input.multiple = true
  
  input.onchange = (e) => {
    const files = [...e.target.files]
    emit('dropped', files)
  }
  
  input.click()
}

const clearDropzone = () => {
  // This method can be called from parent to clear the dropzone state if needed
  isDragging.value = false
}

// Expose methods to parent
defineExpose({
  clearDropzone,
})
</script>

<template>
  <div
    ref="dropZone"
    :class="[
      'dropzone d-flex align-center justify-center rounded cursor-pointer',
      { 'dropzone-dragging': isDragging },
      { 'dropzone-disabled': disabled }
    ]"
    @drop="onDrop"
    @dragover="onDragOver"
    @dragleave="onDragLeave"
    @click="onClick"
  >
    <div class="text-center">
      <VIcon
        size="48"
        icon="tabler-upload"
        class="mb-2"
      />
      <div class="text-body-1">
        Trascina qui i file o clicca per selezionarli
      </div>
      <div class="text-caption text-medium-emphasis">
        File accettati: {{ accept }}
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.dropzone {
  border: 2px dashed rgba(var(--v-border-color), var(--v-border-opacity));
  background: rgb(var(--v-theme-surface));
  min-height: 200px;
  transition: all 0.3s ease;

  &:hover:not(.dropzone-disabled) {
    border-color: rgb(var(--v-theme-primary));
  }

  &.dropzone-dragging {
    border-color: rgb(var(--v-theme-primary));
    background: rgba(var(--v-theme-primary), 0.05);
  }

  &.dropzone-disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}
</style> 

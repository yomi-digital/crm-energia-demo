<script setup>
import { ref } from 'vue';

const props = defineProps({
  communication: {
    type: Object,
    default: () => ({}),
  },
})

const title = ref(props.communication.title || '')
const subject = ref(props.communication.subject || '')
const body = ref(typeof props.communication.body === 'string' ? props.communication.body : '')
const sendEmail = ref(false)
const documents = ref([])
const refForm = ref()
const isSaving = ref(false)
const showSnackbar = ref(false)

const emit = defineEmits([
  'communicationData',
])

const downloadFile = async (communicationId, documentId) => {
  try {
    const response = await $api(`/communications/${communicationId}/documents/${documentId}/download`)
    if (response.downloadUrl) {
      window.open(response.downloadUrl, '_blank')
    }
  } catch (e) {
    console.error(e)
  }
}

const createCommunication = async () => {
  isSaving.value = true
  try {
    const formData = new FormData()

    formData.append('title', title.value)
    formData.append('subject', subject.value)
    formData.append('body', body.value)
    formData.append('send_email', sendEmail.value ? '1' : '0')

    documents.value.forEach(file => {
      formData.append('documents[]', file)
    })

    if (props.communication.id) {
      formData.append('_method', 'PUT')

      const data = await $api(`/communications/${ props.communication.id }`, {
        method: 'POST',
        body: formData,
      })

      showSnackbar.value = true
      isSaving.value = false
      
      setTimeout(() => {
        emit('communicationData', data)
      }, 1000)
    } else {
      const data = await $api('/communications', {
        method: 'POST',
        body: formData,
      })
      
      showSnackbar.value = true
      isSaving.value = false

      setTimeout(() => {
        emit('communicationData', data)
      }, 1000)
    }
  } catch (err) {
    isSaving.value = false
    console.error(err)
  }
}
</script>

<template>
  <VForm ref="refForm" @submit.prevent="createCommunication">
    <VRow>
      <!-- 👉 Title -->
      <VCol
        cols="12"
        md="9"
      >
        <AppTextField
          v-model="title"
          label="Titolo"
          placeholder="Titolo"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Subject -->
      <VCol
        cols="12"
        md="9"
      >
        <AppTextField
          v-model="subject"
          label="Oggetto"
          placeholder="Oggetto"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Body -->
      <VCol
        cols="12"
        md="9"
      >
        <AppEditor
          v-model="body"
          label="Contenuto"
          placeholder="Contenuto"
          :rules="[requiredValidator]"
          :editor-options="{
            placeholder: 'Contenuto',
            modules: {
              toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean']
              ]
            }
          }"
        />
      </VCol>

      <VCol
        cols="12"
        md="9"
      >
        <VFileInput
          v-model="documents"
          label="Allegati"
          placeholder="Seleziona file"
          multiple
          prepend-icon="tabler-paperclip"
        />

        <!-- Existing documents -->
        <VList v-if="props.communication.documents && props.communication.documents.length" class="mt-2 border rounded">
          <VListSubheader>Allegati esistenti</VListSubheader>
          <VListItem
            v-for="doc in props.communication.documents"
            :key="doc.id"
            :title="doc.name"
            prepend-icon="tabler-file"
          >
            <template #append>
              <VBtn
                icon="tabler-download"
                variant="text"
                color="primary"
                @click.prevent="downloadFile(props.communication.id, doc.id)"
              />
            </template>
          </VListItem>
        </VList>
      </VCol>

      <!-- Add a checkbox to send an email -->
      <VCol
        cols="12"
        md="9"
      >
        <VCheckbox v-model="sendEmail" label="Invia email" />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit" :disabled="isSaving">
          {{ props.communication.id ? 'Salva' : 'Crea' }}
        </VBtn>

        <VBtn
          v-if="! props.communication.id"
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>

  <VSnackbar
    v-model="showSnackbar"
    timeout="2000"
    location="top"
    :color="props.communication.id ? 'success' : 'success'"
  >
    {{ props.communication.id ? 'Comunicazione aggiornata con successo' : 'Comunicazione creata con successo' }}
  </VSnackbar>
</template>

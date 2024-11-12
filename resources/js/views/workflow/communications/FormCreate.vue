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
const refForm = ref()
const isSaving = ref(false)
const showSnackbar = ref(false)

const emit = defineEmits([
  'communicationData',
])

const createCommunication = async () => {
  isSaving.value = true
  try {
    if (props.communication.id) {
      const data = await $api(`/communications/${ props.communication.id }`, {
        method: 'PUT',
        body: { title: title.value, subject: subject.value, body: body.value, send_email: sendEmail.value },
      })
      showSnackbar.value = true
      isSaving.value = false
      emit('communicationData', data)
    } else {
      const data = await $api('/communications', {
        method: 'POST',
        body: { title: title.value, subject: subject.value, body: body.value, send_email: sendEmail.value },
      })
      isSaving.value = false
      emit('communicationData', data)
    }
  } catch (err) {
    isSaving.value = false
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
  >
    Comunicazione aggiornata con successo
  </VSnackbar>
</template>

<template>
  <VDialog
    v-model="isDialogVisible"
    persistent
    max-width="500"
  >
    <VCard>
      <VCardTitle class="text-h5 pa-6">
        🔐 Cambio Password Obbligatorio
      </VCardTitle>
      
      <VCardText class="pa-6 pt-0">
        <VAlert
          type="warning"
          class="mb-4"
        >
          Per motivi di sicurezza, devi cambiare la tua password al primo accesso.
        </VAlert>

        <VForm
          ref="refForm"
          @submit.prevent="handlePasswordChange"
        >
          <VRow>
            <!-- Nuova Password -->
            <VCol cols="12">
              <AppTextField
                v-model="form.newPassword"
                :type="isNewPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                label="Nuova Password"
                placeholder="Inserisci la nuova password"
                :rules="passwordRules"
                required
                autofocus
                @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
              />
            </VCol>

            <!-- Conferma Password -->
            <VCol cols="12">
              <AppTextField
                v-model="form.confirmPassword"
                :type="isConfirmPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                label="Conferma Password"
                placeholder="Ripeti la nuova password"
                :rules="confirmPasswordRules"
                required
                @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
              />
            </VCol>

            <!-- Messaggio di errore -->
            <VCol
              v-if="errorMessage"
              cols="12"
            >
              <VAlert
                type="error"
                class="mb-0"
              >
                {{ errorMessage }}
              </VAlert>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions class="pa-6 pt-0">
        <VSpacer />
        <VBtn
          :loading="isLoading"
          :disabled="!isFormValidComputed"
          color="primary"
          @click="handlePasswordChange"
        >
          Cambia Password
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'password-changed'])

const isDialogVisible = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val),
})

const refForm = ref()
const isFormValid = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

// Computed property per verificare se il form è valido
const isFormValidComputed = computed(() => {
  const newPassword = form.value.newPassword
  const confirmPassword = form.value.confirmPassword
  
  // Verifica tutte le regole della password
  const passwordValid = newPassword && 
    newPassword.length >= 8 &&
    /(?=.*[a-z])/.test(newPassword) &&
    /(?=.*[A-Z])/.test(newPassword) &&
    /(?=.*\d)/.test(newPassword)
  
  // Verifica che le password corrispondano
  const confirmPasswordValid = confirmPassword && confirmPassword === newPassword
  
  return passwordValid && confirmPasswordValid
})

const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

const form = ref({
  newPassword: '',
  confirmPassword: '',
})

// Regole di validazione
const passwordRules = [
  v => !!v || 'La password è obbligatoria',
  v => (v && v.length >= 8) || 'La password deve essere di almeno 8 caratteri',
  v => /(?=.*[a-z])/.test(v) || 'La password deve contenere almeno una lettera minuscola',
  v => /(?=.*[A-Z])/.test(v) || 'La password deve contenere almeno una lettera maiuscola',
  v => /(?=.*\d)/.test(v) || 'La password deve contenere almeno un numero',
]

const confirmPasswordRules = computed(() => [
  v => !!v || 'La conferma password è obbligatoria',
  v => v === form.value.newPassword || 'Le password non corrispondono',
])

const handlePasswordChange = async () => {
  // Valida il form
  const { valid } = await refForm.value.validate()
  if (!valid) return

  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await $api('/profile/password', {
      method: 'PUT',
      body: {
        new_password: form.value.newPassword,
        confirm_password: form.value.confirmPassword,
      },
    })

    // Aggiorna i dati utente nel cookie
    const userData = useCookie('userData')
    userData.value = { ...userData.value, must_change_password: false }

    // Emetti evento di successo
    emit('password-changed')
    
    // Chiudi il dialog
    isDialogVisible.value = false
    
    // Messaggio di successo
    // Puoi usare toast/snackbar qui se disponibile
    console.log('Password cambiata con successo!')

  } catch (error) {
    console.error('Errore nel cambio password:', error)
    errorMessage.value = error.data?.message || 'Errore durante il cambio password. Riprova.'
  } finally {
    isLoading.value = false
  }
}

// Reset form quando il dialog si chiude
watch(isDialogVisible, (newVal) => {
  if (!newVal) {
    form.value = {
      newPassword: '',
      confirmPassword: '',
    }
    errorMessage.value = ''
    if (refForm.value) {
      refForm.value.resetValidation()
    }
  }
})
</script>

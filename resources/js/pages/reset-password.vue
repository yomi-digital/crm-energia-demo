<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';

const route = useRoute()
const router = useRouter()

const form = ref({
  password: '',
  password_confirmation: '',
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const refVForm = ref()

// Leggi token ed email dalla query string
const token = computed(() => route.query.token || '')
const email = computed(() => route.query.email || '')

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

// Verifica che token ed email siano presenti
onMounted(() => {
  if (!token.value || !email.value) {
    errorMessage.value = 'Link di reset non valido. Token o email mancanti.'
  }
})

const resetPassword = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  
  if (!token.value || !email.value) {
    errorMessage.value = 'Link di reset non valido. Token o email mancanti.'
    return
  }
  
  const { valid } = await refVForm.value?.validate()
  if (!valid) return
  
  isLoading.value = true
  try {
    await $api('/auth/reset-password', {
      method: 'POST',
      body: {
        email: email.value,
        token: token.value,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
      },
      onResponseError({ response }) {
        if (response.status === 422) {
          const errors = response._data?.errors
          if (errors?.password) {
            errorMessage.value = Array.isArray(errors.password) ? errors.password[0] : errors.password
          } else if (errors?.token) {
            errorMessage.value = Array.isArray(errors.token) ? errors.token[0] : errors.token
          } else {
            errorMessage.value = response._data?.message || 'Errore durante il reset della password'
          }
        } else {
          errorMessage.value = response._data?.message || 'Token non valido o scaduto. Richiedi un nuovo link di reset.'
        }
      },
    })
    
    successMessage.value = 'Password reimpostata con successo! Verrai reindirizzato al login...'
    
    // Reindirizza al login dopo 2 secondi
    setTimeout(() => {
      router.push({ name: 'login' })
    }, 2000)
  } catch (err) {
    console.error('Errore:', err)
    if (!errorMessage.value) {
      errorMessage.value = 'Si è verificato un errore. Riprova più tardi.'
    }
  } finally {
    isLoading.value = false
  }
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      resetPassword()
    }
  })
}

// Validazione password
const passwordRules = [
  v => !!v || 'Password obbligatoria',
  v => (v && v.length >= 8) || 'La password deve essere di almeno 8 caratteri',
]

const confirmPasswordRules = [
  v => !!v || 'Conferma password obbligatoria',
  v => v === form.value.password || 'Le password non corrispondono',
]
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth Card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <div class="app-logo">
              <VNodeRenderer :nodes="themeConfig.app.logo" />
            </div>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Reset Password 🔒
          </h4>
          <p class="mb-0">
            Inserisci la tua nuova password. La password deve essere di almeno 8 caratteri.
          </p>
        </VCardText>

        <VCardText>
          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  autofocus
                  label="Nuova Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  :rules="passwordRules"
                />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password_confirmation"
                  label="Conferma Password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  :rules="confirmPasswordRules"
                />
              </VCol>

              <!-- Success message -->
              <VCol cols="12" v-if="successMessage">
                <VAlert
                  type="success"
                  elevation="1"
                >
                  {{ successMessage }}
                </VAlert>
              </VCol>

              <!-- Error message -->
              <VCol cols="12" v-if="errorMessage">
                <VAlert
                  type="error"
                  elevation="1"
                  closable
                  @click:close="errorMessage = ''"
                >
                  {{ errorMessage }}
                </VAlert>
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="isLoading || !token || !email"
                >
                  {{ isLoading ? 'Reset in corso...' : 'Imposta Nuova Password' }}
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'login' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
                  <span>Torna al login</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>


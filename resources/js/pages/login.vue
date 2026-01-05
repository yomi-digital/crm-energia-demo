<!-- ❗Errors in the form are set on line 60 -->
<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components/VForm'
import { VAlert } from 'vuetify/lib/components/index.mjs'

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const isPasswordVisible = ref(false)
const route = useRoute()
const router = useRouter()
const ability = useAbility()

const errors = ref({
  email: undefined,
  password: undefined,
  message: undefined,
})

const refVForm = ref()

const credentials = ref({
  email: '',
  password: '',
  // email: 'admin@demo.com',
  // password: 'admin',
})

const rememberMe = ref(false)
const showForcePasswordDialog = ref(false)

const login = async () => {
  console.log("Stiamo per fare il login")
  errors.value.email = false
  errors.value.password = false
  errors.value.message = undefined
  try {
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        email: credentials.value.email,
        password: credentials.value.password,
      },
      onResponseError({ response }) {
        if (response.status === 401) {
          // errors.value.email = true
          // errors.value.password = true
          console.log("Ci sono stati errori")
          errors.value.message = 'Credenziali non valide'
          return
        }
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    useLocalStorage('userAbilityRules').value = JSON.stringify(userAbilityRules)
    ability.update(userAbilityRules)
    
    // Rimuovi commercial_profile dal cookie per rispettare il limite di 4KB
    const userDataForCookie = { ...userData }
    delete userDataForCookie.commercial_profile
    
    useCookie('userData').value = userDataForCookie
    useCookie('accessToken').value = accessToken

    console.log("userData", userData)
    console.log("accessToken", accessToken)

    await nextTick(() => {
      // Controlla se l'utente deve cambiare la password
      if (userData.must_change_password) {
        // Mostra il dialog di cambio password invece di reindirizzare
        showForcePasswordDialog.value = true
      } else {
        // Login normale, reindirizza alla dashboard
        router.replace(route.query.to ? String(route.query.to) : '/')
      }
    })
  } catch (err) {
    console.error(err)
  }
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid)
      login()
  })
}

const handlePasswordChanged = () => {
  // Password cambiata con successo, reindirizza alla dashboard
  router.replace(route.query.to ? String(route.query.to) : '/')
}

// Controlla al mount se l'utente è già loggato e deve cambiare password
onMounted(() => {
  const userData = useCookie('userData')?.value
  const accessToken = useCookie('accessToken')?.value
  
  // Se l'utente è già autenticato e deve cambiare la password, mostra il modal
  if (userData && accessToken && userData?.must_change_password) {
    showForcePasswordDialog.value = true
  }
})
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
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
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
            Benvenuto in <span class="text-capitalize">{{ themeConfig.app.title }}</span>! 👋🏻
          </h4>
          <!-- <p class="mb-0">
            Effettua l'accesso
          </p> -->
        </VCardText>

        <VCardText>
          <VForm ref="refVForm"
            @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.email"
                  autofocus
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                  :error="errors.email"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  :error="errors.password"
                />

                <!-- remember me checkbox -->
                <div class="d-flex align-center justify-space-between flex-wrap my-6">
                  <VCheckbox
                    v-model="rememberMe"
                    label="Ricordami"
                  />

                  <RouterLink
                    class="text-primary"
                    :to="{ name: 'forgot-password' }"
                  >
                    Password Dimenticata?
                  </RouterLink>
                </div>

                <!-- login button -->
                <VBtn
                  block
                  type="submit"
                >
                  Login
                </VBtn>
                <VAlert
                  v-if="errors.message"
                  type="error"
                  elevation="1"
                  class="mt-4"
                >
                  {{ errors.message }}
                </VAlert>
              </VCol>

              <!-- <VCol
                cols="12"
                class="d-flex align-center"
              >
                <VDivider />
                <span class="mx-4 text-high-emphasis">or</span>
                <VDivider />
              </VCol> -->

              <!-- auth providers -->
              <!-- <VCol
                cols="12"
                class="text-center"
              >
                <AuthProvider />
              </VCol> -->
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>

  <!-- Dialog per cambio password obbligatorio -->
  <ForcePasswordChangeDialog
    v-model="showForcePasswordDialog"
    @password-changed="handlePasswordChanged"
  />
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

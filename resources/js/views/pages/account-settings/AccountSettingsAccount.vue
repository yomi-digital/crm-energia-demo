<script setup>

const userData = useCookie('userData')
console.log(userData.value)

const accountData = {
  avatar: userData.value.avatar,
  name: userData.value.name,
  last_name: userData.value.last_name,
  email: userData.value.email,
  phone: userData.value.phone,
}

const refInputEl = ref()
const accountDataLocal = ref(structuredClone(accountData))

const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData)
}

const changeAvatar = async file => {
  const fileReader = new FileReader()
  const { files } = file.target
  if (files && files.length) {
    // Create form data and append file
    const formData = new FormData()
    formData.append('file', files[0])
    formData.append('scope', 'avatars')

    // Upload file
    const response = await $api('/uploads', {
      method: 'POST',
      body: formData,
    })

    // Update avatar path with uploaded file path
    accountDataLocal.value.avatar = response.path
    // fileReader.readAsDataURL(files[0])
    // fileReader.onload = () => {
    //   if (typeof fileReader.result === 'string')
    //     accountDataLocal.value.avatar = fileReader.result
    // }
  }
}

// reset avatar image
const resetAvatar = () => {
  accountDataLocal.value.avatar = accountData.avatar
}

const removeAvatar = () => {
  accountDataLocal.value.avatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(accountDataLocal.value.name + ' ' + accountDataLocal.value.last_name) + '&background=random&color=fff'
}

const saveChanges = async () => {
  const data = await $api(`/profile`, {
    method: 'PUT',
    body: accountDataLocal.value,
  })

  let overrideUserData = useCookie('userData').value

  // Update cookie value useCookie('userData')
  overrideUserData.avatar = data.avatar
  overrideUserData.name = data.name
  overrideUserData.last_name = data.last_name
  overrideUserData.email = data.email
  overrideUserData.phone = data.phone

  useCookie('userData').value = overrideUserData

  // Force refresh of the page
  window.location.reload()
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded
            size="100"
            class="me-6"
            :image="accountDataLocal.avatar"
          />

          <!-- 👉 Upload Photo -->
          <form class="d-flex flex-column justify-center gap-4">
            <div class="d-flex flex-wrap gap-4">
              <VBtn
                color="primary"
                size="small"
                @click="refInputEl?.click()"
              >
                <VIcon
                  icon="tabler-cloud-upload"
                  class="d-sm-none"
                />
                <span class="d-none d-sm-block">Carica nuova foto</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg,GIF"
                hidden
                @input="changeAvatar"
              >

              <VBtn
                type="reset"
                size="small"
                color="secondary"
                variant="tonal"
                @click="resetAvatar"
              >
                <span class="d-none d-sm-block">Reset</span>
                <VIcon
                  icon="tabler-refresh"
                  class="d-sm-none"
                />
              </VBtn>

              <VBtn
                type="button"
                size="small"
                color="error"
                variant="tonal"
                @click="removeAvatar"
              >
                <span class="d-none d-sm-block">Rimuovi</span>
                <VIcon
                  icon="tabler-refresh"
                  class="d-sm-none"
                />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Consenti JPG, GIF o PNG. Massimo 800K
            </p>
          </form>
        </VCardText>

        <VCardText class="pt-2">
          <!-- 👉 Form -->
          <VForm class="mt-3">
            <VRow>
              <!-- 👉 First Name -->
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="accountDataLocal.name"
                  placeholder="John"
                  label="Nome"
                />
              </VCol>

              <!-- 👉 Last Name -->
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="accountDataLocal.last_name"
                  placeholder="Doe"
                  label="Cognome"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountDataLocal.email"
                  label="Email"
                  placeholder="johndoe@gmail.com"
                  type="email"
                />
              </VCol>

              <!-- 👉 Phone -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountDataLocal.phone"
                  label="Telefono"
                  placeholder="+39 333 333 3333"
                />
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
              >
                <VBtn @click="saveChanges">Salva Modifiche</VBtn>

                <VBtn
                  color="secondary"
                  variant="tonal"
                  type="reset"
                  @click.prevent="resetForm"
                >
                  Annulla
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

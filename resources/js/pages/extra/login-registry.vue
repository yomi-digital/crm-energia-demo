<script setup>
definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    action: 'access',
    subject: 'extra-login-registry',
  },
})

// Controllo permessi admin
const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

// Redirect se non è admin
if (!isAdmin) {
  await navigateTo('/not-authorized')
}
</script>

<template>
  <VCard>
    <VCardText>
      <h1 class="text-h3 mb-4">
        Registro Accessi
      </h1>
    </VCardText>
  </VCard>
</template> 

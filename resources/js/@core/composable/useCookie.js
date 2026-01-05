// Ported from [Nuxt](https://github.com/nuxt/nuxt/blob/main/packages/nuxt/src/app/composables/cookie.ts)
import { parse, serialize } from 'cookie-es'
import { destr } from 'destr'

const CookieDefaults = {
  path: '/',
  watch: true,
  decode: val => destr(decodeURIComponent(val)),
  encode: val => encodeURIComponent(typeof val === 'string' ? val : JSON.stringify(val)),
}

export const useCookie = (name, _opts) => {
  const opts = { ...CookieDefaults, ..._opts || {} }
  const cookies = parse(document.cookie, opts)
  
  // Per userData, se non esiste nel cookie, prova nel localStorage
  let initialValue = cookies[name]
  if (!initialValue && name === 'userData') {
    try {
      const stored = localStorage.getItem('userData')
      if (stored) {
        initialValue = JSON.parse(stored)
      }
    } catch (e) {
      console.warn(`[useCookie] Errore nel recupero di "${name}" da localStorage:`, e)
    }
  }
  
  const cookie = ref(initialValue ?? opts.default?.())

  watch(cookie, () => {
    // Per userData, salva sempre in localStorage invece che nel cookie (per evitare limite 4KB)
    if (name === 'userData') {
      try {
        localStorage.setItem('userData', JSON.stringify(cookie.value))
      } catch (e) {
        console.error(`[useCookie] Errore nel salvataggio di "${name}" in localStorage:`, e)
      }
      // Salta il salvataggio poiché è troppo grande per il cookie userData
      return
    }
    
    // Per gli altri cookie, usa il comportamento normale
    const cookieString = serializeCookie(name, cookie.value, opts)
    document.cookie = cookieString
  })
  
  return cookie
}
function serializeCookie(name, value, opts = {}) {
  if (value === null || value === undefined) {
    const result = serialize(name, value, { ...opts, maxAge: -1 })
    return result
  }
  
  const result = serialize(name, value, opts)
  return result
}

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
        console.log(`[useCookie] "${name}" recuperato da localStorage`)
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
        console.log(`[useCookie] "${name}" salvato in localStorage`)
      } catch (e) {
        console.error(`[useCookie] Errore nel salvataggio di "${name}" in localStorage:`, e)
      }
      // Non salvare userData nel cookie, è troppo grande
      return
    }
    
    // Per gli altri cookie, usa il comportamento normale
    const cookieString = serializeCookie(name, cookie.value, opts)
    console.log(`[useCookie] Tentativo di impostare cookie "${name}":`, {
      cookieString,
      length: cookieString.length,
      value: cookie.value,
      opts
    })
    document.cookie = cookieString
    // Verifica se è stato impostato
    const cookiesAfter = parse(document.cookie, opts)
    console.log(`[useCookie] Cookie "${name}" dopo impostazione:`, {
      exists: name in cookiesAfter,
      value: cookiesAfter[name],
      allCookies: document.cookie
    })
  })
  
  return cookie
}
function serializeCookie(name, value, opts = {}) {
  if (value === null || value === undefined) {
    const result = serialize(name, value, { ...opts, maxAge: -1 })
    console.log(`[serializeCookie] Cookie "${name}" con valore null/undefined:`, result)
    return result
  }
  
  try {
    // Prova a serializzare il valore per vedere se ci sono errori
    const encoded = typeof value === 'string' ? encodeURIComponent(value) : encodeURIComponent(JSON.stringify(value))
    console.log(`[serializeCookie] Encoding per "${name}":`, {
      originalType: typeof value,
      encodedLength: encoded.length,
      encodedPreview: encoded.substring(0, 100) + (encoded.length > 100 ? '...' : '')
    })
  } catch (error) {
    console.error(`[serializeCookie] Errore durante encoding per "${name}":`, error)
  }
  
  const result = serialize(name, value, opts)
  console.log(`[serializeCookie] Risultato serializzazione per "${name}":`, {
    result,
    resultLength: result.length,
    opts
  })
  return result
}

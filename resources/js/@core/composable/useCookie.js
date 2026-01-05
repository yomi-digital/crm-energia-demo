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
  const cookie = ref(cookies[name] ?? opts.default?.())

  watch(cookie, () => {
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

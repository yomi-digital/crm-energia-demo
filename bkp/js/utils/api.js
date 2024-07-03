import { ofetch } from 'ofetch'

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value

    if (options.method !== 'GET') {
      const csrfToken = await fetch('/api/auth/csrf').then(response => response.json()).then(data => data.csrf_token)

      if (csrfToken) {
        options.headers = {
          ...options.headers,
          "X-CSRF-TOKEN": `${csrfToken}`,
        }
      }
    }

    if (accessToken) {
      options.headers = {
        ...options.headers,
        Authorization: `Bearer ${accessToken}`,
      }
    }
  },
})

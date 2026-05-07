import axios from 'axios'

export const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  withXSRFToken: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
})
// HElper qui garantit que la session est maintenue en obtenant d'abord le cookie CSRF avant de faire la requête GET
export async function authGet(url) {
  await api.get('/sanctum/csrf-cookie')
  return api.get(url)
}
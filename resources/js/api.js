import axios from 'axios'

export const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  withCredentials: true,
})
// HElper qui garantit que la session est maintenue en obtenant d'abord le cookie CSRF avant de faire la requête GET
export async function authGet(url) {
  await api.get('/sanctum/csrf-cookie')
  return api.get(url)
}
import axios from 'axios'

export function useApi() {
  const api = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
  })

  async function get(url: string, params?: any) {
    return api.get(url, { params })
  }

  async function post(url: string, data?: any) {
    return api.post(url, data)
  }

  async function put(url: string, data?: any) {
    return api.put(url, data)
  }

  async function del(url: string) {
    return api.delete(url)
  }

  return { get, post, put, del }
}

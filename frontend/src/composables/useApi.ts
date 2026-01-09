import axiosInstance from '@/lib/axios'
import { AxiosResponse } from 'axios'

export function useApi() {
  async function get<T = any>(url: string, params?: any): Promise<AxiosResponse<T>> {
    return axiosInstance.get<T>(`/api/v1${url}`, { params })
  }

  async function post<T = any>(url: string, data?: any): Promise<AxiosResponse<T>> {
    return axiosInstance.post<T>(`/api/v1${url}`, data)
  }

  async function put<T = any>(url: string, data?: any): Promise<AxiosResponse<T>> {
    return axiosInstance.put<T>(`/api/v1${url}`, data)
  }

  async function del<T = any>(url: string): Promise<AxiosResponse<T>> {
    return axiosInstance.delete<T>(`/api/v1${url}`)
  }

  async function patch<T = any>(url: string, data?: any): Promise<AxiosResponse<T>> {
    return axiosInstance.patch<T>(`/api/v1${url}`, data)
  }

  return { get, post, put, del, patch }
}

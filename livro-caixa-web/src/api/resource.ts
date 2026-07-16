import { api } from "@/api/client"
import type { Paginated } from "@/types"

/**
 * Thin wrapper around the repeated index/store/show/update/destroy shape
 * shared by every simple CRUD endpoint (books, categories, vehicles, fuel
 * suppliers, fuel products, nfe links) — avoids re-writing the same five
 * axios calls per resource.
 */
export function createResourceApi<T, TPayload = Partial<T>>(path: string) {
  return {
    list: (params?: Record<string, unknown>) =>
      api.get(path, { params }).then((r) => r.data.data as T[]),
    listPaginated: (params?: Record<string, unknown>) =>
      api.get(path, { params }).then((r) => r.data as Paginated<T>),
    get: (id: number) => api.get(`${path}/${id}`).then((r) => r.data.data as T),
    create: (payload: TPayload) =>
      api.post(path, payload).then((r) => r.data.data as T),
    update: (id: number, payload: TPayload) =>
      api.put(`${path}/${id}`, payload).then((r) => r.data.data as T),
    remove: (id: number) => api.delete(`${path}/${id}`),
  }
}

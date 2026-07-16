import { api } from "@/api/client"
import type { DashboardSummary, Movement, MovementSummary, Paginated } from "@/types"

export interface MovementFilters {
  month?: number
  year?: number
  date_from?: string
  date_to?: string
  book_id?: number
  page_from?: number
  page_to?: number
  q?: string
  page?: number
  per_page?: number
}

export interface MovementPayload {
  book_id: number
  category_id: number | null
  page_number: number
  type: "credit" | "debit"
  description: string
  amount: number
  movement_date: string
}

export const movementsApi = {
  list: (filters: MovementFilters = {}) =>
    api.get("/movements", { params: filters }).then((r) => r.data as Paginated<Movement>),
  // Backend shape depends on the filter: month/year → {month, year}, date range → a flat summary.
  summaryForMonth: (month: number, year: number) =>
    api
      .get("/movements/summary", { params: { month, year } })
      .then((r) => r.data.data as DashboardSummary),
  summaryForRange: (dateFrom: string, dateTo: string) =>
    api
      .get("/movements/summary", { params: { date_from: dateFrom, date_to: dateTo } })
      .then((r) => r.data.data as MovementSummary),
  create: (payload: MovementPayload) =>
    api.post("/movements", payload).then((r) => r.data.data as Movement),
  update: (id: number, payload: MovementPayload) =>
    api.put(`/movements/${id}`, payload).then((r) => r.data.data as Movement),
  remove: (id: number) => api.delete(`/movements/${id}`),
}

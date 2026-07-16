import { api } from "@/api/client"
import type { Paginated, Refueling, RefuelingEfficiency } from "@/types"

export interface RefuelingPayload {
  vehicle_id: number
  fuel_supplier_id: number
  fuel_product_id: number
  nfe_link_id: number | null
  invoice_number: string
  access_key: string | null
  refueled_at: string
  odometer_km: number
  quantity: number
  unit_price: number
  notes: string | null
  target_efficiency_km_per_liter?: number
}

export const refuelingsApi = {
  list: (params: { vehicle_id?: number; page?: number } = {}) =>
    api.get("/refuelings", { params }).then((r) => r.data as Paginated<Refueling>),
  create: (payload: RefuelingPayload) =>
    api.post("/refuelings", payload).then((r) => r.data.data as Refueling),
  update: (id: number, payload: RefuelingPayload) =>
    api.put(`/refuelings/${id}`, payload).then((r) => r.data.data as Refueling),
  remove: (id: number) => api.delete(`/refuelings/${id}`),
}

export async function fetchVehicleEfficiency(vehicleId: number) {
  const { data } = await api.get(`/vehicles/${vehicleId}/efficiency`)
  return {
    refuelings: data.data as Refueling[],
    efficiency: data.meta.efficiency as RefuelingEfficiency[],
  }
}

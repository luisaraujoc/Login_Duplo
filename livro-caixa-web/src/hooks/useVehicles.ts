import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { vehiclesApi } from "@/api/resources"
import { fetchVehicleEfficiency } from "@/api/refuelings"
import type { Vehicle } from "@/types"

export function useVehiclesQuery() {
  return useQuery({ queryKey: ["vehicles"], queryFn: () => vehiclesApi.list() })
}

export function useVehicleEfficiencyQuery(vehicleId: number | undefined) {
  return useQuery({
    queryKey: ["vehicles", vehicleId, "efficiency"],
    queryFn: () => fetchVehicleEfficiency(vehicleId as number),
    enabled: vehicleId !== undefined,
  })
}

export function useVehicleMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ["vehicles"] })

  return {
    create: useMutation({
      mutationFn: (payload: Partial<Vehicle>) => vehiclesApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: Partial<Vehicle> }) =>
        vehiclesApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => vehiclesApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

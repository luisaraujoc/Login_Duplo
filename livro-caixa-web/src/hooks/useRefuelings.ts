import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { refuelingsApi, type RefuelingPayload } from "@/api/refuelings"

export function useRefuelingsQuery(vehicleId?: number) {
  return useQuery({
    queryKey: ["refuelings", { vehicleId }],
    queryFn: () => refuelingsApi.list({ vehicle_id: vehicleId }),
  })
}

export function useRefuelingMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: ["refuelings"] })
    queryClient.invalidateQueries({ queryKey: ["vehicles"] })
  }

  return {
    create: useMutation({
      mutationFn: (payload: RefuelingPayload) => refuelingsApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: RefuelingPayload }) =>
        refuelingsApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => refuelingsApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { fuelProductsApi } from "@/api/resources"
import type { FuelProduct } from "@/types"

export function useFuelProductsQuery() {
  return useQuery({ queryKey: ["fuel-products"], queryFn: () => fuelProductsApi.list() })
}

export function useFuelProductMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ["fuel-products"] })

  return {
    create: useMutation({
      mutationFn: (payload: Partial<FuelProduct>) => fuelProductsApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: Partial<FuelProduct> }) =>
        fuelProductsApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => fuelProductsApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

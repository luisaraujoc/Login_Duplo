import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { fuelSuppliersApi } from "@/api/resources"
import type { FuelSupplier } from "@/types"

export function useFuelSuppliersQuery() {
  return useQuery({ queryKey: ["fuel-suppliers"], queryFn: () => fuelSuppliersApi.list() })
}

export function useFuelSupplierMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ["fuel-suppliers"] })

  return {
    create: useMutation({
      mutationFn: (payload: Partial<FuelSupplier>) => fuelSuppliersApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: Partial<FuelSupplier> }) =>
        fuelSuppliersApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => fuelSuppliersApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

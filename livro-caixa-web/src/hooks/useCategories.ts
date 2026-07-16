import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { categoriesApi } from "@/api/resources"
import type { Category } from "@/types"

export function useCategoriesQuery() {
  return useQuery({ queryKey: ["categories"], queryFn: () => categoriesApi.list() })
}

export function useCategoryMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ["categories"] })

  return {
    create: useMutation({
      mutationFn: (payload: Partial<Category>) => categoriesApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: Partial<Category> }) =>
        categoriesApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => categoriesApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

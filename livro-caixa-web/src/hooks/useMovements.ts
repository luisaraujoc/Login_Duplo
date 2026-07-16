import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { movementsApi, type MovementFilters, type MovementPayload } from "@/api/movements"

export function useMovementsQuery(filters: MovementFilters) {
  return useQuery({
    queryKey: ["movements", filters],
    queryFn: () => movementsApi.list(filters),
    placeholderData: (previous) => previous,
  })
}

export function useMonthlySummaryQuery(month: number, year: number) {
  return useQuery({
    queryKey: ["movements", "summary", "month", month, year],
    queryFn: () => movementsApi.summaryForMonth(month, year),
  })
}

export function useRangeSummaryQuery(dateFrom: string, dateTo: string) {
  return useQuery({
    queryKey: ["movements", "summary", "range", dateFrom, dateTo],
    queryFn: () => movementsApi.summaryForRange(dateFrom, dateTo),
  })
}

export function useMovementMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ["movements"] })

  return {
    create: useMutation({
      mutationFn: (payload: MovementPayload) => movementsApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: MovementPayload }) =>
        movementsApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => movementsApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

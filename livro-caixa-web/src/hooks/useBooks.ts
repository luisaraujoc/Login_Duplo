import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { booksApi } from "@/api/resources"
import type { Book } from "@/types"

export function useBooksQuery() {
  return useQuery({ queryKey: ["books"], queryFn: () => booksApi.list() })
}

export function useBookMutations() {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ["books"] })

  return {
    create: useMutation({
      mutationFn: (payload: Partial<Book>) => booksApi.create(payload),
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, payload }: { id: number; payload: Partial<Book> }) =>
        booksApi.update(id, payload),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (id: number) => booksApi.remove(id),
      onSuccess: invalidate,
    }),
  }
}

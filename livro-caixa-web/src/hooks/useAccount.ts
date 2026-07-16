import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { accountsApi, type CreateAccountPayload } from "@/api/accounts"

export function useAccountsQuery() {
  return useQuery({
    queryKey: ["accounts"],
    queryFn: accountsApi.list,
  })
}

export function useCurrentAccountQuery() {
  return useQuery({
    queryKey: ["accounts", "current"],
    queryFn: accountsApi.current,
  })
}

export function useCreateAccountMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: CreateAccountPayload) => accountsApi.create(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["accounts"] })
    },
  })
}

export function useSelectAccountMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (accountId: number) => accountsApi.select(accountId),
    onSuccess: (account) => {
      queryClient.setQueryData(["accounts", "current"], account)
      // Everything account-scoped (books, movements, ...) must be refetched.
      queryClient.invalidateQueries({ queryKey: ["books"] })
      queryClient.invalidateQueries({ queryKey: ["movements"] })
    },
  })
}

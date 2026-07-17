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

export function useAccountMembersQuery(accountId: number | undefined) {
  return useQuery({
    queryKey: ["accounts", accountId, "users"],
    queryFn: () => accountsApi.members(accountId as number),
    enabled: accountId !== undefined,
  })
}

export function useAccountMemberMutations(accountId: number | undefined) {
  const queryClient = useQueryClient()
  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: ["accounts", accountId, "users"] })

  return {
    invite: useMutation({
      mutationFn: (userId: number) => accountsApi.inviteUser(accountId as number, userId),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: (userId: number) => accountsApi.removeUser(accountId as number, userId),
      onSuccess: invalidate,
    }),
  }
}

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { authApi, type LoginPayload, type RegisterPayload } from "@/api/auth"

export function useMeQuery() {
  return useQuery({
    queryKey: ["me"],
    queryFn: authApi.me,
    // A 401 here just means "not logged in" — not worth retrying.
    retry: false,
  })
}

export function useLoginMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: LoginPayload) => authApi.login(payload),
    onSuccess: (user) => {
      queryClient.setQueryData(["me"], user)
    },
  })
}

export function useRegisterMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: RegisterPayload) => authApi.register(payload),
    onSuccess: (user) => {
      queryClient.setQueryData(["me"], user)
    },
  })
}

export function useUpdateMeMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: authApi.updateMe,
    onSuccess: (user) => {
      queryClient.setQueryData(["me"], user)
    },
  })
}

export function useLogoutMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: authApi.logout,
    onSuccess: () => {
      queryClient.clear()
    },
  })
}

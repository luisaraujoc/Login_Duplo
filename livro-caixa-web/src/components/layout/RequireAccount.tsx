import { Navigate, Outlet } from "react-router-dom"
import { useCurrentAccountQuery } from "@/hooks/useAccount"
import { Skeleton } from "@/components/ui/skeleton"

/**
 * Gates the Livro Caixa area behind an active account, mirroring the
 * legacy "second login" step — minus the unused account password.
 */
export function RequireAccount() {
  const { data: account, isLoading } = useCurrentAccountQuery()

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <Skeleton className="h-8 w-48" />
      </div>
    )
  }

  if (!account) {
    return <Navigate to="/select-account" replace />
  }

  return <Outlet />
}

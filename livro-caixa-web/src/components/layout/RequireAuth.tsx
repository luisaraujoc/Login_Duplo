import { Navigate, Outlet, useLocation } from "react-router-dom"
import { useMeQuery } from "@/hooks/useAuth"
import { Skeleton } from "@/components/ui/skeleton"

export function RequireAuth() {
  const { data: user, isLoading, isError } = useMeQuery()
  const location = useLocation()

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <Skeleton className="h-8 w-48" />
      </div>
    )
  }

  if (isError || !user) {
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  return <Outlet />
}

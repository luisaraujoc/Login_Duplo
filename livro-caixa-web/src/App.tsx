import { Navigate, Route, Routes } from "react-router-dom"
import { RequireAuth } from "@/components/layout/RequireAuth"
import { RequireAccount } from "@/components/layout/RequireAccount"
import { AppShell } from "@/components/layout/AppShell"
import { LoginPage } from "@/pages/LoginPage"
import { RegisterPage } from "@/pages/RegisterPage"
import { SelectAccountPage } from "@/pages/SelectAccountPage"
import { DashboardPage } from "@/pages/DashboardPage"
import { MovementsPage } from "@/pages/MovementsPage"
import { CategoriesPage } from "@/pages/CategoriesPage"
import { BooksPage } from "@/pages/BooksPage"
import { ProfilePage } from "@/pages/ProfilePage"
import { VehiclesPage } from "@/pages/fleet/VehiclesPage"
import { FuelSuppliersPage } from "@/pages/fleet/FuelSuppliersPage"
import { FuelProductsPage } from "@/pages/fleet/FuelProductsPage"
import { RefuelingsPage } from "@/pages/fleet/RefuelingsPage"
import { VehicleEfficiencyPage } from "@/pages/fleet/VehicleEfficiencyPage"

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />

      <Route element={<RequireAuth />}>
        <Route path="/select-account" element={<SelectAccountPage />} />

        <Route element={<RequireAccount />}>
          <Route element={<AppShell />}>
            <Route path="/" element={<DashboardPage />} />
            <Route path="/movements" element={<MovementsPage />} />
            <Route path="/categories" element={<CategoriesPage />} />
            <Route path="/books" element={<BooksPage />} />
            <Route path="/profile" element={<ProfilePage />} />

            <Route path="/fleet/vehicles" element={<VehiclesPage />} />
            <Route path="/fleet/vehicles/:id" element={<VehicleEfficiencyPage />} />
            <Route path="/fleet/fuel-suppliers" element={<FuelSuppliersPage />} />
            <Route path="/fleet/fuel-products" element={<FuelProductsPage />} />
            <Route path="/fleet/refuelings" element={<RefuelingsPage />} />
          </Route>
        </Route>
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}

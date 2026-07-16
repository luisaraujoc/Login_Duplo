import { NavLink, Outlet, useNavigate } from "react-router-dom"
import { cn } from "@/lib/utils"
import { useMeQuery, useLogoutMutation } from "@/hooks/useAuth"
import { useCurrentAccountQuery } from "@/hooks/useAccount"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"

const navGroups = [
  {
    label: "Livro Caixa",
    items: [
      { to: "/", label: "Dashboard", end: true },
      { to: "/movements", label: "Lançamentos" },
      { to: "/categories", label: "Categorias" },
      { to: "/books", label: "Livros" },
    ],
  },
  {
    label: "Frota",
    items: [
      { to: "/fleet/vehicles", label: "Veículos" },
      { to: "/fleet/fuel-suppliers", label: "Fornecedores" },
      { to: "/fleet/fuel-products", label: "Produtos" },
      { to: "/fleet/refuelings", label: "Abastecimentos" },
    ],
  },
]

export function AppShell() {
  const navigate = useNavigate()
  const { data: user } = useMeQuery()
  const { data: account } = useCurrentAccountQuery()
  const logout = useLogoutMutation()

  async function handleLogout() {
    await logout.mutateAsync()
    navigate("/login", { replace: true })
  }

  const initials = user?.name
    .split(" ")
    .map((part) => part[0])
    .slice(0, 2)
    .join("")
    .toUpperCase()

  return (
    <div className="flex min-h-screen">
      <aside className="hidden w-56 shrink-0 border-r bg-muted/30 p-4 md:block">
        <div className="mb-6 px-2 text-lg font-semibold">Livro Caixa</div>
        <nav className="space-y-6">
          {navGroups.map((group) => (
            <div key={group.label}>
              <p className="mb-2 px-2 text-xs font-medium uppercase text-muted-foreground">
                {group.label}
              </p>
              <div className="space-y-1">
                {group.items.map((item) => (
                  <NavLink
                    key={item.to}
                    to={item.to}
                    end={item.end}
                    className={({ isActive }) =>
                      cn(
                        "block rounded-md px-2 py-1.5 text-sm hover:bg-muted",
                        isActive && "bg-muted font-medium text-foreground"
                      )
                    }
                  >
                    {item.label}
                  </NavLink>
                ))}
              </div>
            </div>
          ))}
        </nav>
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="flex h-14 items-center justify-between border-b px-4">
          <span className="text-sm text-muted-foreground">
            Conta: <span className="font-medium text-foreground">{account?.name}</span>
          </span>

          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" className="gap-2">
                <Avatar className="size-6">
                  <AvatarFallback className="text-xs">{initials}</AvatarFallback>
                </Avatar>
                {user?.name}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem onSelect={() => navigate("/profile")}>
                Meus Dados
              </DropdownMenuItem>
              <DropdownMenuItem onSelect={() => navigate("/select-account")}>
                Trocar Conta
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem onSelect={handleLogout}>Sair</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </header>

        <main className="flex-1 p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

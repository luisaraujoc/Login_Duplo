import { useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "sonner"
import { Settings } from "lucide-react"
import {
  useAccountsQuery,
  useCreateAccountMutation,
  useSelectAccountMutation,
} from "@/hooks/useAccount"
import { useLogoutMutation, useMeQuery } from "@/hooks/useAuth"
import { ApiError } from "@/api/client"
import type { Account } from "@/types"
import { AccountMembersDialog } from "@/components/accounts/AccountMembersDialog"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Skeleton } from "@/components/ui/skeleton"

export function SelectAccountPage() {
  const navigate = useNavigate()
  const { data: user } = useMeQuery()
  const { data: accounts, isLoading } = useAccountsQuery()
  const selectAccount = useSelectAccountMutation()
  const createAccount = useCreateAccountMutation()
  const logout = useLogoutMutation()

  const [showCreate, setShowCreate] = useState(false)
  const [name, setName] = useState("")
  const [managingAccount, setManagingAccount] = useState<Account | null>(null)

  async function handleSelect(accountId: number) {
    try {
      await selectAccount.mutateAsync(accountId)
      navigate("/", { replace: true })
    } catch (error) {
      toast.error(
        error instanceof ApiError ? error.message : "Não foi possível selecionar a conta."
      )
    }
  }

  async function handleCreate() {
    if (!name.trim() || !user) return

    try {
      const account = await createAccount.mutateAsync({
        name: name.trim(),
        owner_name: user.name,
      })
      await handleSelect(account.id)
    } catch (error) {
      toast.error(
        error instanceof ApiError ? error.message : "Não foi possível criar a conta."
      )
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted/30 p-4">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Selecione uma conta</CardTitle>
          <CardDescription>
            Escolha o livro caixa que deseja acessar, {user?.name}.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {isLoading && (
            <div className="space-y-2">
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-10 w-full" />
            </div>
          )}

          {!isLoading && accounts?.length === 0 && !showCreate && (
            <p className="text-sm text-muted-foreground">
              Você ainda não tem nenhuma conta.
            </p>
          )}

          <div className="space-y-2">
            {accounts?.map((account) => (
              <div key={account.id} className="flex items-center gap-2">
                <Button
                  variant="outline"
                  className="flex-1 justify-start"
                  disabled={selectAccount.isPending}
                  onClick={() => handleSelect(account.id)}
                >
                  {account.name}
                  <span className="ml-auto text-xs text-muted-foreground">
                    {account.owner_name}
                  </span>
                </Button>
                <Button
                  variant="outline"
                  size="icon"
                  title="Gerenciar quem tem acesso"
                  onClick={() => setManagingAccount(account)}
                >
                  <Settings className="size-4" />
                </Button>
              </div>
            ))}
          </div>

          {showCreate ? (
            <div className="space-y-2 border-t pt-4">
              <Label htmlFor="account-name">Nome da nova conta</Label>
              <Input
                id="account-name"
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="Ex: Conta Pessoal"
              />
              <div className="flex gap-2">
                <Button
                  className="flex-1"
                  onClick={handleCreate}
                  disabled={createAccount.isPending || !name.trim()}
                >
                  Criar e entrar
                </Button>
                <Button variant="ghost" onClick={() => setShowCreate(false)}>
                  Cancelar
                </Button>
              </div>
            </div>
          ) : (
            <Button
              variant="secondary"
              className="w-full"
              onClick={() => setShowCreate(true)}
            >
              Criar nova conta
            </Button>
          )}

          <Button
            variant="ghost"
            className="w-full text-muted-foreground"
            onClick={() => logout.mutateAsync().then(() => navigate("/login"))}
          >
            Sair
          </Button>
        </CardContent>
      </Card>

      <AccountMembersDialog
        account={managingAccount}
        onOpenChange={(open) => !open && setManagingAccount(null)}
      />
    </div>
  )
}

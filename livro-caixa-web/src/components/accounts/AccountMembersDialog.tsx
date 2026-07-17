import { useState } from "react"
import { toast } from "sonner"
import { useAccountMemberMutations, useAccountMembersQuery } from "@/hooks/useAccount"
import { useUsersQuery } from "@/hooks/useUsers"
import { useMeQuery } from "@/hooks/useAuth"
import { ApiError } from "@/api/client"
import type { Account } from "@/types"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

interface AccountMembersDialogProps {
  account: Account | null
  onOpenChange: (open: boolean) => void
}

/**
 * "Convidar alguém para esta conta". Sem e-mail/token: o app roda local,
 * para um grupo pequeno e conhecido de pessoas, então basta escolher
 * quem convidar na lista de usuários já cadastrados no sistema.
 */
export function AccountMembersDialog({ account, onOpenChange }: AccountMembersDialogProps) {
  const { data: me } = useMeQuery()
  const { data: members, isLoading } = useAccountMembersQuery(account?.id)
  const { data: allUsers } = useUsersQuery()
  const { invite, remove } = useAccountMemberMutations(account?.id)
  const [selectedUserId, setSelectedUserId] = useState("")

  const memberIds = new Set(members?.map((m) => m.id))
  const invitable = allUsers?.filter((u) => !memberIds.has(u.id)) ?? []

  async function handleInvite() {
    if (!selectedUserId) return

    try {
      await invite.mutateAsync(Number(selectedUserId))
      setSelectedUserId("")
      toast.success("Usuário convidado para a conta.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível convidar.")
    }
  }

  async function handleRemove(userId: number, name: string) {
    if (!confirm(`Remover o acesso de "${name}" a esta conta?`)) return

    try {
      await remove.mutateAsync(userId)
      toast.success("Acesso removido.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível remover.")
    }
  }

  return (
    <Dialog open={!!account} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Quem tem acesso a "{account?.name}"</DialogTitle>
          <DialogDescription>
            Convide outra pessoa já cadastrada no sistema para acessar essa conta.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-2">
          {isLoading && <p className="text-sm text-muted-foreground">Carregando...</p>}
          {members?.map((member) => (
            <div
              key={member.id}
              className="flex items-center justify-between rounded-md border px-3 py-2"
            >
              <div>
                <p className="text-sm font-medium">
                  {member.name}
                  {member.id === me?.id && (
                    <span className="ml-2 text-xs text-muted-foreground">(você)</span>
                  )}
                </p>
                <p className="text-xs text-muted-foreground">{member.email}</p>
              </div>
              <div className="flex items-center gap-2">
                <Badge variant="secondary">{member.role}</Badge>
                {members.length > 1 && (
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    disabled={remove.isPending}
                    onClick={() => handleRemove(member.id, member.name)}
                  >
                    Remover
                  </Button>
                )}
              </div>
            </div>
          ))}
        </div>

        <div className="flex items-end gap-2 border-t pt-4">
          <div className="flex-1 space-y-1">
            <Select value={selectedUserId} onValueChange={setSelectedUserId}>
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Escolha um usuário para convidar" />
              </SelectTrigger>
              <SelectContent>
                {invitable.map((u) => (
                  <SelectItem key={u.id} value={String(u.id)}>
                    {u.name} — {u.email}
                  </SelectItem>
                ))}
                {invitable.length === 0 && (
                  <div className="px-2 py-1.5 text-sm text-muted-foreground">
                    Todo mundo já tem acesso.
                  </div>
                )}
              </SelectContent>
            </Select>
          </div>
          <Button onClick={handleInvite} disabled={!selectedUserId || invite.isPending}>
            Convidar
          </Button>
        </div>

        <DialogFooter>
          <Button variant="ghost" onClick={() => onOpenChange(false)}>
            Fechar
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

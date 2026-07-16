import { useState } from "react"
import { toast } from "sonner"
import { useMovementMutations } from "@/hooks/useMovements"
import { ApiError } from "@/api/client"
import { formatCurrency, formatDate } from "@/lib/format"
import type { Movement } from "@/types"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Button } from "@/components/ui/button"
import { MovementFormDialog } from "@/components/movements/MovementFormDialog"

export function MovementsTable({ movements }: { movements: Movement[] }) {
  const [editing, setEditing] = useState<Movement | null>(null)
  const { remove } = useMovementMutations()

  async function handleDelete(movement: Movement) {
    if (!confirm(`Apagar o lançamento "${movement.description}"?`)) return

    try {
      await remove.mutateAsync(movement.id)
      toast.success("Lançamento apagado.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível apagar.")
    }
  }

  return (
    <>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Data</TableHead>
            <TableHead>Descrição</TableHead>
            <TableHead>Categoria</TableHead>
            <TableHead>Livro/Folha</TableHead>
            <TableHead className="text-right">Entradas</TableHead>
            <TableHead className="text-right">Saídas</TableHead>
            <TableHead className="text-right">Saldo</TableHead>
            <TableHead />
          </TableRow>
        </TableHeader>
        <TableBody>
          {movements.map((movement) => (
            <TableRow key={movement.id}>
              <TableCell>{formatDate(movement.movement_date)}</TableCell>
              <TableCell
                className="max-w-64 truncate cursor-pointer underline-offset-2 hover:underline"
                onClick={() => setEditing(movement)}
              >
                {movement.description}
              </TableCell>
              <TableCell className="text-muted-foreground">
                {movement.category_name ?? "—"}
              </TableCell>
              <TableCell className="text-muted-foreground">
                {movement.book_number}/{movement.page_number}
              </TableCell>
              <TableCell className="text-right text-blue-600 dark:text-blue-400">
                {movement.type === "credit" ? formatCurrency(movement.amount) : ""}
              </TableCell>
              <TableCell className="text-right text-red-600 dark:text-red-400">
                {movement.type === "debit" ? formatCurrency(movement.amount) : ""}
              </TableCell>
              <TableCell className="text-right font-medium">
                {movement.running_balance !== undefined
                  ? formatCurrency(movement.running_balance)
                  : "—"}
              </TableCell>
              <TableCell>
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-muted-foreground"
                  onClick={() => handleDelete(movement)}
                >
                  Apagar
                </Button>
              </TableCell>
            </TableRow>
          ))}
          {movements.length === 0 && (
            <TableRow>
              <TableCell colSpan={8} className="text-center text-muted-foreground">
                Nenhum lançamento encontrado.
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>

      {editing && (
        <MovementFormDialog
          open={!!editing}
          onOpenChange={(open) => !open && setEditing(null)}
          movement={editing}
        />
      )}
    </>
  )
}

import { useState } from "react"
import { toast } from "sonner"
import { useRefuelingMutations, useRefuelingsQuery } from "@/hooks/useRefuelings"
import { RefuelingFormDialog } from "@/components/fleet/RefuelingFormDialog"
import { ApiError } from "@/api/client"
import { formatCurrency } from "@/lib/format"
import type { Refueling } from "@/types"
import { Button } from "@/components/ui/button"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

export function RefuelingsPage() {
  const { data, isLoading } = useRefuelingsQuery()
  const { remove } = useRefuelingMutations()
  const [editing, setEditing] = useState<Refueling | null>(null)
  const [showForm, setShowForm] = useState(false)

  async function handleDelete(refueling: Refueling) {
    if (!confirm(`Apagar o abastecimento de ${refueling.vehicle_plate}?`)) return
    try {
      await remove.mutateAsync(refueling.id)
      toast.success("Abastecimento apagado.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível apagar.")
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Abastecimentos</h1>
        <Button
          onClick={() => {
            setEditing(null)
            setShowForm(true)
          }}
        >
          Novo Abastecimento
        </Button>
      </div>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Data</TableHead>
              <TableHead>Veículo</TableHead>
              <TableHead>Fornecedor</TableHead>
              <TableHead>Produto</TableHead>
              <TableHead className="text-right">Km</TableHead>
              <TableHead className="text-right">Qtd.</TableHead>
              <TableHead className="text-right">Total</TableHead>
              <TableHead />
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && (
              <TableRow>
                <TableCell colSpan={8} className="text-center text-muted-foreground">
                  Carregando...
                </TableCell>
              </TableRow>
            )}
            {data?.data.map((refueling) => (
              <TableRow key={refueling.id}>
                <TableCell>{new Date(refueling.refueled_at).toLocaleString("pt-BR")}</TableCell>
                <TableCell
                  className="cursor-pointer font-medium hover:underline"
                  onClick={() => {
                    setEditing(refueling)
                    setShowForm(true)
                  }}
                >
                  {refueling.vehicle_plate}
                </TableCell>
                <TableCell className="text-muted-foreground">
                  {refueling.fuel_supplier_name}
                </TableCell>
                <TableCell className="text-muted-foreground">
                  {refueling.fuel_product_name}
                </TableCell>
                <TableCell className="text-right">{refueling.odometer_km}</TableCell>
                <TableCell className="text-right">{refueling.quantity} L</TableCell>
                <TableCell className="text-right font-medium">
                  {formatCurrency(refueling.total_cost)}
                </TableCell>
                <TableCell>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    onClick={() => handleDelete(refueling)}
                  >
                    Apagar
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <RefuelingFormDialog
        open={showForm}
        onOpenChange={setShowForm}
        refueling={editing ?? undefined}
      />
    </div>
  )
}

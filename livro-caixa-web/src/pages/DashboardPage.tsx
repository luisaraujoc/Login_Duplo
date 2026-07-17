import { useState } from "react"
import { useMonthlySummaryQuery, useMovementsQuery } from "@/hooks/useMovements"
import { reportsApi } from "@/api/reports"
import { BalanceSummaryCards } from "@/components/movements/BalanceSummaryCards"
import { MovementsTable } from "@/components/movements/MovementsTable"
import { MovementFormDialog } from "@/components/movements/MovementFormDialog"
import { Button } from "@/components/ui/button"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { MONTHS } from "@/lib/format"

const now = new Date()

export function DashboardPage() {
  const [month, setMonth] = useState(now.getMonth() + 1)
  const [year, setYear] = useState(now.getFullYear())
  const [showNew, setShowNew] = useState(false)

  const { data: summary } = useMonthlySummaryQuery(month, year)
  const { data: movements, isLoading } = useMovementsQuery({ month, year, per_page: 100 })

  const years = Array.from({ length: 20 }, (_, i) => now.getFullYear() - 10 + i)

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-xl font-semibold">Dashboard</h1>

        <div className="flex items-center gap-2">
          <Select value={String(month)} onValueChange={(v) => setMonth(Number(v))}>
            <SelectTrigger className="w-36">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {MONTHS.map((label, i) => (
                <SelectItem key={label} value={String(i + 1)}>
                  {label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Select value={String(year)} onValueChange={(v) => setYear(Number(v))}>
            <SelectTrigger className="w-24">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {years.map((y) => (
                <SelectItem key={y} value={String(y)}>
                  {y}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Button variant="outline" asChild>
            <a
              href={reportsApi.monthlyPdfUrl(month, year)}
              target="_blank"
              rel="noreferrer"
            >
              Exportar PDF
            </a>
          </Button>
          <Button onClick={() => setShowNew(true)}>Novo Lançamento</Button>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <BalanceSummaryCards title="Balanço Mensal" summary={summary?.month} />
        <BalanceSummaryCards title="Balanço Anual" summary={summary?.year} />
      </div>

      <div className="rounded-lg border">
        {isLoading ? (
          <p className="p-6 text-sm text-muted-foreground">Carregando...</p>
        ) : (
          <MovementsTable movements={movements?.data ?? []} />
        )}
      </div>

      <MovementFormDialog open={showNew} onOpenChange={setShowNew} />
    </div>
  )
}

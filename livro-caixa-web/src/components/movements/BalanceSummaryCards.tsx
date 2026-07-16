import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { formatCurrency } from "@/lib/format"
import type { MovementSummary } from "@/types"

export function BalanceSummaryCards({
  title,
  summary,
}: {
  title: string
  summary?: MovementSummary
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-sm text-muted-foreground">{title}</CardTitle>
      </CardHeader>
      <CardContent className="grid grid-cols-2 gap-y-3 text-sm sm:grid-cols-4">
        <div>
          <p className="text-muted-foreground">Saldo Anterior</p>
          <p className="font-medium">{formatCurrency(summary?.opening_balance ?? 0)}</p>
        </div>
        <div>
          <p className="text-muted-foreground">Entradas</p>
          <p className="font-medium text-blue-600 dark:text-blue-400">
            {formatCurrency(summary?.credits ?? 0)}
          </p>
        </div>
        <div>
          <p className="text-muted-foreground">Saídas</p>
          <p className="font-medium text-red-600 dark:text-red-400">
            {formatCurrency(summary?.debits ?? 0)}
          </p>
        </div>
        <div>
          <p className="text-muted-foreground">Saldo Atual</p>
          <p
            className={
              "font-semibold " +
              ((summary?.closing_balance ?? 0) >= 0
                ? "text-emerald-600 dark:text-emerald-400"
                : "text-red-600 dark:text-red-400")
            }
          >
            {formatCurrency(summary?.closing_balance ?? 0)}
          </p>
        </div>
      </CardContent>
    </Card>
  )
}

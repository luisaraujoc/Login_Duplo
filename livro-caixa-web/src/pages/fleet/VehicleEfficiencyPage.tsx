import { useParams } from "react-router-dom"
import {
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts"
import { useVehicleEfficiencyQuery } from "@/hooks/useVehicles"
import { useVehiclesQuery } from "@/hooks/useVehicles"
import { formatCurrency, formatDate } from "@/lib/format"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

export function VehicleEfficiencyPage() {
  const { id } = useParams<{ id: string }>()
  const vehicleId = Number(id)

  const { data: vehicles } = useVehiclesQuery()
  const { data, isLoading } = useVehicleEfficiencyQuery(vehicleId)

  const vehicle = vehicles?.find((v) => v.id === vehicleId)

  const chartData = data?.efficiency
    .filter((e) => e.km_per_liter !== null)
    .map((e) => {
      const refueling = data.refuelings.find((r) => r.id === e.refueling_id)
      return {
        date: refueling ? formatDate(refueling.refueled_at) : "",
        kmPerLiter: e.km_per_liter,
        target: refueling?.target_efficiency_km_per_liter,
      }
    })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold">
          Eficiência — {vehicle?.plate ?? "..."}
        </h1>
        <p className="text-sm text-muted-foreground">
          {vehicle?.brand} {vehicle?.model}
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-sm text-muted-foreground">
            Km/l ao longo do tempo
          </CardTitle>
        </CardHeader>
        <CardContent className="h-64">
          {chartData && chartData.length > 0 ? (
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                <XAxis dataKey="date" fontSize={12} />
                <YAxis fontSize={12} />
                <Tooltip />
                <Line
                  type="monotone"
                  dataKey="kmPerLiter"
                  name="Km/l"
                  stroke="var(--color-chart-1, #6366f1)"
                  strokeWidth={2}
                />
                <Line
                  type="monotone"
                  dataKey="target"
                  name="Meta"
                  stroke="var(--color-chart-3, #94a3b8)"
                  strokeDasharray="4 4"
                />
              </LineChart>
            </ResponsiveContainer>
          ) : (
            <p className="flex h-full items-center justify-center text-sm text-muted-foreground">
              {isLoading ? "Carregando..." : "Sem dados suficientes ainda (mínimo de 2 abastecimentos)."}
            </p>
          )}
        </CardContent>
      </Card>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Data</TableHead>
              <TableHead className="text-right">Km</TableHead>
              <TableHead className="text-right">Km rodado</TableHead>
              <TableHead className="text-right">Km/l</TableHead>
              <TableHead className="text-right">R$/km</TableHead>
              <TableHead className="text-right">Total</TableHead>
              <TableHead>Meta</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {data?.refuelings.map((refueling) => {
              const efficiency = data.efficiency.find((e) => e.refueling_id === refueling.id)
              return (
                <TableRow key={refueling.id}>
                  <TableCell>{formatDate(refueling.refueled_at)}</TableCell>
                  <TableCell className="text-right">{refueling.odometer_km}</TableCell>
                  <TableCell className="text-right text-muted-foreground">
                    {efficiency?.km_traveled ?? "—"}
                  </TableCell>
                  <TableCell className="text-right">
                    {efficiency?.km_per_liter ?? "—"}
                  </TableCell>
                  <TableCell className="text-right text-muted-foreground">
                    {efficiency?.cost_per_km ? formatCurrency(efficiency.cost_per_km) : "—"}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(refueling.total_cost)}
                  </TableCell>
                  <TableCell>
                    {efficiency?.meets_target === null || efficiency?.meets_target === undefined ? (
                      "—"
                    ) : (
                      <Badge variant={efficiency.meets_target ? "default" : "destructive"}>
                        {efficiency.meets_target ? "Dentro da meta" : "Abaixo da meta"}
                      </Badge>
                    )}
                  </TableCell>
                </TableRow>
              )
            })}
          </TableBody>
        </Table>
      </div>
    </div>
  )
}

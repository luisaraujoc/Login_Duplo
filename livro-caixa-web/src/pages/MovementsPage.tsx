import { useState } from "react"
import { useBooksQuery } from "@/hooks/useBooks"
import { useMovementsQuery, useRangeSummaryQuery } from "@/hooks/useMovements"
import { BalanceSummaryCards } from "@/components/movements/BalanceSummaryCards"
import { MovementsTable } from "@/components/movements/MovementsTable"
import { MovementFormDialog } from "@/components/movements/MovementFormDialog"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

function toISODate(date: Date) {
  return date.toISOString().slice(0, 10)
}

export function MovementsPage() {
  const [showNew, setShowNew] = useState(false)

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Lançamentos</h1>
        <Button onClick={() => setShowNew(true)}>Novo Lançamento</Button>
      </div>

      <Tabs defaultValue="period">
        <TabsList>
          <TabsTrigger value="period">Filtrar por Data</TabsTrigger>
          <TabsTrigger value="book">Filtrar por Livro/Folha</TabsTrigger>
        </TabsList>
        <TabsContent value="period" className="mt-4">
          <PeriodFilter />
        </TabsContent>
        <TabsContent value="book" className="mt-4">
          <BookPageFilter />
        </TabsContent>
      </Tabs>

      <MovementFormDialog open={showNew} onOpenChange={setShowNew} />
    </div>
  )
}

function PeriodFilter() {
  const today = new Date()
  const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)

  const [dateFrom, setDateFrom] = useState(toISODate(firstOfMonth))
  const [dateTo, setDateTo] = useState(toISODate(today))
  const [q, setQ] = useState("")

  const { data: summary } = useRangeSummaryQuery(dateFrom, dateTo)
  const { data: movements, isLoading } = useMovementsQuery({
    date_from: dateFrom,
    date_to: dateTo,
    q: q || undefined,
    per_page: 100,
  })

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end gap-4 rounded-lg border p-4">
        <div className="space-y-1">
          <Label htmlFor="date-from">De</Label>
          <Input
            id="date-from"
            type="date"
            value={dateFrom}
            onChange={(e) => setDateFrom(e.target.value)}
          />
        </div>
        <div className="space-y-1">
          <Label htmlFor="date-to">Até</Label>
          <Input
            id="date-to"
            type="date"
            value={dateTo}
            onChange={(e) => setDateTo(e.target.value)}
          />
        </div>
        <div className="flex-1 space-y-1">
          <Label htmlFor="q">Buscar na descrição</Label>
          <Input id="q" value={q} onChange={(e) => setQ(e.target.value)} />
        </div>
      </div>

      <BalanceSummaryCards title="Balanço do Período" summary={summary} />

      <div className="rounded-lg border">
        {isLoading ? (
          <p className="p-6 text-sm text-muted-foreground">Carregando...</p>
        ) : (
          <MovementsTable movements={movements?.data ?? []} />
        )}
      </div>
    </div>
  )
}

function BookPageFilter() {
  const { data: books } = useBooksQuery()
  const [bookId, setBookId] = useState<number | undefined>(undefined)
  const [pageFrom, setPageFrom] = useState("1")
  const [pageTo, setPageTo] = useState("999")
  const [q, setQ] = useState("")

  const { data: movements, isLoading } = useMovementsQuery({
    book_id: bookId,
    page_from: Number(pageFrom) || undefined,
    page_to: Number(pageTo) || undefined,
    q: q || undefined,
    per_page: 100,
  })

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end gap-4 rounded-lg border p-4">
        <div className="space-y-1">
          <Label>Livro</Label>
          <Select
            value={bookId ? String(bookId) : ""}
            onValueChange={(v) => setBookId(Number(v))}
          >
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Selecione" />
            </SelectTrigger>
            <SelectContent>
              {books?.map((book) => (
                <SelectItem key={book.id} value={String(book.id)}>
                  {book.number} {book.label ? `— ${book.label}` : ""}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <Label htmlFor="page-from">Folha inicial</Label>
          <Input
            id="page-from"
            type="number"
            min={1}
            value={pageFrom}
            onChange={(e) => setPageFrom(e.target.value)}
            className="w-28"
          />
        </div>
        <div className="space-y-1">
          <Label htmlFor="page-to">Folha final</Label>
          <Input
            id="page-to"
            type="number"
            min={1}
            value={pageTo}
            onChange={(e) => setPageTo(e.target.value)}
            className="w-28"
          />
        </div>
        <div className="flex-1 space-y-1">
          <Label htmlFor="q-book">Buscar na descrição</Label>
          <Input id="q-book" value={q} onChange={(e) => setQ(e.target.value)} />
        </div>
      </div>

      <div className="rounded-lg border">
        {!bookId ? (
          <p className="p-6 text-sm text-muted-foreground">Selecione um livro.</p>
        ) : isLoading ? (
          <p className="p-6 text-sm text-muted-foreground">Carregando...</p>
        ) : (
          <MovementsTable movements={movements?.data ?? []} />
        )}
      </div>
    </div>
  )
}

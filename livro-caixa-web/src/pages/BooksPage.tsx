import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useBookMutations, useBooksQuery } from "@/hooks/useBooks"
import { ApiError } from "@/api/client"
import type { Book } from "@/types"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"

const schema = z.object({
  number: z.coerce.number().min(1, "Informe o número do livro"),
  label: z.string().nullable(),
})

type FormValues = z.infer<typeof schema>

export function BooksPage() {
  const { data: books, isLoading } = useBooksQuery()
  const { create, update, remove } = useBookMutations()
  const [editing, setEditing] = useState<Book | null>(null)
  const [showForm, setShowForm] = useState(false)

  const form = useForm<z.input<typeof schema>, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { number: (books?.length ?? 0) + 1, label: "" },
  })

  function openCreate() {
    setEditing(null)
    form.reset({ number: (books?.length ?? 0) + 1, label: "" })
    setShowForm(true)
  }

  function openEdit(book: Book) {
    setEditing(book)
    form.reset({ number: book.number, label: book.label ?? "" })
    setShowForm(true)
  }

  async function onSubmit(values: FormValues) {
    try {
      if (editing) {
        await update.mutateAsync({ id: editing.id, payload: values })
        toast.success("Livro atualizado.")
      } else {
        await create.mutateAsync(values)
        toast.success("Livro criado.")
      }
      setShowForm(false)
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  async function handleDelete(book: Book) {
    if (!confirm(`Apagar o livro nº ${book.number}?`)) return

    try {
      await remove.mutateAsync(book.id)
      toast.success("Livro apagado.")
    } catch (error) {
      toast.error(
        error instanceof ApiError
          ? error.message
          : "Não foi possível apagar (verifique se há lançamentos neste livro)."
      )
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Livros</h1>
        <Button onClick={openCreate}>Novo Livro</Button>
      </div>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Número</TableHead>
              <TableHead>Etiqueta</TableHead>
              <TableHead />
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && (
              <TableRow>
                <TableCell colSpan={3} className="text-center text-muted-foreground">
                  Carregando...
                </TableCell>
              </TableRow>
            )}
            {books?.map((book) => (
              <TableRow key={book.id}>
                <TableCell
                  className="cursor-pointer hover:underline"
                  onClick={() => openEdit(book)}
                >
                  Livro {book.number}
                </TableCell>
                <TableCell className="text-muted-foreground">{book.label ?? "—"}</TableCell>
                <TableCell>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    onClick={() => handleDelete(book)}
                  >
                    Apagar
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <Dialog open={showForm} onOpenChange={setShowForm}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editing ? "Editar livro" : "Novo livro"}</DialogTitle>
          </DialogHeader>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <FormField
                control={form.control}
                name="number"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Número</FormLabel>
                    <FormControl>
                      <Input type="number" min={1} {...field} value={field.value as number} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="label"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Etiqueta (opcional)</FormLabel>
                    <FormControl>
                      <Input {...field} value={field.value ?? ""} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <DialogFooter>
                <Button type="button" variant="ghost" onClick={() => setShowForm(false)}>
                  Cancelar
                </Button>
                <Button type="submit" disabled={create.isPending || update.isPending}>
                  Salvar
                </Button>
              </DialogFooter>
            </form>
          </Form>
        </DialogContent>
      </Dialog>
    </div>
  )
}

import { useEffect } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useBooksQuery } from "@/hooks/useBooks"
import { useCategoriesQuery } from "@/hooks/useCategories"
import { useMovementMutations } from "@/hooks/useMovements"
import { ApiError } from "@/api/client"
import type { Movement } from "@/types"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

const schema = z.object({
  type: z.enum(["credit", "debit"]),
  book_id: z.coerce.number().min(1, "Selecione um livro"),
  category_id: z.coerce.number().nullable(),
  page_number: z.coerce.number().min(1, "Informe a folha"),
  description: z.string().min(1, "Informe a descrição"),
  amount: z.coerce.number().positive("Informe um valor maior que zero"),
  movement_date: z.string().min(1, "Informe a data"),
})

type FormValues = z.infer<typeof schema>

interface MovementFormDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  movement?: Movement
  defaultBookId?: number
}

export function MovementFormDialog({
  open,
  onOpenChange,
  movement,
  defaultBookId,
}: MovementFormDialogProps) {
  const { data: books } = useBooksQuery()
  const { data: categories } = useCategoriesQuery()
  const { create, update } = useMovementMutations()

  const form = useForm<z.input<typeof schema>, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      type: "debit",
      book_id: defaultBookId ?? 0,
      category_id: null,
      page_number: 1,
      description: "",
      amount: 0,
      movement_date: new Date().toISOString().slice(0, 10),
    },
  })

  useEffect(() => {
    if (!open) return

    form.reset(
      movement
        ? {
            type: movement.type,
            book_id: movement.book_id,
            category_id: movement.category_id,
            page_number: movement.page_number,
            description: movement.description,
            amount: movement.amount,
            movement_date: movement.movement_date,
          }
        : {
            type: "debit",
            book_id: defaultBookId ?? books?.[0]?.id ?? 0,
            category_id: null,
            page_number: 1,
            description: "",
            amount: 0,
            movement_date: new Date().toISOString().slice(0, 10),
          }
    )
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, movement])

  const type = form.watch("type")
  const filteredCategories = categories?.filter((c) => c.type === type)

  async function onSubmit(values: FormValues) {
    try {
      if (movement) {
        await update.mutateAsync({ id: movement.id, payload: values })
        toast.success("Lançamento atualizado.")
      } else {
        await create.mutateAsync(values)
        toast.success("Lançamento adicionado.")
      }
      onOpenChange(false)
    } catch (error) {
      toast.error(
        error instanceof ApiError ? error.message : "Não foi possível salvar."
      )
    }
  }

  const isPending = create.isPending || update.isPending

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{movement ? "Editar lançamento" : "Novo lançamento"}</DialogTitle>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="type"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Tipo</FormLabel>
                  <Select value={field.value} onValueChange={field.onChange}>
                    <FormControl>
                      <SelectTrigger className="w-full">
                        <SelectValue />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="credit">Entrada</SelectItem>
                      <SelectItem value="debit">Saída</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="book_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Livro</FormLabel>
                    <Select
                      value={String(field.value)}
                      onValueChange={(v) => field.onChange(Number(v))}
                    >
                      <FormControl>
                        <SelectTrigger className="w-full">
                          <SelectValue />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {books?.map((book) => (
                          <SelectItem key={book.id} value={String(book.id)}>
                            {book.number} {book.label ? `— ${book.label}` : ""}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="page_number"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Folha</FormLabel>
                    <FormControl>
                      <Input type="number" min={1} {...field} value={field.value as number} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <FormField
              control={form.control}
              name="category_id"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Categoria</FormLabel>
                  <Select
                    value={field.value ? String(field.value) : ""}
                    onValueChange={(v) => field.onChange(Number(v))}
                  >
                    <FormControl>
                      <SelectTrigger className="w-full">
                        <SelectValue placeholder="Selecione" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {filteredCategories?.map((category) => (
                        <SelectItem key={category.id} value={String(category.id)}>
                          {category.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="description"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Descrição</FormLabel>
                  <FormControl>
                    <Input {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="amount"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Valor</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.01"
                        min={0}
                        {...field}
                        value={field.value as number}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="movement_date"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Data</FormLabel>
                    <FormControl>
                      <Input type="date" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <DialogFooter>
              <Button type="button" variant="ghost" onClick={() => onOpenChange(false)}>
                Cancelar
              </Button>
              <Button type="submit" disabled={isPending}>
                {isPending ? "Salvando..." : "Salvar"}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  )
}

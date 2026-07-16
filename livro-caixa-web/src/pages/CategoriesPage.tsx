import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useCategoriesQuery, useCategoryMutations } from "@/hooks/useCategories"
import { ApiError } from "@/api/client"
import type { Category } from "@/types"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

const schema = z.object({
  name: z.string().min(1, "Informe o nome"),
  type: z.enum(["credit", "debit"]),
})

type FormValues = z.infer<typeof schema>

export function CategoriesPage() {
  const { data: categories, isLoading } = useCategoriesQuery()
  const { create, update, remove } = useCategoryMutations()
  const [editing, setEditing] = useState<Category | null>(null)
  const [showForm, setShowForm] = useState(false)

  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { name: "", type: "debit" },
  })

  function openCreate() {
    setEditing(null)
    form.reset({ name: "", type: "debit" })
    setShowForm(true)
  }

  function openEdit(category: Category) {
    setEditing(category)
    form.reset({ name: category.name, type: category.type })
    setShowForm(true)
  }

  async function onSubmit(values: FormValues) {
    try {
      if (editing) {
        await update.mutateAsync({ id: editing.id, payload: values })
        toast.success("Categoria atualizada.")
      } else {
        await create.mutateAsync(values)
        toast.success("Categoria criada.")
      }
      setShowForm(false)
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  async function handleDelete(category: Category) {
    if (!confirm(`Apagar a categoria "${category.name}"?`)) return

    try {
      await remove.mutateAsync(category.id)
      toast.success("Categoria apagada.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível apagar.")
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Categorias</h1>
        <Button onClick={openCreate}>Nova Categoria</Button>
      </div>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nome</TableHead>
              <TableHead>Tipo</TableHead>
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
            {categories?.map((category) => (
              <TableRow key={category.id}>
                <TableCell
                  className="cursor-pointer hover:underline"
                  onClick={() => openEdit(category)}
                >
                  {category.name}
                </TableCell>
                <TableCell>
                  <Badge variant={category.type === "credit" ? "default" : "destructive"}>
                    {category.type === "credit" ? "Crédito" : "Débito"}
                  </Badge>
                </TableCell>
                <TableCell>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    onClick={() => handleDelete(category)}
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
            <DialogTitle>{editing ? "Editar categoria" : "Nova categoria"}</DialogTitle>
          </DialogHeader>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <FormField
                control={form.control}
                name="name"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Nome</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
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
                        <SelectItem value="credit">Crédito</SelectItem>
                        <SelectItem value="debit">Débito</SelectItem>
                      </SelectContent>
                    </Select>
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

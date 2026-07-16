import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useFuelProductMutations, useFuelProductsQuery } from "@/hooks/useFuelProducts"
import { ApiError } from "@/api/client"
import type { FuelProduct } from "@/types"
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
  code: z.string().min(1, "Informe o código"),
  name: z.string().min(1, "Informe o nome"),
  unit: z.string().min(1, "Informe a unidade"),
})

type FormValues = z.infer<typeof schema>

const emptyValues: FormValues = { code: "", name: "", unit: "L" }

export function FuelProductsPage() {
  const { data: products, isLoading } = useFuelProductsQuery()
  const { create, update, remove } = useFuelProductMutations()
  const [editing, setEditing] = useState<FuelProduct | null>(null)
  const [showForm, setShowForm] = useState(false)

  const form = useForm<FormValues>({ resolver: zodResolver(schema), defaultValues: emptyValues })

  function openCreate() {
    setEditing(null)
    form.reset(emptyValues)
    setShowForm(true)
  }

  function openEdit(product: FuelProduct) {
    setEditing(product)
    form.reset({ ...product })
    setShowForm(true)
  }

  async function onSubmit(values: FormValues) {
    try {
      if (editing) {
        await update.mutateAsync({ id: editing.id, payload: values })
        toast.success("Produto atualizado.")
      } else {
        await create.mutateAsync(values)
        toast.success("Produto cadastrado.")
      }
      setShowForm(false)
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  async function handleDelete(product: FuelProduct) {
    if (!confirm(`Apagar o produto "${product.name}"?`)) return
    try {
      await remove.mutateAsync(product.id)
      toast.success("Produto apagado.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível apagar.")
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Produtos</h1>
        <Button onClick={openCreate}>Novo Produto</Button>
      </div>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Código</TableHead>
              <TableHead>Nome</TableHead>
              <TableHead>Unidade</TableHead>
              <TableHead />
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && (
              <TableRow>
                <TableCell colSpan={4} className="text-center text-muted-foreground">
                  Carregando...
                </TableCell>
              </TableRow>
            )}
            {products?.map((product) => (
              <TableRow key={product.id}>
                <TableCell className="text-muted-foreground">{product.code}</TableCell>
                <TableCell
                  className="cursor-pointer hover:underline"
                  onClick={() => openEdit(product)}
                >
                  {product.name}
                </TableCell>
                <TableCell className="text-muted-foreground">{product.unit}</TableCell>
                <TableCell>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    onClick={() => handleDelete(product)}
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
            <DialogTitle>{editing ? "Editar produto" : "Novo produto"}</DialogTitle>
          </DialogHeader>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <FormField
                control={form.control}
                name="code"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Código</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
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
                name="unit"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Unidade</FormLabel>
                    <FormControl>
                      <Input {...field} />
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

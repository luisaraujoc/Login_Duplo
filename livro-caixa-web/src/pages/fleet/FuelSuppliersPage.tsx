import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useFuelSupplierMutations, useFuelSuppliersQuery } from "@/hooks/useFuelSuppliers"
import { ApiError } from "@/api/client"
import type { FuelSupplier } from "@/types"
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
  company_name: z.string().min(1, "Informe a razão social"),
  address: z.string().min(1, "Informe o endereço"),
  neighborhood: z.string().min(1, "Informe o bairro"),
  city: z.string().min(1, "Informe a cidade"),
  state: z.string().min(1, "Informe o estado"),
  zip_code: z.string().min(1, "Informe o CEP"),
  phone: z.string().min(1, "Informe o telefone"),
  cnpj: z.string().min(1, "Informe o CNPJ"),
})

type FormValues = z.infer<typeof schema>

const emptyValues: FormValues = {
  company_name: "",
  address: "",
  neighborhood: "",
  city: "",
  state: "",
  zip_code: "",
  phone: "",
  cnpj: "",
}

const fields: Array<{ name: keyof FormValues; label: string }> = [
  { name: "company_name", label: "Razão Social" },
  { name: "cnpj", label: "CNPJ" },
  { name: "address", label: "Endereço" },
  { name: "neighborhood", label: "Bairro" },
  { name: "city", label: "Cidade" },
  { name: "state", label: "Estado" },
  { name: "zip_code", label: "CEP" },
  { name: "phone", label: "Telefone" },
]

export function FuelSuppliersPage() {
  const { data: suppliers, isLoading } = useFuelSuppliersQuery()
  const { create, update, remove } = useFuelSupplierMutations()
  const [editing, setEditing] = useState<FuelSupplier | null>(null)
  const [showForm, setShowForm] = useState(false)

  const form = useForm<FormValues>({ resolver: zodResolver(schema), defaultValues: emptyValues })

  function openCreate() {
    setEditing(null)
    form.reset(emptyValues)
    setShowForm(true)
  }

  function openEdit(supplier: FuelSupplier) {
    setEditing(supplier)
    form.reset({ ...supplier })
    setShowForm(true)
  }

  async function onSubmit(values: FormValues) {
    try {
      if (editing) {
        await update.mutateAsync({ id: editing.id, payload: values })
        toast.success("Fornecedor atualizado.")
      } else {
        await create.mutateAsync(values)
        toast.success("Fornecedor cadastrado.")
      }
      setShowForm(false)
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  async function handleDelete(supplier: FuelSupplier) {
    if (!confirm(`Apagar o fornecedor "${supplier.company_name}"?`)) return
    try {
      await remove.mutateAsync(supplier.id)
      toast.success("Fornecedor apagado.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível apagar.")
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Fornecedores</h1>
        <Button onClick={openCreate}>Novo Fornecedor</Button>
      </div>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Razão Social</TableHead>
              <TableHead>Cidade/UF</TableHead>
              <TableHead>Telefone</TableHead>
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
            {suppliers?.map((supplier) => (
              <TableRow key={supplier.id}>
                <TableCell
                  className="cursor-pointer hover:underline"
                  onClick={() => openEdit(supplier)}
                >
                  {supplier.company_name}
                </TableCell>
                <TableCell className="text-muted-foreground">
                  {supplier.city}/{supplier.state}
                </TableCell>
                <TableCell className="text-muted-foreground">{supplier.phone}</TableCell>
                <TableCell>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    onClick={() => handleDelete(supplier)}
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
            <DialogTitle>{editing ? "Editar fornecedor" : "Novo fornecedor"}</DialogTitle>
          </DialogHeader>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="grid grid-cols-2 gap-4">
              {fields.map(({ name, label }) => (
                <FormField
                  key={name}
                  control={form.control}
                  name={name}
                  render={({ field }) => (
                    <FormItem className={name === "company_name" ? "col-span-2" : ""}>
                      <FormLabel>{label}</FormLabel>
                      <FormControl>
                        <Input {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              ))}
              <DialogFooter className="col-span-2">
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

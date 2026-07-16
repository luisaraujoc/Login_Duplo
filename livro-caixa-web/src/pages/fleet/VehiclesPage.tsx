import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { Link } from "react-router-dom"
import { useVehicleMutations, useVehiclesQuery } from "@/hooks/useVehicles"
import { ApiError } from "@/api/client"
import type { Vehicle } from "@/types"
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
  plate: z.string().min(1, "Informe a placa"),
  brand: z.string().min(1, "Informe a marca"),
  model: z.string().min(1, "Informe o modelo"),
  manufacture_year: z.coerce.number().min(1900).max(2100),
  model_year: z.coerce.number().min(1900).max(2100),
  renavam: z.string().nullable(),
  chassis: z.string().nullable(),
})

type FormValues = z.infer<typeof schema>

const emptyValues: FormValues = {
  plate: "",
  brand: "",
  model: "",
  manufacture_year: new Date().getFullYear(),
  model_year: new Date().getFullYear(),
  renavam: "",
  chassis: "",
}

export function VehiclesPage() {
  const { data: vehicles, isLoading } = useVehiclesQuery()
  const { create, update, remove } = useVehicleMutations()
  const [editing, setEditing] = useState<Vehicle | null>(null)
  const [showForm, setShowForm] = useState(false)

  const form = useForm<z.input<typeof schema>, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: emptyValues,
  })

  function openCreate() {
    setEditing(null)
    form.reset(emptyValues)
    setShowForm(true)
  }

  function openEdit(vehicle: Vehicle) {
    setEditing(vehicle)
    form.reset({ ...vehicle })
    setShowForm(true)
  }

  async function onSubmit(values: FormValues) {
    try {
      if (editing) {
        await update.mutateAsync({ id: editing.id, payload: values })
        toast.success("Veículo atualizado.")
      } else {
        await create.mutateAsync(values)
        toast.success("Veículo cadastrado.")
      }
      setShowForm(false)
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  async function handleDelete(vehicle: Vehicle) {
    if (!confirm(`Apagar o veículo ${vehicle.plate}?`)) return
    try {
      await remove.mutateAsync(vehicle.id)
      toast.success("Veículo apagado.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível apagar.")
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Veículos</h1>
        <Button onClick={openCreate}>Novo Veículo</Button>
      </div>

      <div className="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Placa</TableHead>
              <TableHead>Marca/Modelo</TableHead>
              <TableHead>Ano</TableHead>
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
            {vehicles?.map((vehicle) => (
              <TableRow key={vehicle.id}>
                <TableCell>
                  <Link
                    to={`/fleet/vehicles/${vehicle.id}`}
                    className="font-medium hover:underline"
                  >
                    {vehicle.plate}
                  </Link>
                </TableCell>
                <TableCell
                  className="cursor-pointer text-muted-foreground hover:underline"
                  onClick={() => openEdit(vehicle)}
                >
                  {vehicle.brand} {vehicle.model}
                </TableCell>
                <TableCell className="text-muted-foreground">
                  {vehicle.manufacture_year}/{vehicle.model_year}
                </TableCell>
                <TableCell>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground"
                    onClick={() => handleDelete(vehicle)}
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
            <DialogTitle>{editing ? "Editar veículo" : "Novo veículo"}</DialogTitle>
          </DialogHeader>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <FormField
                  control={form.control}
                  name="plate"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Placa</FormLabel>
                      <FormControl>
                        <Input {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="renavam"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Renavam</FormLabel>
                      <FormControl>
                        <Input {...field} value={field.value ?? ""} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <FormField
                  control={form.control}
                  name="brand"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Marca</FormLabel>
                      <FormControl>
                        <Input {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="model"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Modelo</FormLabel>
                      <FormControl>
                        <Input {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <FormField
                  control={form.control}
                  name="manufacture_year"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Ano Fabricação</FormLabel>
                      <FormControl>
                        <Input type="number" {...field} value={field.value as number} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="model_year"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Ano Modelo</FormLabel>
                      <FormControl>
                        <Input type="number" {...field} value={field.value as number} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
              <FormField
                control={form.control}
                name="chassis"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Chassi</FormLabel>
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

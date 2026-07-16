import { useEffect } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useVehiclesQuery } from "@/hooks/useVehicles"
import { useFuelSuppliersQuery } from "@/hooks/useFuelSuppliers"
import { useFuelProductsQuery } from "@/hooks/useFuelProducts"
import { useRefuelingMutations } from "@/hooks/useRefuelings"
import { nfeLinksApi } from "@/api/resources"
import { ApiError } from "@/api/client"
import type { Refueling } from "@/types"
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
  vehicle_id: z.coerce.number().min(1, "Selecione o veículo"),
  fuel_supplier_id: z.coerce.number().min(1, "Selecione o fornecedor"),
  fuel_product_id: z.coerce.number().min(1, "Selecione o produto"),
  invoice_number: z.string().min(1, "Informe o número da nota"),
  access_key: z.string().nullable(),
  nfe_url: z.string().url("Informe uma URL válida").or(z.literal("")).nullable(),
  refueled_at: z.string().min(1, "Informe a data/hora"),
  odometer_km: z.coerce.number().min(0, "Informe o km"),
  quantity: z.coerce.number().positive("Informe a quantidade"),
  unit_price: z.coerce.number().positive("Informe o valor unitário"),
  notes: z.string().nullable(),
})

type FormValues = z.infer<typeof schema>

function emptyValues(defaultVehicleId?: number): FormValues {
  return {
    vehicle_id: defaultVehicleId ?? 0,
    fuel_supplier_id: 0,
    fuel_product_id: 0,
    invoice_number: "",
    access_key: "",
    nfe_url: "",
    refueled_at: new Date().toISOString().slice(0, 16),
    odometer_km: 0,
    quantity: 0,
    unit_price: 0,
    notes: "",
  }
}

interface RefuelingFormDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  refueling?: Refueling
  defaultVehicleId?: number
}

export function RefuelingFormDialog({
  open,
  onOpenChange,
  refueling,
  defaultVehicleId,
}: RefuelingFormDialogProps) {
  const { data: vehicles } = useVehiclesQuery()
  const { data: suppliers } = useFuelSuppliersQuery()
  const { data: products } = useFuelProductsQuery()
  const { create, update } = useRefuelingMutations()

  const form = useForm<z.input<typeof schema>, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: emptyValues(defaultVehicleId),
  })

  useEffect(() => {
    if (!open) return

    form.reset(
      refueling
        ? {
            vehicle_id: refueling.vehicle_id,
            fuel_supplier_id: refueling.fuel_supplier_id,
            fuel_product_id: refueling.fuel_product_id,
            invoice_number: refueling.invoice_number,
            access_key: refueling.access_key,
            nfe_url: refueling.nfe_link_url ?? "",
            refueled_at: refueling.refueled_at.slice(0, 16),
            odometer_km: refueling.odometer_km,
            quantity: refueling.quantity,
            unit_price: refueling.unit_price,
            notes: refueling.notes,
          }
        : emptyValues(defaultVehicleId)
    )
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, refueling])

  async function onSubmit(values: FormValues) {
    try {
      let nfeLinkId = refueling?.nfe_link_id ?? null

      if (values.nfe_url) {
        const link = await nfeLinksApi.create({ url: values.nfe_url })
        nfeLinkId = link.id
      }

      const payload = {
        vehicle_id: values.vehicle_id,
        fuel_supplier_id: values.fuel_supplier_id,
        fuel_product_id: values.fuel_product_id,
        nfe_link_id: nfeLinkId,
        invoice_number: values.invoice_number,
        access_key: values.access_key || null,
        refueled_at: values.refueled_at,
        odometer_km: values.odometer_km,
        quantity: values.quantity,
        unit_price: values.unit_price,
        notes: values.notes || null,
      }

      if (refueling) {
        await update.mutateAsync({ id: refueling.id, payload })
        toast.success("Abastecimento atualizado.")
      } else {
        await create.mutateAsync(payload)
        toast.success("Abastecimento registrado.")
      }
      onOpenChange(false)
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  const isPending = create.isPending || update.isPending

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{refueling ? "Editar abastecimento" : "Novo abastecimento"}</DialogTitle>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="vehicle_id"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Veículo</FormLabel>
                  <Select
                    value={String(field.value)}
                    onValueChange={(v) => field.onChange(Number(v))}
                  >
                    <FormControl>
                      <SelectTrigger className="w-full">
                        <SelectValue placeholder="Selecione" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {vehicles?.map((vehicle) => (
                        <SelectItem key={vehicle.id} value={String(vehicle.id)}>
                          {vehicle.plate} — {vehicle.brand} {vehicle.model}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="fuel_supplier_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Fornecedor</FormLabel>
                    <Select
                      value={String(field.value)}
                      onValueChange={(v) => field.onChange(Number(v))}
                    >
                      <FormControl>
                        <SelectTrigger className="w-full">
                          <SelectValue placeholder="Selecione" />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {suppliers?.map((supplier) => (
                          <SelectItem key={supplier.id} value={String(supplier.id)}>
                            {supplier.company_name}
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
                name="fuel_product_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Produto</FormLabel>
                    <Select
                      value={String(field.value)}
                      onValueChange={(v) => field.onChange(Number(v))}
                    >
                      <FormControl>
                        <SelectTrigger className="w-full">
                          <SelectValue placeholder="Selecione" />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {products?.map((product) => (
                          <SelectItem key={product.id} value={String(product.id)}>
                            {product.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="refueled_at"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Data/Hora</FormLabel>
                    <FormControl>
                      <Input type="datetime-local" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="odometer_km"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Odômetro (km)</FormLabel>
                    <FormControl>
                      <Input type="number" min={0} {...field} value={field.value as number} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="quantity"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Quantidade (L)</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.001"
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
                name="unit_price"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Valor unitário</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.0001"
                        min={0}
                        {...field}
                        value={field.value as number}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="invoice_number"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Nº da Nota</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="access_key"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Chave de Acesso</FormLabel>
                    <FormControl>
                      <Input {...field} value={field.value ?? ""} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <FormField
              control={form.control}
              name="nfe_url"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Link da NFe (opcional)</FormLabel>
                  <FormControl>
                    <Input {...field} value={field.value ?? ""} placeholder="https://..." />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="notes"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Observação</FormLabel>
                  <FormControl>
                    <Input {...field} value={field.value ?? ""} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

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

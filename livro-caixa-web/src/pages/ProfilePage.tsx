import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { useMeQuery, useUpdateMeMutation } from "@/hooks/useAuth"
import { useCurrentAccountQuery } from "@/hooks/useAccount"
import { ApiError } from "@/api/client"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"

const schema = z.object({
  name: z.string().min(1, "Informe seu nome"),
  email: z.string().email("Informe um e-mail válido"),
})

export function ProfilePage() {
  const { data: user } = useMeQuery()
  const { data: account } = useCurrentAccountQuery()
  const updateMe = useUpdateMeMutation()

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    values: { name: user?.name ?? "", email: user?.email ?? "" },
  })

  async function onSubmit(values: z.infer<typeof schema>) {
    try {
      await updateMe.mutateAsync(values)
      toast.success("Dados atualizados.")
    } catch (error) {
      toast.error(error instanceof ApiError ? error.message : "Não foi possível salvar.")
    }
  }

  return (
    <div className="max-w-lg space-y-6">
      <h1 className="text-xl font-semibold">Meus Dados</h1>

      <Card>
        <CardHeader>
          <CardTitle>Usuário</CardTitle>
          <CardDescription>Dados do seu login pessoal</CardDescription>
        </CardHeader>
        <CardContent>
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
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>E-mail</FormLabel>
                    <FormControl>
                      <Input type="email" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <Button type="submit" disabled={updateMe.isPending}>
                Salvar
              </Button>
            </form>
          </Form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Conta Ativa</CardTitle>
          <CardDescription>Livro caixa selecionado no momento</CardDescription>
        </CardHeader>
        <CardContent className="text-sm">
          <p>
            <span className="text-muted-foreground">Nome: </span>
            {account?.name}
          </p>
          <p>
            <span className="text-muted-foreground">Proprietário: </span>
            {account?.owner_name}
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

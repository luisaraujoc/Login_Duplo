import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { Link, useNavigate } from "react-router-dom"
import { toast } from "sonner"
import { useLoginMutation } from "@/hooks/useAuth"
import { ApiError } from "@/api/client"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"

const schema = z.object({
  email: z.string().email("Informe um e-mail válido"),
  password: z.string().min(1, "Informe sua senha"),
})

export function LoginPage() {
  const navigate = useNavigate()
  const login = useLoginMutation()

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: { email: "", password: "" },
  })

  async function onSubmit(values: z.infer<typeof schema>) {
    try {
      await login.mutateAsync(values)
      navigate("/select-account", { replace: true })
    } catch (error) {
      const message =
        error instanceof ApiError ? error.message : "Não foi possível entrar."
      toast.error(message)
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted/30 p-4">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Entrar</CardTitle>
          <CardDescription>Acesse sua conta do Livro Caixa</CardDescription>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <FormField
                control={form.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>E-mail</FormLabel>
                    <FormControl>
                      <Input type="email" autoComplete="email" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="password"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Senha</FormLabel>
                    <FormControl>
                      <Input
                        type="password"
                        autoComplete="current-password"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <Button type="submit" className="w-full" disabled={login.isPending}>
                {login.isPending ? "Entrando..." : "Entrar"}
              </Button>
            </form>
          </Form>
          <p className="mt-4 text-center text-sm text-muted-foreground">
            Não tem conta?{" "}
            <Link to="/register" className="font-medium text-foreground underline">
              Cadastre-se
            </Link>
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

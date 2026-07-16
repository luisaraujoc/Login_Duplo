import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { Link, useNavigate } from "react-router-dom"
import { toast } from "sonner"
import { useRegisterMutation } from "@/hooks/useAuth"
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

const schema = z
  .object({
    name: z.string().min(1, "Informe seu nome"),
    email: z.string().email("Informe um e-mail válido"),
    password: z.string().min(8, "A senha deve ter ao menos 8 caracteres"),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "As senhas não conferem",
    path: ["password_confirmation"],
  })

export function RegisterPage() {
  const navigate = useNavigate()
  const register = useRegisterMutation()

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: { name: "", email: "", password: "", password_confirmation: "" },
  })

  async function onSubmit(values: z.infer<typeof schema>) {
    try {
      await register.mutateAsync(values)
      navigate("/select-account", { replace: true })
    } catch (error) {
      const message =
        error instanceof ApiError ? error.message : "Não foi possível cadastrar."
      toast.error(message)
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted/30 p-4">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Criar conta</CardTitle>
          <CardDescription>Cadastre-se para acessar o sistema</CardDescription>
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
                      <Input autoComplete="name" {...field} />
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
                        autoComplete="new-password"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="password_confirmation"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Confirmar senha</FormLabel>
                    <FormControl>
                      <Input
                        type="password"
                        autoComplete="new-password"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <Button type="submit" className="w-full" disabled={register.isPending}>
                {register.isPending ? "Cadastrando..." : "Cadastrar"}
              </Button>
            </form>
          </Form>
          <p className="mt-4 text-center text-sm text-muted-foreground">
            Já tem conta?{" "}
            <Link to="/login" className="font-medium text-foreground underline">
              Entrar
            </Link>
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

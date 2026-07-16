import { api, ensureCsrfCookie } from "@/api/client"
import type { User } from "@/types"

export interface LoginPayload {
  email: string
  password: string
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export const authApi = {
  async login(payload: LoginPayload): Promise<User> {
    await ensureCsrfCookie()
    const { data } = await api.post("/login", payload)
    return data.data
  },
  async register(payload: RegisterPayload): Promise<User> {
    await ensureCsrfCookie()
    const { data } = await api.post("/register", payload)
    return data.data
  },
  async logout(): Promise<void> {
    await api.post("/logout")
  },
  async me(): Promise<User> {
    const { data } = await api.get("/me")
    return data.data
  },
  async updateMe(payload: { name: string; email: string }): Promise<User> {
    const { data } = await api.put("/me", payload)
    return data.data
  },
}

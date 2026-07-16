import { api } from "@/api/client"
import type { Account } from "@/types"

export interface CreateAccountPayload {
  name: string
  owner_name: string
}

export const accountsApi = {
  async list(): Promise<Account[]> {
    const { data } = await api.get("/accounts")
    return data.data
  },
  async create(payload: CreateAccountPayload): Promise<Account> {
    const { data } = await api.post("/accounts", payload)
    return data.data
  },
  async current(): Promise<Account | null> {
    const { data } = await api.get("/accounts/current")
    return data.data
  },
  async select(accountId: number): Promise<Account> {
    const { data } = await api.post(`/accounts/${accountId}/select`)
    return data.data
  },
}

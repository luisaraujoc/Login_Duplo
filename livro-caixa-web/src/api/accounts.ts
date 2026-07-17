import { api } from "@/api/client"
import type { Account, User } from "@/types"

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
  async members(accountId: number): Promise<User[]> {
    const { data } = await api.get(`/accounts/${accountId}/users`)
    return data.data
  },
  async inviteUser(accountId: number, userId: number): Promise<User[]> {
    const { data } = await api.post(`/accounts/${accountId}/users`, { user_id: userId })
    return data.data
  },
  async removeUser(accountId: number, userId: number): Promise<User[]> {
    const { data } = await api.delete(`/accounts/${accountId}/users/${userId}`)
    return data.data
  },
}

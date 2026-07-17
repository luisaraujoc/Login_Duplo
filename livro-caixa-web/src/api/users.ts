import { api } from "@/api/client"
import type { User } from "@/types"

export const usersApi = {
  async list(): Promise<User[]> {
    const { data } = await api.get("/users")
    return data.data
  },
}

import axios from "axios"

const API_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8000"

export const api = axios.create({
  baseURL: `${API_URL}/api`,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: "application/json",
  },
})

/**
 * Sanctum's SPA auth is cookie/session based: a CSRF cookie must be fetched
 * once (outside /api, at the app root) before any state-changing request.
 * axios then attaches it automatically as X-XSRF-TOKEN (withXSRFToken above).
 */
export async function ensureCsrfCookie() {
  await axios.get(`${API_URL}/sanctum/csrf-cookie`, { withCredentials: true })
}

export class ApiError extends Error {
  status: number
  errors?: Record<string, string[]>

  constructor(status: number, message: string, errors?: Record<string, string[]>) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (axios.isAxiosError(error) && error.response) {
      const { status, data } = error.response
      throw new ApiError(
        status,
        data?.message ?? "Erro inesperado.",
        data?.errors
      )
    }

    throw error
  }
)

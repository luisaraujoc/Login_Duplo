import { API_URL } from "@/api/client"

/**
 * These aren't fetched via axios — they're opened directly as a browser
 * navigation (window.open), so the existing session cookie goes along for
 * free and the PDF opens in its own tab. No CSRF token needed either: it's
 * a GET request, and Laravel only checks CSRF on state-changing methods.
 */
export const reportsApi = {
  monthlyPdfUrl(month: number, year: number): string {
    return `${API_URL}/api/reports/monthly-pdf?${new URLSearchParams({
      month: String(month),
      year: String(year),
    })}`
  },
  periodPdfUrl(dateFrom: string, dateTo: string, q?: string): string {
    const params = new URLSearchParams({ date_from: dateFrom, date_to: dateTo })
    if (q) params.set("q", q)

    return `${API_URL}/api/reports/period-pdf?${params}`
  },
}

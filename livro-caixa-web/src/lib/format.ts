const currencyFormatter = new Intl.NumberFormat("pt-BR", {
  style: "currency",
  currency: "BRL",
})

export function formatCurrency(value: number): string {
  return currencyFormatter.format(value)
}

export function formatDate(value: string): string {
  const [year, month, day] = value.slice(0, 10).split("-")
  return `${day}/${month}/${year}`
}

export const MONTHS = [
  "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
  "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro",
]

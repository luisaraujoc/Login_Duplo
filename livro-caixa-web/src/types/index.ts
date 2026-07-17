export type CashFlowType = "credit" | "debit"

export interface User {
  id: number
  name: string
  email: string
  photo_path: string | null
  /** Present only when returned inside an account's member list. */
  role?: string
}

export interface Account {
  id: number
  name: string
  owner_name: string
  role?: string
}

export interface Book {
  id: number
  number: number
  label: string | null
}

export interface Category {
  id: number
  name: string
  type: CashFlowType
}

export interface Movement {
  id: number
  book_id: number
  book_number?: number
  category_id: number | null
  category_name?: string | null
  page_number: number
  type: CashFlowType
  description: string
  amount: number
  running_balance?: number
  movement_date: string
  created_at: string
}

export interface MovementSummary {
  opening_balance: number
  credits: number
  debits: number
  balance: number
  closing_balance: number
}

export interface DashboardSummary {
  month: MovementSummary
  year: MovementSummary
}

export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
}

export interface Vehicle {
  id: number
  plate: string
  brand: string
  model: string
  manufacture_year: number
  model_year: number
  renavam: string | null
  chassis: string | null
}

export interface FuelSupplier {
  id: number
  company_name: string
  address: string
  neighborhood: string
  city: string
  state: string
  zip_code: string
  phone: string
  cnpj: string
}

export interface FuelProduct {
  id: number
  code: string
  name: string
  unit: string
}

export interface NfeLink {
  id: number
  url: string
}

export interface Refueling {
  id: number
  vehicle_id: number
  vehicle_plate?: string
  fuel_supplier_id: number
  fuel_supplier_name?: string
  fuel_product_id: number
  fuel_product_name?: string
  nfe_link_id: number | null
  nfe_link_url?: string | null
  invoice_number: string
  access_key: string | null
  refueled_at: string
  odometer_km: number
  quantity: number
  unit_price: number
  total_cost: number
  notes: string | null
  target_efficiency_km_per_liter: number
}

export interface RefuelingEfficiency {
  refueling_id: number
  previous_odometer_km: number | null
  km_traveled: number | null
  km_per_liter: number | null
  cost_per_km: number | null
  liters_per_km: number | null
  meets_target: boolean | null
}

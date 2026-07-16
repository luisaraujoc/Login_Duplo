import { createResourceApi } from "@/api/resource"
import type {
  Book,
  Category,
  FuelProduct,
  FuelSupplier,
  NfeLink,
  Vehicle,
} from "@/types"

export const booksApi = createResourceApi<Book>("/books")
export const categoriesApi = createResourceApi<Category>("/categories")
export const vehiclesApi = createResourceApi<Vehicle>("/vehicles")
export const fuelSuppliersApi = createResourceApi<FuelSupplier>("/fuel-suppliers")
export const fuelProductsApi = createResourceApi<FuelProduct>("/fuel-products")
export const nfeLinksApi = createResourceApi<NfeLink>("/nfe-links")

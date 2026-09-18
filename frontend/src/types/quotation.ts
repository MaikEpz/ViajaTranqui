export interface PricingBreakdown {
  days_count: number;
  base_rate_per_day: number;
  base_amount: number;
  surcharge_percentage: number;
  surcharge_amount: number;
  total_amount: number;
  formatted?: {
    base_rate?: string;
    base_amount?: string;
    surcharge_amount?: string;
    total_amount?: string;
  };
}

export type QuotationStatus = 'Cotizado' | 'Contratado';

export interface Quotation {
  id: number;
  first_name: string;
  last_name: string;
  full_name?: string;
  identification_number: string;
  email: string;
  birth_date: string;
  destination_country: string;
  destination_country_code: string;
  destination_region: string;
  destination_flag_url: string | null;
  start_date: string;
  end_date: string;
  days_count: number;
  base_rate_per_day: number;
  base_amount: number;
  surcharge_percentage: number;
  surcharge_amount: number;
  total_amount: number;
  status: QuotationStatus;
  contracted_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface QuotationPayload {
  first_name: string;
  last_name: string;
  identification_number: string;
  email: string;
  birth_date: string;
  destination_country: string;
  destination_country_code: string;
  destination_region: string;
  destination_flag_url?: string | null;
  start_date: string;
  end_date: string;
}

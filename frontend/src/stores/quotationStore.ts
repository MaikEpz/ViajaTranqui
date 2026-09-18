import { defineStore } from 'pinia';
import api from '../services/api';
import type { Country } from '../types/country';
import type { Quotation, QuotationPayload, PricingBreakdown } from '../types/quotation';
import type { ApiResponse, PaginationMeta } from '../types/api';

export interface QuotationState {
  countries: Country[];
  loadingCountries: boolean;
  quotations: Quotation[];
  meta: PaginationMeta;
  loadingQuotations: boolean;
  currentQuote: Quotation | null;
  activeTab: 'create' | 'list';
  searchTerm: string;
  statusFilter: string;
  actionLoading: boolean;
  errorMessage: string;
  successMessage: string;
}

export const useQuotationStore = defineStore('quotation', {
  state: (): QuotationState => ({
    countries: [],
    loadingCountries: false,
    quotations: [],
    meta: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    },
    loadingQuotations: false,
    currentQuote: null,
    activeTab: 'create',
    searchTerm: '',
    statusFilter: '',
    actionLoading: false,
    errorMessage: '',
    successMessage: '',
  }),

  actions: {
    async fetchCountries(): Promise<void> {
      if (this.countries.length > 0) return;
      this.loadingCountries = true;
      try {
        const response = await api.get<ApiResponse<Country[]>>('/countries');
        if (response.data.success) {
          this.countries = response.data.data;
        }
      } catch (err) {
        console.error('Error fetching countries:', err);
        this.errorMessage = 'No se pudieron cargar los países. Intente nuevamente.';
      } finally {
        this.loadingCountries = false;
      }
    },

    async calculatePreview(
      region: string,
      startDate: string,
      endDate: string
    ): Promise<PricingBreakdown | null> {
      if (!region || !startDate || !endDate) return null;
      try {
        const response = await api.post<ApiResponse<PricingBreakdown>>('/quotes/calculate', {
          destination_region: region,
          start_date: startDate,
          end_date: endDate,
        });
        return response.data.data;
      } catch (err) {
        console.error('Error calculating quote preview:', err);
        return null;
      }
    },

    async createQuotation(payload: QuotationPayload): Promise<Quotation> {
      this.actionLoading = true;
      this.errorMessage = '';
      try {
        const response = await api.post<ApiResponse<Quotation>>('/quotes', payload);
        if (response.data.success) {
          this.currentQuote = response.data.data;
          this.successMessage = '¡Cotización generada con éxito!';
          return this.currentQuote;
        }
        throw new Error(response.data.message || 'Error al generar la cotización');
      } catch (err: any) {
        const message = err.response?.data?.message || 'Error al generar la cotización. Verifique los campos.';
        this.errorMessage = message;
        throw err;
      } finally {
        this.actionLoading = false;
      }
    },

    async contractQuotation(quoteId: number): Promise<Quotation> {
      this.actionLoading = true;
      this.errorMessage = '';
      try {
        const response = await api.patch<ApiResponse<Quotation>>(`/quotes/${quoteId}/contract`);
        if (response.data.success) {
          if (this.currentQuote && this.currentQuote.id === quoteId) {
            this.currentQuote = response.data.data;
          }
          const index = this.quotations.findIndex((q) => q.id === quoteId);
          if (index !== -1) {
            this.quotations[index] = response.data.data;
          }
          this.successMessage = '¡El seguro ha sido contratado exitosamente!';
          return response.data.data;
        }
        throw new Error(response.data.message || 'No se pudo contratar el seguro');
      } catch (err: any) {
        const message = err.response?.data?.message || 'No se pudo contratar el seguro.';
        this.errorMessage = message;
        throw err;
      } finally {
        this.actionLoading = false;
      }
    },

    async fetchQuotations(page = 1): Promise<void> {
      this.loadingQuotations = true;
      try {
        const params = {
          page,
          search: this.searchTerm || undefined,
          status: this.statusFilter || undefined,
        };
        const response = await api.get<ApiResponse<Quotation[]>>('/quotes', { params });
        if (response.data.success) {
          this.quotations = response.data.data;
          if (response.data.meta) {
            this.meta = response.data.meta;
          }
        }
      } catch (err) {
        console.error('Error fetching quotations:', err);
        this.errorMessage = 'Error al cargar la lista de cotizaciones.';
      } finally {
        this.loadingQuotations = false;
      }
    },

    downloadPdf(quoteId: number): void {
      const url = `${api.defaults.baseURL}/quotes/${quoteId}/pdf`;
      window.open(url, '_blank');
    },

    clearAlerts(): void {
      this.errorMessage = '';
      this.successMessage = '';
    },
  },
});

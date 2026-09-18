import { defineStore } from 'pinia';
import api from '../services/api';
import type { Country } from '../types/country';
import type { Quotation, QuotationPayload, PricingBreakdown } from '../types/quotation';
import type { ApiResponse, PaginationMeta } from '../types/api';

/**
 * Borrador del itinerario seleccionado en la barra de búsqueda.
 */
export interface TripDraft {
  countryCode: string;
  startDate: string;
  endDate: string;
}

/**
 * Estado del almacén global de cotizaciones y seguros.
 */
export interface QuotationState {
  countries: Country[];
  loadingCountries: boolean;
  quotations: Quotation[];
  meta: PaginationMeta;
  loadingQuotations: boolean;
  currentQuote: Quotation | null;
  activeTab: 'home' | 'checkout' | 'list';
  tripDraft: TripDraft;
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
    activeTab: 'home',
    tripDraft: {
      countryCode: '',
      startDate: '',
      endDate: '',
    },
    searchTerm: '',
    statusFilter: '',
    actionLoading: false,
    errorMessage: '',
    successMessage: '',
  }),

  getters: {
    // Total de pólizas en estado Contratado en la vista actual
    contractedCount: (state): number => {
      return state.quotations.filter((q) => q.status === 'Contratado').length;
    },

    // Total facturado de pólizas contratadas (USD)
    totalBilledAmount: (state): number => {
      return state.quotations
        .filter((q) => q.status === 'Contratado')
        .reduce((sum, q) => sum + Number(q.total_amount), 0);
    },

    // Tasa de conversión de cotizaciones a contratos (%)
    conversionRate: (state): string => {
      if (state.quotations.length === 0) return '0%';
      const contracted = state.quotations.filter((q) => q.status === 'Contratado').length;
      return `${Math.round((contracted / state.quotations.length) * 100)}%`;
    },
  },

  actions: {
    /**
     * Carga el catálogo de países desde el backend (utiliza caché de 24h del servidor).
     */
    async fetchCountries(): Promise<void> {
      if (this.countries.length > 0) return;
      this.loadingCountries = true;
      try {
        const response = await api.get<ApiResponse<Country[]>>('/countries');
        if (response.data.success) {
          this.countries = response.data.data;
        }
      } catch (err) {
        console.error('Error al obtener países:', err);
        this.errorMessage = 'No se pudieron cargar los países. Intente nuevamente.';
      } finally {
        this.loadingCountries = false;
      }
    },

    /**
     * Pre-calcula el valor y desglose de la cotización en tiempo real sin persistir.
     */
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
        console.error('Error al calcular cotización preliminar:', err);
        return null;
      }
    },

    /**
     * Registra una nueva cotización en estado 'Cotizado'.
     */
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

    /**
     * Confirma la contratación y transiciona el estado a 'Contratado'.
     */
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

    /**
     * Consulta la lista paginada de cotizaciones con filtros de búsqueda y estado.
     */
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
        console.error('Error al cargar cotizaciones:', err);
        this.errorMessage = 'Error al cargar la lista de cotizaciones.';
      } finally {
        this.loadingQuotations = false;
      }
    },

    /**
     * Abre en una pestaña nueva la descarga del comprobante PDF de la cotización.
     */
    downloadPdf(quoteId: number): void {
      const url = `${api.defaults.baseURL}/quotes/${quoteId}/pdf`;
      window.open(url, '_blank');
    },

    /**
     * Actualiza el borrador del itinerario seleccionado por el usuario.
     */
    setTripDraft(data: Partial<TripDraft>): void {
      if (!this.tripDraft) {
        this.tripDraft = { countryCode: '', startDate: '', endDate: '' };
      }
      this.tripDraft = { ...this.tripDraft, ...data };
    },

    /**
     * Transiciona a la vista de checkout y configuración de póliza.
     */
    goToCheckout(countryCode?: string, start?: string, end?: string): void {
      if (!this.tripDraft) {
        this.tripDraft = { countryCode: '', startDate: '', endDate: '' };
      }
      if (countryCode) this.tripDraft.countryCode = countryCode;
      if (start) this.tripDraft.startDate = start;
      if (end) this.tripDraft.endDate = end;
      this.activeTab = 'checkout';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    /**
     * Retorna a la vista principal.
     */
    goHome(): void {
      this.activeTab = 'home';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    /**
     * Navega a la vista de consulta de seguros.
     */
    goToList(): void {
      this.activeTab = 'list';
      this.fetchQuotations();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    /**
     * Limpia los mensajes de alerta en pantalla.
     */
    clearAlerts(): void {
      this.errorMessage = '';
      this.successMessage = '';
    },
  },
});

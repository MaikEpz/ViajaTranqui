import { defineStore } from 'pinia';
import api from '../services/api';

export const useQuotationStore = defineStore('quotation', {
  state: () => ({
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
    activeTab: 'create', // 'create' | 'list'
    searchTerm: '',
    statusFilter: '',
    actionLoading: false,
    errorMessage: '',
    successMessage: '',
  }),

  actions: {
    async fetchCountries() {
      if (this.countries.length > 0) return;
      this.loadingCountries = true;
      try {
        const response = await api.get('/countries');
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

    async calculatePreview(region, startDate, endDate) {
      if (!region || !startDate || !endDate) return null;
      try {
        const response = await api.post('/quotes/calculate', {
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

    async createQuotation(payload) {
      this.actionLoading = true;
      this.errorMessage = '';
      try {
        const response = await api.post('/quotes', payload);
        if (response.data.success) {
          this.currentQuote = response.data.data;
          this.successMessage = '¡Cotización generada con éxito!';
          // Refresh list if needed
          return this.currentQuote;
        }
      } catch (err) {
        const message = err.response?.data?.message || 'Error al generar la cotización. Verifique los campos.';
        this.errorMessage = message;
        throw err;
      } finally {
        this.actionLoading = false;
      }
    },

    async contractQuotation(quoteId) {
      this.actionLoading = true;
      this.errorMessage = '';
      try {
        const response = await api.patch(`/quotes/${quoteId}/contract`);
        if (response.data.success) {
          if (this.currentQuote && this.currentQuote.id === quoteId) {
            this.currentQuote = response.data.data;
          }
          // Update item in quotations list if present
          const index = this.quotations.findIndex(q => q.id === quoteId);
          if (index !== -1) {
            this.quotations[index] = response.data.data;
          }
          this.successMessage = '¡El seguro ha sido contratado exitosamente!';
          return response.data.data;
        }
      } catch (err) {
        const message = err.response?.data?.message || 'No se pudo contratar el seguro.';
        this.errorMessage = message;
        throw err;
      } finally {
        this.actionLoading = false;
      }
    },

    async fetchQuotations(page = 1) {
      this.loadingQuotations = true;
      try {
        const params = {
          page,
          search: this.searchTerm || undefined,
          status: this.statusFilter || undefined,
        };
        const response = await api.get('/quotes', { params });
        if (response.data.success) {
          this.quotations = response.data.data;
          this.meta = response.data.meta;
        }
      } catch (err) {
        console.error('Error fetching quotations:', err);
        this.errorMessage = 'Error al cargar la lista de cotizaciones.';
      } finally {
        this.loadingQuotations = false;
      }
    },

    downloadPdf(quoteId) {
      const url = `${api.defaults.baseURL}/quotes/${quoteId}/pdf`;
      window.open(url, '_blank');
    },

    clearAlerts() {
      this.errorMessage = '';
      this.successMessage = '';
    },
  },
});

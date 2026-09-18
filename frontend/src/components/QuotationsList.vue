<template>
  <div class="card list-card">
    <div class="list-header">
      <div>
        <h2 class="list-title">Registro de Cotizaciones y Seguros</h2>
        <p class="list-subtitle">Consulte las pólizas cotizadas y contratadas con búsqueda y filtros.</p>
      </div>

      <button class="btn btn-primary" @click="store.activeTab = 'create'">
        + Nueva Cotización
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
      <div class="search-box">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="store.searchTerm"
          type="text"
          class="form-control search-input"
          placeholder="Buscar por cliente, identificación o destino..."
          @input="onSearchInput"
        />
      </div>

      <div class="status-filter">
        <select
          v-model="store.statusFilter"
          class="form-control"
          @change="onStatusFilterChange"
        >
          <option value="">Todos los Estados</option>
          <option value="Cotizado">Solo Cotizados</option>
          <option value="Contratado">Solo Contratados</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="store.loadingQuotations" class="table-loading">
      <div class="spinner"></div>
      <p>Cargando cotizaciones...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="store.quotations.length === 0" class="empty-state">
      <div class="empty-emoji">📑</div>
      <h3>No se encontraron registros</h3>
      <p>No hay cotizaciones que coincidan con los filtros seleccionados.</p>
    </div>

    <!-- Table of Quotations -->
    <div v-else class="table-responsive">
      <table class="quotes-table">
        <thead>
          <tr>
            <th>Ref.</th>
            <th>Cliente</th>
            <th>Identificación</th>
            <th>Destino</th>
            <th>Fechas de Viaje</th>
            <th>Días</th>
            <th>Valor Total</th>
            <th>Estado</th>
            <th>Emisión</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="quote in store.quotations" :key="quote.id">
            <td class="ref-col">#VTQ-{{ String(quote.id).padStart(5, '0') }}</td>
            <td>
              <div class="client-name">{{ quote.first_name }} {{ quote.last_name }}</div>
              <div class="client-email">{{ quote.email }}</div>
            </td>
            <td><code>{{ quote.identification_number }}</code></td>
            <td>
              <div class="destination-cell">
                <img v-if="quote.destination_flag_url" :src="quote.destination_flag_url" class="country-flag" />
                <div>
                  <span>{{ quote.destination_country }}</span>
                  <span class="region-sub">{{ quote.destination_region }}</span>
                </div>
              </div>
            </td>
            <td class="dates-cell">
              <div>{{ formatDate(quote.start_date) }}</div>
              <div class="date-arrow">↓ al {{ formatDate(quote.end_date) }}</div>
            </td>
            <td><strong>{{ quote.days_count }} d</strong></td>
            <td><strong class="price-val">${{ Number(quote.total_amount).toFixed(2) }}</strong></td>
            <td>
              <span
                class="badge"
                :class="quote.status === 'Contratado' ? 'badge-contracted' : 'badge-quoted'"
              >
                {{ quote.status }}
              </span>
            </td>
            <td class="date-created">{{ formatDateTime(quote.created_at) }}</td>
            <td class="actions-cell text-right">
              <!-- Download PDF -->
              <button
                class="btn btn-secondary btn-sm"
                title="Descargar PDF"
                @click="store.downloadPdf(quote.id)"
              >
                PDF
              </button>

              <!-- Contract Insurance if Cotizado -->
              <button
                v-if="quote.status === 'Cotizado'"
                class="btn btn-success btn-sm"
                title="Contratar este seguro"
                @click="handleContract(quote.id)"
              >
                Contratar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="store.meta.last_page > 1" class="pagination-bar">
      <span class="pagination-info">
        Página {{ store.meta.current_page }} de {{ store.meta.last_page }} ({{ store.meta.total }} cotizaciones)
      </span>

      <div class="pagination-btns">
        <button
          class="btn btn-secondary btn-sm"
          :disabled="store.meta.current_page <= 1"
          @click="changePage(store.meta.current_page - 1)"
        >
          ← Anterior
        </button>

        <button
          class="btn btn-secondary btn-sm"
          :disabled="store.meta.current_page >= store.meta.last_page"
          @click="changePage(store.meta.current_page + 1)"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useQuotationStore } from '../stores/quotationStore';

const store = useQuotationStore();

let searchDebounceTimeout = null;

onMounted(() => {
  store.fetchQuotations();
});

function onSearchInput() {
  clearTimeout(searchDebounceTimeout);
  searchDebounceTimeout = setTimeout(() => {
    store.fetchQuotations(1);
  }, 350);
}

function onStatusFilterChange() {
  store.fetchQuotations(1);
}

function changePage(page) {
  store.fetchQuotations(page);
}

async function handleContract(id) {
  if (confirm('¿Desea confirmar la contratación de este seguro de viaje?')) {
    await store.contractQuotation(id);
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function formatDateTime(dateTimeStr) {
  if (!dateTimeStr) return '-';
  const d = new Date(dateTimeStr);
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<style scoped>
.list-card {
  margin-top: 10px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.list-title {
  font-size: 1.45rem;
  font-weight: 800;
  color: var(--color-accent);
}
.list-subtitle {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 2px;
}

.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
}
.search-box {
  position: relative;
  flex: 1;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}
.search-input {
  padding-left: 38px;
}
.status-filter {
  min-width: 200px;
}

.table-responsive {
  overflow-x: auto;
}

.quotes-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}
.quotes-table th {
  background-color: #f8fafc;
  color: var(--text-muted);
  text-align: left;
  font-weight: 700;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-color);
}
.quotes-table td {
  padding: 14px;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
}
.quotes-table tr:hover {
  background-color: #f8fafc;
}

.ref-col {
  font-weight: 700;
  color: var(--color-primary);
  font-size: 0.82rem;
}

.client-name {
  font-weight: 700;
  color: var(--color-accent);
}
.client-email {
  font-size: 0.78rem;
  color: var(--text-muted);
}

.destination-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}
.country-flag {
  width: 24px;
  height: 16px;
  object-fit: cover;
  border-radius: 2px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.region-sub {
  font-size: 0.75rem;
  color: var(--text-muted);
  display: block;
}

.dates-cell {
  font-size: 0.82rem;
}
.date-arrow {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.price-val {
  font-size: 0.95rem;
  color: var(--color-accent);
}

.date-created {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.actions-cell {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
}
.text-right {
  text-align: right;
}

.pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding-top: 14px;
  border-top: 1px solid var(--border-color);
}
.pagination-info {
  font-size: 0.85rem;
  color: var(--text-muted);
}
.pagination-btns {
  display: flex;
  gap: 8px;
}

.table-loading, .empty-state {
  text-align: center;
  padding: 48px 20px;
  color: var(--text-muted);
}
.empty-emoji {
  font-size: 40px;
  margin-bottom: 12px;
}
.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--color-primary-light);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .list-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .filters-bar {
    flex-direction: column;
  }
}
</style>

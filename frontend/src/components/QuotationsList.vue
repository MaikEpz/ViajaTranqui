<template>
  <div class="card list-card">
    <div class="list-header">
      <div>
        <span class="editorial-category">Gestión Operativa</span>
        <h2 class="list-title">Registro de Pólizas</h2>
        <p class="list-subtitle">
          Historial de cotizaciones emitidas y pólizas de viaje contratadas.
        </p>
      </div>

      <button class="btn btn-primary" @click="store.goHome()">
        + Nueva Cotización
      </button>
    </div>

    <!-- Panel de Métricas / KPIs Minimalista -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <span class="kpi-label">Total Cotizaciones</span>
        <div class="kpi-value">{{ store.meta.total }}</div>
        <span class="kpi-sub">Emisiones registradas</span>
      </div>

      <div class="kpi-card">
        <span class="kpi-label">Pólizas Contratadas</span>
        <div class="kpi-value text-success">{{ store.contractedCount }}</div>
        <span class="kpi-sub">Coberturas vigentes</span>
      </div>

      <div class="kpi-card">
        <span class="kpi-label">Tasa de Conversión</span>
        <div class="kpi-value">{{ store.conversionRate }}</div>
        <span class="kpi-sub">Efectividad de contratación</span>
      </div>

      <div class="kpi-card">
        <span class="kpi-label">Total Facturado</span>
        <div class="kpi-value">${{ store.totalBilledAmount.toFixed(2) }} <small>USD</small></div>
        <span class="kpi-sub">Primas netas recaudadas</span>
      </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-bar">
      <div class="search-box">
        <input
          v-model="store.searchTerm"
          type="text"
          class="form-control search-input"
          placeholder="Buscar por asegurado, pasaporte, cédula, país o correo..."
          @input="onSearchInput"
        />
      </div>

      <div class="status-filter">
        <select
          v-model="store.statusFilter"
          class="form-control status-select"
          @change="onStatusFilterChange"
        >
          <option value="">Todos los Estados</option>
          <option value="Cotizado">Solo Cotizados</option>
          <option value="Contratado">Solo Contratados</option>
        </select>
      </div>
    </div>

    <!-- Estado de Carga -->
    <div v-if="store.loadingQuotations" class="table-loading">
      <p>Cargando información...</p>
    </div>

    <!-- Estado Vacío -->
    <div v-else-if="store.quotations.length === 0" class="empty-state">
      <h3>No se encontraron pólizas</h3>
      <p>No existen registros que coincidan con el criterio de búsqueda ingresado.</p>
    </div>

    <!-- Tabla Editorial de Cotizaciones -->
    <div v-else class="table-responsive">
      <table class="quotes-table">
        <thead>
          <tr>
            <th>Ref.</th>
            <th>Asegurado</th>
            <th>Identificación</th>
            <th>Destino</th>
            <th>Fechas de Cobertura</th>
            <th>Días</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Emisión</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="quote in store.quotations" :key="quote.id" class="quote-row">
            <td class="ref-col">
              <code>#VTQ-{{ String(quote.id).padStart(5, '0') }}</code>
            </td>
            <td>
              <div class="client-name">{{ quote.first_name }} {{ quote.last_name }}</div>
              <div class="client-email">{{ quote.email }}</div>
            </td>
            <td>{{ quote.identification_number }}</td>
            <td>
              <div class="destination-cell">
                <img
                  v-if="quote.destination_flag_url"
                  :src="quote.destination_flag_url"
                  :alt="quote.destination_country"
                  class="country-flag"
                />
                <div>
                  <span class="country-text">{{ quote.destination_country }}</span>
                  <span class="region-sub">{{ quote.destination_region }}</span>
                </div>
              </div>
            </td>
            <td class="dates-cell">
              {{ formatDate(quote.start_date) }} — {{ formatDate(quote.end_date) }}
            </td>
            <td>{{ quote.days_count }} d</td>
            <td><strong>${{ Number(quote.total_amount).toFixed(2) }}</strong></td>
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
              <button
                class="btn btn-secondary btn-sm action-btn"
                title="Descargar comprobante en PDF"
                @click="store.downloadPdf(quote.id)"
              >
                PDF
              </button>

              <button
                v-if="quote.status === 'Cotizado'"
                class="btn btn-primary btn-sm action-btn"
                title="Confirmar contratación"
                @click="handleContract(quote.id)"
              >
                Contratar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación Minimalista -->
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
          Anterior
        </button>

        <button
          class="btn btn-secondary btn-sm"
          :disabled="store.meta.current_page >= store.meta.last_page"
          @click="changePage(store.meta.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useQuotationStore } from '../stores/quotationStore';

const store = useQuotationStore();

let searchDebounceTimeout: any = null;

onMounted(() => {
  store.fetchQuotations();
});

function onSearchInput(): void {
  clearTimeout(searchDebounceTimeout);
  searchDebounceTimeout = setTimeout(() => {
    store.fetchQuotations(1);
  }, 350);
}

function onStatusFilterChange(): void {
  store.fetchQuotations(1);
}

function changePage(page: number): void {
  store.fetchQuotations(page);
}

async function handleContract(id: number): Promise<void> {
  if (confirm('¿Desea confirmar la contratación de este seguro de viaje?')) {
    await store.contractQuotation(id);
  }
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function formatDateTime(dateTimeStr: string | null): string {
  if (!dateTimeStr) return '-';
  const d = new Date(dateTimeStr);
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<style scoped>
.list-card {
  margin-top: 10px;
  background: #ffffff;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  padding: 40px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
}

.editorial-category {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  margin-bottom: 6px;
}

.list-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: var(--color-accent);
  letter-spacing: -0.03em;
  line-height: 1.15;
}
.list-subtitle {
  font-size: 0.95rem;
  color: var(--text-muted);
  margin-top: 6px;
}

/* Grilla KPI */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 32px;
}
.kpi-card {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 22px;
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 0.68rem;
  font-weight: 800;
  color: #717171;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 6px;
}
.kpi-value {
  font-size: 1.8rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin-bottom: 4px;
}
.kpi-value small {
  font-size: 0.9rem;
  font-weight: 600;
  color: #717171;
}
.kpi-sub {
  font-size: 0.75rem;
  color: #888888;
}

.text-success { color: var(--color-success) !important; }

/* Filtros */
.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}
.search-box {
  flex: 1;
}
.status-select {
  width: 200px;
}

/* Tabla */
.table-responsive {
  overflow-x: auto;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  background: #ffffff;
}
.quotes-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.88rem;
}
.quotes-table th {
  background-color: var(--bg-subtle);
  color: #717171;
  font-weight: 700;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border-color);
  white-space: nowrap;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.quotes-table td {
  padding: 16px 18px;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}
.quote-row:hover {
  background-color: #fafafa;
}

.ref-col code {
  font-family: monospace;
  font-size: 0.8rem;
  color: #111111;
}

.client-name {
  font-weight: 700;
  color: #111111;
}
.client-email {
  font-size: 0.78rem;
  color: #717171;
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
}
.country-text {
  font-weight: 600;
  color: #111111;
}
.region-sub {
  font-size: 0.72rem;
  color: #717171;
  display: block;
}

.dates-cell {
  font-size: 0.82rem;
  color: #444444;
  white-space: nowrap;
}

.date-created {
  font-size: 0.8rem;
  color: #717171;
}

.actions-cell {
  white-space: nowrap;
}
.action-btn {
  margin-left: 6px;
}

/* Paginación */
.pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 24px;
}
.pagination-info {
  font-size: 0.85rem;
  color: #717171;
}
.pagination-btns {
  display: flex;
  gap: 8px;
}

/* Carga y vacío */
.table-loading, .empty-state {
  text-align: center;
  padding: 48px 20px;
  color: var(--text-muted);
}
.empty-state h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #111111;
  margin-bottom: 6px;
}

@media (max-width: 900px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 640px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }
  .filters-bar {
    flex-direction: column;
  }
  .status-select {
    width: 100%;
  }
}
</style>

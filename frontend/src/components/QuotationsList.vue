<template>
  <div class="card list-card">
    <div class="list-header">
      <div>
        <div class="header-tag">
          <span class="tag-globe">🗺️</span>
          <span>Panel Operativo de Pólizas</span>
        </div>
        <h2 class="list-title">Historial de Cotizaciones y Seguros</h2>
        <p class="list-subtitle">
          Supervise las emisiones de seguros de viaje, gestione contrataciones y descargue certificados consulares.
        </p>
      </div>

      <button class="btn btn-primary btn-new-quote" @click="store.activeTab = 'create'">
        <span>✈️</span> Nueva Cotización
      </button>
    </div>

    <!-- Panel de Métricas / KPIs de Viaje (Bonus UI) -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon-wrapper icon-blue">✈️</div>
        <div class="kpi-info">
          <span class="kpi-label">Total Cotizaciones</span>
          <span class="kpi-value">{{ store.meta.total }}</span>
          <span class="kpi-microtag">Emisiones en sistema</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon-wrapper icon-green">🛡️</div>
        <div class="kpi-info">
          <span class="kpi-label">Pólizas Contratadas</span>
          <span class="kpi-value text-success">{{ store.contractedCount }}</span>
          <span class="kpi-microtag text-success">Coberturas vigentes</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon-wrapper icon-purple">📈</div>
        <div class="kpi-info">
          <span class="kpi-label">Tasa de Conversión</span>
          <span class="kpi-value text-primary">{{ store.conversionRate }}</span>
          <span class="kpi-microtag">Efectividad de cierre</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon-wrapper icon-gold">💵</div>
        <div class="kpi-info">
          <span class="kpi-label">Prima Facturada</span>
          <span class="kpi-value text-accent">${{ store.totalBilledAmount.toFixed(2) }} <small>USD</small></span>
          <span class="kpi-microtag">Total recaudado</span>
        </div>
      </div>
    </div>

    <!-- Barra de Filtros & Búsqueda -->
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
          placeholder="Buscar por pasajero, pasaporte/cédula, país o correo..."
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
      <div class="spinner"></div>
      <p>Cargando itinerarios y pólizas...</p>
    </div>

    <!-- Estado Vacío -->
    <div v-else-if="store.quotations.length === 0" class="empty-state">
      <div class="empty-emoji">🧭</div>
      <h3>No se encontraron pólizas registradas</h3>
      <p>No existen cotizaciones que coincidan con el término de búsqueda o filtro seleccionado.</p>
    </div>

    <!-- Tabla de Pasajeros y Coberturas -->
    <div v-else class="table-responsive">
      <table class="quotes-table">
        <thead>
          <tr>
            <th>Ref. / Póliza</th>
            <th>Pasajero</th>
            <th>Identificación</th>
            <th>Destino Internacional</th>
            <th>Fechas de Viaje</th>
            <th>Días</th>
            <th>Valor Total</th>
            <th>Estado</th>
            <th>Emisión</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="quote in store.quotations" :key="quote.id" class="quote-row">
            <td class="ref-col">
              <span class="ref-tag">#VTQ-{{ String(quote.id).padStart(5, '0') }}</span>
            </td>
            <td>
              <div class="client-cell">
                <div class="passenger-avatar" :style="{ background: getAvatarColor(quote.first_name) }">
                  {{ getInitials(quote.first_name, quote.last_name) }}
                </div>
                <div>
                  <div class="client-name">{{ quote.first_name }} {{ quote.last_name }}</div>
                  <div class="client-email">{{ quote.email }}</div>
                </div>
              </div>
            </td>
            <td>
              <code class="id-badge">{{ quote.identification_number }}</code>
            </td>
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
              <div class="date-start">🛫 {{ formatDate(quote.start_date) }}</div>
              <div class="date-end">🛬 {{ formatDate(quote.end_date) }}</div>
            </td>
            <td>
              <span class="days-badge">{{ quote.days_count }} d</span>
            </td>
            <td>
              <strong class="price-val">${{ Number(quote.total_amount).toFixed(2) }}</strong>
              <span class="price-cur">USD</span>
            </td>
            <td>
              <span
                class="badge"
                :class="quote.status === 'Contratado' ? 'badge-contracted' : 'badge-quoted'"
              >
                <span class="badge-dot" :class="quote.status === 'Contratado' ? 'dot-green' : 'dot-blue'"></span>
                {{ quote.status }}
              </span>
            </td>
            <td class="date-created">{{ formatDateTime(quote.created_at) }}</td>
            <td class="actions-cell text-right">
              <!-- Botón Descarga PDF -->
              <button
                class="btn btn-secondary btn-sm action-btn"
                title="Descargar comprobante PDF oficial"
                @click="store.downloadPdf(quote.id)"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                </svg>
                PDF
              </button>

              <!-- Botón Contratar si está en Cotizado -->
              <button
                v-if="quote.status === 'Cotizado'"
                class="btn btn-success btn-sm action-btn"
                title="Confirmar y emitir seguro"
                @click="handleContract(quote.id)"
              >
                ✓ Contratar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div v-if="store.meta.last_page > 1" class="pagination-bar">
      <span class="pagination-info">
        Mostrando página <strong>{{ store.meta.current_page }}</strong> de <strong>{{ store.meta.last_page }}</strong> ({{ store.meta.total }} cotizaciones registradas)
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
  if (confirm('¿Desea confirmar y emitir oficialmente la póliza de este seguro de viaje?')) {
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

function getInitials(firstName: string, lastName: string): string {
  const f = firstName ? firstName.trim()[0] : '';
  const l = lastName ? lastName.trim()[0] : '';
  return `${f}${l}`.toUpperCase();
}

function getAvatarColor(name: string): string {
  const colors = [
    'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
    'linear-gradient(135deg, #059669 0%, #047857 100%)',
    'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
    'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)',
    'linear-gradient(135deg, #0891b2 0%, #0e7490 100%)',
  ];
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
}
</script>

<style scoped>
.list-card {
  margin-top: 10px;
  background: #ffffff;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  padding: 32px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.header-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--color-sky-light);
  color: var(--color-primary);
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  margin-bottom: 8px;
  border: 1px solid rgba(2, 132, 199, 0.15);
}

.list-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--color-accent);
  letter-spacing: -0.3px;
}
.list-subtitle {
  font-size: 0.95rem;
  color: var(--text-muted);
  margin-top: 4px;
}

.btn-new-quote {
  padding: 10px 18px;
  font-size: 0.9rem;
}

/* Grilla de Métricas / KPIs */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 28px;
}
.kpi-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.kpi-icon-wrapper {
  width: 46px;
  height: 46px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.icon-blue {
  background: var(--color-primary-light);
}
.icon-green {
  background: var(--color-success-light);
}
.icon-purple {
  background: #f3e8ff;
}
.icon-gold {
  background: #fef3c7;
}

.kpi-info {
  display: flex;
  flex-direction: column;
}
.kpi-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.kpi-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--color-accent);
  line-height: 1.1;
  margin: 2px 0;
}
.kpi-value small {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-muted);
}
.kpi-microtag {
  font-size: 0.72rem;
  color: #94a3b8;
}

.text-success { color: var(--color-success) !important; }
.text-primary { color: var(--color-primary) !important; }
.text-accent { color: var(--color-accent) !important; }

/* Filtros */
.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 22px;
}
.search-box {
  position: relative;
  flex: 1;
}
.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}
.search-input {
  padding-left: 42px;
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
  background-color: #f8fafc;
  color: #475569;
  font-weight: 700;
  padding: 14px 16px;
  border-bottom: 1px solid var(--border-color);
  white-space: nowrap;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.quotes-table td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
}
.quote-row {
  transition: background-color 0.15s ease;
}
.quote-row:hover {
  background-color: #f8fafc;
}

.ref-tag {
  font-family: monospace;
  font-weight: 700;
  color: #0369a1;
  background: var(--color-primary-light);
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.82rem;
}

/* Celda de Pasajero */
.client-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}
.passenger-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.client-name {
  font-weight: 700;
  color: var(--color-accent);
}
.client-email {
  font-size: 0.78rem;
  color: var(--text-muted);
}

.id-badge {
  background: #f1f5f9;
  color: #334155;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
}

/* Celda de Destino */
.destination-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}
.country-flag {
  width: 28px;
  height: 18px;
  object-fit: cover;
  border-radius: 3px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.country-text {
  font-weight: 700;
  color: var(--color-accent);
  display: block;
}
.region-sub {
  font-size: 0.72rem;
  color: var(--text-muted);
  display: block;
}

/* Fechas de Vuelo */
.dates-cell {
  font-size: 0.82rem;
  white-space: nowrap;
}
.date-start {
  color: #0369a1;
  font-weight: 600;
}
.date-end {
  color: #475569;
  margin-top: 2px;
}

.days-badge {
  background: #f1f5f9;
  padding: 3px 8px;
  border-radius: var(--radius-full);
  font-weight: 700;
  font-size: 0.78rem;
  color: var(--color-accent);
}

.price-val {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--color-accent);
}
.price-cur {
  font-size: 0.72rem;
  color: var(--text-muted);
  margin-left: 3px;
  font-weight: 600;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.dot-green {
  background-color: var(--color-success);
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}
.dot-blue {
  background-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
}

.date-created {
  font-size: 0.78rem;
  color: var(--text-muted);
  white-space: nowrap;
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
  margin-top: 20px;
}
.pagination-info {
  font-size: 0.85rem;
  color: var(--text-muted);
}
.pagination-btns {
  display: flex;
  gap: 8px;
}

/* Estado de Carga */
.table-loading, .empty-state {
  text-align: center;
  padding: 48px 20px;
  color: var(--text-muted);
}
.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid var(--border-color);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}
.empty-emoji {
  font-size: 3rem;
  margin-bottom: 12px;
}
.empty-state h3 {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--color-accent);
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

<template>
  <div class="list-card">
    <div class="list-header">
      <div>
        <span class="editorial-category">Gestión Operativa</span>
        <h2 class="list-title">Registro de Pólizas</h2>
        <p class="list-subtitle">
          Historial y consulta de cotizaciones emitidas y pólizas de viaje contratadas.
        </p>
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

    <!-- Indicador de desplazamiento horizontal para pantallas estrechas -->
    <div v-if="!store.loadingQuotations && store.quotations.length > 0" class="scroll-hint-bar">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="17 11 12 6 7 11"></polyline>
        <polyline points="17 18 12 13 7 18"></polyline>
      </svg>
      <span>Desliza la tabla hacia la derecha para ver todas las columnas</span>
    </div>

    <!-- 1. ESTADO DE CARGA: SKELETON LOADER CON MISMA ESTRUCTURA -->
    <div v-if="store.loadingQuotations" class="table-responsive skeleton-wrapper" aria-busy="true">
      <table class="quotes-table skeleton-table">
        <thead>
          <tr>
            <th class="th-ref">Póliza & Emisión</th>
            <th class="th-client">Asegurado & Contacto</th>
            <th class="th-dest">Destino</th>
            <th class="th-dates">Vigencia & Días</th>
            <th class="th-amount">Total & Estado</th>
            <th class="th-actions text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="n in 5" :key="'skel-' + n" class="skeleton-row">
            <!-- Columna 1: Ref & Emisión -->
            <td class="td-ref">
              <div class="skel-bar skel-ref"></div>
              <div class="skel-bar skel-date"></div>
            </td>

            <!-- Columna 2: Asegurado & Contacto -->
            <td class="td-client">
              <div class="skel-bar skel-name"></div>
              <div class="skel-bar skel-sub"></div>
            </td>

            <!-- Columna 3: Destino -->
            <td class="td-dest">
              <div class="skel-dest-wrapper">
                <div class="skel-flag"></div>
                <div>
                  <div class="skel-bar skel-country"></div>
                  <div class="skel-bar skel-region"></div>
                </div>
              </div>
            </td>

            <!-- Columna 4: Vigencia & Días -->
            <td class="td-dates">
              <div class="skel-bar skel-dates"></div>
              <div class="skel-bar skel-days"></div>
            </td>

            <!-- Columna 5: Monto & Estado -->
            <td class="td-amount">
              <div class="skel-bar skel-amount"></div>
              <div class="skel-bar skel-badge"></div>
            </td>

            <!-- Columna 6: Acciones -->
            <td class="td-actions text-right">
              <div class="skel-actions-wrapper">
                <div class="skel-btn skel-btn-sm"></div>
                <div class="skel-btn skel-btn-md"></div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 2. ESTADO VACÍO -->
    <div v-else-if="store.quotations.length === 0" class="empty-state">
      <h3>No se encontraron pólizas</h3>
      <p>No existen registros que coincidan con el criterio de búsqueda ingresado.</p>
    </div>

    <!-- 3. TABLA DE DATOS REALES -->
    <div v-else class="table-responsive">
      <table class="quotes-table">
        <thead>
          <tr>
            <th class="th-ref">Póliza & Emisión</th>
            <th class="th-client">Asegurado & Contacto</th>
            <th class="th-dest">Destino</th>
            <th class="th-dates">Vigencia & Días</th>
            <th class="th-amount">Total & Estado</th>
            <th class="th-actions text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="quote in store.quotations" :key="quote.id" class="quote-row">
            <!-- Columna 1: Referencia y Fecha de Emisión -->
            <td class="td-ref">
              <span class="ref-badge">#VTQ-{{ String(quote.id).padStart(5, '0') }}</span>
              <span class="cell-sub date-sub">{{ formatDateTime(quote.created_at) }}</span>
            </td>

            <!-- Columna 2: Asegurado, Cédula/Pasaporte y Email -->
            <td class="td-client">
              <div class="client-name">{{ quote.first_name }} {{ quote.last_name }}</div>
              <div class="client-meta">
                <span class="client-id">ID: {{ quote.identification_number }}</span>
                <span class="meta-dot">·</span>
                <span class="client-email" :title="quote.email">{{ quote.email }}</span>
              </div>
            </td>

            <!-- Columna 3: Destino y Región -->
            <td class="td-dest">
              <div class="destination-cell">
                <img
                  v-if="quote.destination_flag_url"
                  :src="quote.destination_flag_url"
                  :alt="quote.destination_country"
                  class="country-flag"
                  loading="lazy"
                />
                <div class="destination-text">
                  <span class="country-text">{{ quote.destination_country }}</span>
                  <span class="region-sub">{{ quote.destination_region }}</span>
                </div>
              </div>
            </td>

            <!-- Columna 4: Fechas y Cantidad de Días (Ultra Compacto) -->
            <td class="td-dates">
              <div class="dates-range">
                <span>{{ formatDate(quote.start_date) }}</span>
                <span class="range-sep">→</span>
                <span>{{ formatDate(quote.end_date) }}</span>
              </div>
              <span class="days-badge">{{ quote.days_count }} días</span>
            </td>

            <!-- Columna 5: Monto Total y Estado -->
            <td class="td-amount">
              <div class="amount-main">
                ${{ Number(quote.total_amount).toFixed(2) }} <small>USD</small>
              </div>
              <span
                class="badge"
                :class="quote.status === 'Contratado' ? 'badge-contracted' : 'badge-quoted'"
              >
                {{ quote.status }}
              </span>
            </td>

            <!-- Columna 6: Acciones -->
            <td class="td-actions text-right">
              <div class="actions-group">
                <button
                  class="btn btn-secondary btn-sm action-btn pdf-btn"
                  title="Descargar comprobante en PDF"
                  @click="store.downloadPdf(quote.id)"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                  </svg>
                  PDF
                </button>

                <button
                  v-if="quote.status === 'Cotizado'"
                  class="btn btn-primary btn-sm action-btn contract-btn"
                  title="Confirmar contratación"
                  @click="handleContract(quote.id)"
                >
                  Contratar
                </button>

                <span v-else class="status-confirmed-tag" title="Póliza contratada y vigente">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Activa
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación Minimalista -->
    <div v-if="!store.loadingQuotations && store.meta.last_page > 1" class="pagination-bar">
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
  const clean = dateStr.split('T')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
}

function formatDateTime(dateTimeStr: string | null): string {
  if (!dateTimeStr) return '-';
  const d = new Date(dateTimeStr);
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<style scoped>
.list-card {
  width: 100%;
  box-sizing: border-box;
  margin-top: 10px;
  background: #ffffff;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  padding: 36px 40px;
  opacity: 0;
  transform: translateY(24px);
  animation: listFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes listFadeUp {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
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

/* Filtros */
.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  width: 100%;
}
.search-box {
  flex: 1;
}
.status-select {
  width: 200px;
}

/* Indicador de scroll para pantallas estrechas */
.scroll-hint-bar {
  display: none;
  align-items: center;
  gap: 6px;
  font-size: 0.74rem;
  font-weight: 600;
  color: #717171;
  margin-bottom: 10px;
  padding: 6px 12px;
  background-color: #f4f4f5;
  border-radius: var(--radius-sm);
}

/* Contenedor de Tabla con Ancho Estable */
.table-responsive {
  width: 100%;
  box-sizing: border-box;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  background: #ffffff;
  scrollbar-width: thin;
  scrollbar-color: #d4d4d8 #f4f4f5;
}

.table-responsive::-webkit-scrollbar {
  height: 6px;
}
.table-responsive::-webkit-scrollbar-track {
  background: #f4f4f5;
  border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb {
  background: #d4d4d8;
  border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
  background: #a1a1aa;
}

.quotes-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.88rem;
  table-layout: auto;
}

.quotes-table th {
  background-color: var(--bg-subtle);
  color: #717171;
  font-weight: 700;
  padding: 13px 18px;
  border-bottom: 1px solid var(--border-color);
  white-space: nowrap;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.quotes-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.quote-row:hover {
  background-color: #fafafa;
}

/* Columna 1: Póliza */
.td-ref {
  white-space: nowrap;
}
.ref-badge {
  font-family: monospace;
  font-size: 0.82rem;
  font-weight: 800;
  color: #111111;
  display: block;
}
.cell-sub {
  font-size: 0.74rem;
  color: #888888;
  display: block;
  margin-top: 2px;
}

/* Columna 2: Asegurado */
.td-client {
  min-width: 220px;
}
.client-name {
  font-weight: 700;
  color: #111111;
  font-size: 0.88rem;
  line-height: 1.25;
}
.client-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.76rem;
  color: #717171;
  margin-top: 3px;
  flex-wrap: wrap;
}
.client-id {
  font-weight: 600;
  color: #444444;
}
.meta-dot {
  color: #d4d4d8;
}
.client-email {
  color: #717171;
  max-width: 170px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Columna 3: Destino */
.td-dest {
  white-space: nowrap;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
  flex-shrink: 0;
}
.destination-text {
  display: flex;
  flex-direction: column;
}
.country-text {
  font-weight: 700;
  color: #111111;
  font-size: 0.86rem;
  line-height: 1.2;
}
.region-sub {
  font-size: 0.72rem;
  color: #717171;
  margin-top: 1px;
}

/* Columna 4: Vigencia & Días */
.td-dates {
  white-space: nowrap;
}
.dates-range {
  font-size: 0.82rem;
  font-weight: 600;
  color: #222222;
  line-height: 1.25;
  display: flex;
  align-items: center;
  gap: 5px;
}
.range-sep {
  color: #a1a1aa;
  font-size: 0.76rem;
}
.days-badge {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 600;
  color: #717171;
  background-color: #f4f4f5;
  padding: 1px 6px;
  border-radius: 4px;
  margin-top: 3px;
}

/* Columna 5: Monto & Estado */
.td-amount {
  white-space: nowrap;
}
.amount-main {
  font-size: 0.95rem;
  font-weight: 800;
  color: #111111;
  line-height: 1.2;
  margin-bottom: 3px;
}
.amount-main small {
  font-size: 0.7rem;
  font-weight: 600;
  color: #717171;
}

/* Columna 6: Acciones */
.td-actions {
  white-space: nowrap;
}
.actions-group {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  justify-content: flex-end;
}
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.76rem;
  font-weight: 700;
  padding: 6px 11px;
}
.pdf-btn svg {
  stroke-width: 2.2;
}
.status-confirmed-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.74rem;
  font-weight: 700;
  color: #15803d;
  background-color: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 5px 9px;
  border-radius: var(--radius-sm);
}

/* ============================================================
   SKELETON LOADER CON EFECTO SHIMMER SUAVE
   ============================================================ */
.skeleton-row td {
  padding: 16px 18px;
  border-bottom: 1px solid #f0f0f0;
}

.skel-bar,
.skel-flag,
.skel-btn {
  background: linear-gradient(
    90deg,
    #f4f4f6 0%,
    #e8e8eb 50%,
    #f4f4f6 100%
  );
  background-size: 200% 100%;
  animation: skeletonShimmer 1.4s ease-in-out infinite;
  border-radius: 4px;
}

@keyframes skeletonShimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.skel-ref {
  width: 80px;
  height: 14px;
  margin-bottom: 6px;
}
.skel-date {
  width: 60px;
  height: 10px;
}
.skel-name {
  width: 120px;
  height: 14px;
  margin-bottom: 6px;
}
.skel-sub {
  width: 160px;
  height: 10px;
}
.skel-dest-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}
.skel-flag {
  width: 24px;
  height: 16px;
  border-radius: 2px;
  flex-shrink: 0;
}
.skel-country {
  width: 75px;
  height: 13px;
  margin-bottom: 4px;
}
.skel-region {
  width: 50px;
  height: 10px;
}
.skel-dates {
  width: 140px;
  height: 14px;
  margin-bottom: 6px;
}
.skel-days {
  width: 55px;
  height: 13px;
}
.skel-amount {
  width: 65px;
  height: 15px;
  margin-bottom: 6px;
}
.skel-badge {
  width: 60px;
  height: 15px;
  border-radius: 9999px;
}
.skel-actions-wrapper {
  display: inline-flex;
  gap: 6px;
  justify-content: flex-end;
}
.skel-btn-sm {
  width: 48px;
  height: 26px;
  border-radius: var(--radius-sm);
}
.skel-btn-md {
  width: 70px;
  height: 26px;
  border-radius: var(--radius-sm);
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

/* Estado Vacío */
.empty-state {
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

@media (max-width: 1024px) {
  .scroll-hint-bar {
    display: flex;
  }
}

@media (max-width: 900px) {
  .list-card {
    padding: 24px 20px;
  }
}

@media (max-width: 640px) {
  .list-card {
    padding: 18px 14px;
    border-radius: var(--radius-lg);
    margin-top: 4px;
  }
  .list-title {
    font-size: 1.45rem;
  }
  .list-subtitle {
    font-size: 0.82rem;
  }
  .filters-bar {
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
  }
  .search-input {
    padding: 11px 14px;
    font-size: 0.88rem;
  }
  .status-select {
    width: 100%;
    padding: 11px 14px;
    font-size: 0.88rem;
  }
  .scroll-hint-bar {
    display: flex;
    font-size: 0.72rem;
    padding: 7px 10px;
  }
  .pagination-bar {
    flex-direction: column;
    gap: 12px;
    align-items: center;
    text-align: center;
  }
  .pagination-btns {
    width: 100%;
    display: flex;
  }
  .pagination-btns .btn {
    flex: 1;
  }
}

@media (max-width: 380px) {
  .list-card {
    padding: 14px 10px;
  }
}
</style>

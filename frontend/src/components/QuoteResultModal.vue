<template>
  <div v-if="quote" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card">
      <!-- Encabezado estilo Voucher de Vuelo -->
      <div class="modal-header">
        <div class="modal-brand">
          <span class="brand-plane">✈️</span>
          <div>
            <span class="voucher-title">VIAJATRANQUI GLOBAL TRAVEL</span>
            <span class="voucher-subtitle">COMPROBANTE OFICIAL DE COBERTURA</span>
          </div>
        </div>
        <button class="close-btn" title="Cerrar" @click="$emit('close')">✕</button>
      </div>

      <div class="modal-content">
        <!-- Tarjeta Central de la Póliza -->
        <div class="policy-ticket">
          <!-- Cinta de Estado -->
          <div class="ticket-status-bar">
            <div class="status-left">
              <span class="status-dot" :class="quote.status === 'Contratado' ? 'dot-active' : 'dot-quoted'"></span>
              <span class="status-name">{{ quote.status === 'Contratado' ? 'PÓLIZA CONTRATADA Y ACTIVA' : 'COTIZACIÓN DISPONIBLE' }}</span>
            </div>
            <span class="policy-code">REF: #VTQ-{{ String(quote.id).padStart(5, '0') }}</span>
          </div>

          <!-- Ruta del Viaje -->
          <div class="ticket-route-box">
            <div class="route-point">
              <span class="route-city">ECUADOR</span>
              <span class="route-tag">Origen</span>
            </div>

            <div class="route-flight-symbol">
              <span class="dashed-line"></span>
              <span class="fly-icon">✈</span>
              <span class="dashed-line"></span>
            </div>

            <div class="route-point text-right">
              <span class="route-city">
                <img v-if="quote.destination_flag_url" :src="quote.destination_flag_url" class="route-flag" />
                {{ quote.destination_country }}
              </span>
              <span class="route-tag">Destino ({{ quote.destination_region }})</span>
            </div>
          </div>

          <!-- Perforación decorativa de boleto -->
          <div class="ticket-divider">
            <div class="notch notch-left"></div>
            <div class="dashed-border"></div>
            <div class="notch notch-right"></div>
          </div>

          <!-- Detalles del Pasajero y Fechas -->
          <div class="ticket-passenger-grid">
            <div class="p-item">
              <span class="p-label">Asegurado</span>
              <span class="p-value">{{ quote.first_name }} {{ quote.last_name }}</span>
            </div>
            <div class="p-item">
              <span class="p-label">Identificación / Pasaporte</span>
              <span class="p-value"><code>{{ quote.identification_number }}</code></span>
            </div>
            <div class="p-item">
              <span class="p-label">Fechas del Viaje</span>
              <span class="p-value">{{ formatDate(quote.start_date) }} al {{ formatDate(quote.end_date) }}</span>
            </div>
            <div class="p-item">
              <span class="p-label">Días Cobertura</span>
              <span class="p-value highlight-days">{{ quote.days_count }} días continuos</span>
            </div>
          </div>

          <!-- Desglose Financiero -->
          <div class="ticket-pricing-breakdown">
            <div class="p-row">
              <span>Tarifa Base (USD $3.00/día × {{ quote.days_count }} d):</span>
              <span>${{ Number(quote.base_amount).toFixed(2) }}</span>
            </div>
            <div class="p-row">
              <span>Recargo Regional {{ quote.destination_region }} ({{ quote.surcharge_percentage }}%):</span>
              <span class="text-warning">+${{ Number(quote.surcharge_amount).toFixed(2) }}</span>
            </div>
            <div class="p-row total-row">
              <span class="total-label">TOTAL A PAGAR (USD):</span>
              <span class="total-value">${{ Number(quote.total_amount).toFixed(2) }}</span>
            </div>
          </div>

          <!-- Código de barras decorativo SVG -->
          <div class="ticket-barcode-strip">
            <svg class="barcode-svg" viewBox="0 0 200 30">
              <rect x="0" y="0" width="3" height="30" fill="#334155" />
              <rect x="6" y="0" width="2" height="30" fill="#334155" />
              <rect x="11" y="0" width="4" height="30" fill="#334155" />
              <rect x="18" y="0" width="1" height="30" fill="#334155" />
              <rect x="22" y="0" width="3" height="30" fill="#334155" />
              <rect x="28" y="0" width="2" height="30" fill="#334155" />
              <rect x="33" y="0" width="5" height="30" fill="#334155" />
              <rect x="41" y="0" width="2" height="30" fill="#334155" />
              <rect x="46" y="0" width="3" height="30" fill="#334155" />
              <rect x="52" y="0" width="1" height="30" fill="#334155" />
              <rect x="56" y="0" width="4" height="30" fill="#334155" />
              <rect x="63" y="0" width="2" height="30" fill="#334155" />
              <rect x="68" y="0" width="3" height="30" fill="#334155" />
              <rect x="74" y="0" width="2" height="30" fill="#334155" />
              <rect x="79" y="0" width="4" height="30" fill="#334155" />
              <rect x="86" y="0" width="2" height="30" fill="#334155" />
              <rect x="91" y="0" width="3" height="30" fill="#334155" />
              <rect x="97" y="0" width="2" height="30" fill="#334155" />
              <rect x="102" y="0" width="4" height="30" fill="#334155" />
              <rect x="109" y="0" width="1" height="30" fill="#334155" />
              <rect x="113" y="0" width="3" height="30" fill="#334155" />
              <rect x="119" y="0" width="2" height="30" fill="#334155" />
              <rect x="124" y="0" width="5" height="30" fill="#334155" />
              <rect x="132" y="0" width="2" height="30" fill="#334155" />
              <rect x="137" y="0" width="3" height="30" fill="#334155" />
              <rect x="143" y="0" width="2" height="30" fill="#334155" />
              <rect x="148" y="0" width="4" height="30" fill="#334155" />
              <rect x="155" y="0" width="1" height="30" fill="#334155" />
              <rect x="159" y="0" width="3" height="30" fill="#334155" />
              <rect x="165" y="0" width="2" height="30" fill="#334155" />
              <rect x="170" y="0" width="4" height="30" fill="#334155" />
              <rect x="177" y="0" width="2" height="30" fill="#334155" />
              <rect x="182" y="0" width="3" height="30" fill="#334155" />
              <rect x="188" y="0" width="2" height="30" fill="#334155" />
              <rect x="193" y="0" width="4" height="30" fill="#334155" />
            </svg>
            <span class="barcode-number">VTQ-{{ String(quote.id).padStart(5, '0') }}-SECURE-PASS</span>
          </div>
        </div>

        <div v-if="quote.status === 'Contratado'" class="contract-confirmed-banner">
          <span class="banner-check">✓</span>
          <div>
            <strong>¡Póliza Activa y Confirmada!</strong>
            <p>Se ha emitido oficialmente la cobertura. Puede descargar su certificado en PDF para trámites consulares.</p>
          </div>
        </div>
      </div>

      <!-- Botones de Acción del Modal -->
      <div class="modal-actions">
        <button class="btn btn-secondary btn-action" @click="downloadPdf">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          Descargar Comprobante PDF
        </button>

        <button
          v-if="quote.status === 'Cotizado'"
          class="btn btn-success btn-action"
          :disabled="store.actionLoading"
          @click="contractInsurance"
        >
          <span v-if="store.actionLoading">Procesando emisión...</span>
          <span v-else>✓ Confirmar y Contratar Seguro</span>
        </button>

        <button v-else class="btn btn-primary btn-action" @click="$emit('close')">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useQuotationStore } from '../stores/quotationStore';
import type { Quotation } from '../types/quotation';

const props = defineProps<{
  quote: Quotation | null;
}>();

defineEmits<{
  (e: 'close'): void;
}>();

const store = useQuotationStore();

function downloadPdf() {
  if (props.quote) {
    store.downloadPdf(props.quote.id);
  }
}

async function contractInsurance() {
  if (props.quote) {
    await store.contractQuotation(props.quote.id);
  }
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return dateStr;
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 16px;
}

.modal-card {
  background-color: #ffffff;
  border-radius: var(--radius-xl);
  max-width: 540px;
  width: 100%;
  box-shadow: var(--shadow-xl);
  overflow: hidden;
  animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalPop {
  from {
    opacity: 0;
    transform: scale(0.92) translateY(12px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #ffffff;
  padding: 18px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}
.brand-plane {
  font-size: 1.6rem;
}
.voucher-title {
  display: block;
  font-size: 0.95rem;
  font-weight: 800;
  letter-spacing: 0.5px;
}
.voucher-subtitle {
  display: block;
  font-size: 0.7rem;
  color: #94a3b8;
  font-weight: 600;
  letter-spacing: 0.8px;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.15s ease;
}
.close-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.05);
}

.modal-content {
  padding: 24px;
}

/* Tarjeta Ticket */
.policy-ticket {
  background: #ffffff;
  border: 1.5px solid var(--border-color);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.ticket-status-bar {
  background: #f8fafc;
  padding: 10px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f5f9;
}
.status-left {
  display: flex;
  align-items: center;
  gap: 8px;
}
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.dot-active {
  background-color: var(--color-success);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}
.dot-quoted {
  background-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2);
}
.status-name {
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: var(--color-accent);
}
.policy-code {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-muted);
}

/* Franja de Ruta */
.ticket-route-box {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
}
.route-point {
  display: flex;
  flex-direction: column;
}
.route-city {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--color-accent);
  display: flex;
  align-items: center;
  gap: 6px;
}
.route-flag {
  width: 22px;
  height: 14px;
  object-fit: cover;
  border-radius: 2px;
}
.route-tag {
  font-size: 0.75rem;
  color: var(--text-muted);
}
.route-flight-symbol {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--color-primary);
}
.dashed-line {
  width: 24px;
  height: 1px;
  background: #cbd5e1;
}
.fly-icon {
  font-size: 1.1rem;
}

/* Divisor con perforación */
.ticket-divider {
  position: relative;
  display: flex;
  align-items: center;
  height: 16px;
  background: #ffffff;
}
.dashed-border {
  flex: 1;
  border-top: 2px dashed #cbd5e1;
  margin: 0 14px;
}
.notch {
  width: 12px;
  height: 16px;
  background-color: #ffffff;
  position: absolute;
}
.notch-left {
  left: 0;
  border-radius: 0 8px 8px 0;
  border-right: 1.5px solid var(--border-color);
  background: #f8fafc;
}
.notch-right {
  right: 0;
  border-radius: 8px 0 0 8px;
  border-left: 1.5px solid var(--border-color);
  background: #f8fafc;
}

/* Grilla de Pasajero */
.ticket-passenger-grid {
  padding: 14px 20px;
  background: #ffffff;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.p-item {
  display: flex;
  flex-direction: column;
}
.p-label {
  font-size: 0.72rem;
  color: var(--text-muted);
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.4px;
}
.p-value {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--color-accent);
}
.highlight-days {
  color: var(--color-primary);
}

/* Desglose de Precios */
.ticket-pricing-breakdown {
  padding: 14px 20px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.p-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: var(--text-muted);
}
.p-row span:last-child {
  font-weight: 700;
  color: var(--text-main);
}
.text-warning {
  color: #d97706 !important;
}
.total-row {
  border-top: 1px solid var(--border-color);
  padding-top: 8px;
  margin-top: 4px;
}
.total-label {
  font-weight: 800;
  color: var(--color-accent);
  font-size: 0.9rem;
}
.total-value {
  font-size: 1.3rem;
  font-weight: 800;
  color: var(--color-primary);
}

/* Código de Barras */
.ticket-barcode-strip {
  background: #ffffff;
  padding: 12px 20px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.barcode-svg {
  width: 180px;
  height: 24px;
}
.barcode-number {
  font-size: 0.68rem;
  letter-spacing: 1.5px;
  font-family: monospace;
  color: var(--text-muted);
}

/* Banner de Póliza Activa */
.contract-confirmed-banner {
  margin-top: 16px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 1px solid #bbf7d0;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  gap: 12px;
}
.banner-check {
  font-size: 1.4rem;
  color: #15803d;
  font-weight: 800;
}
.contract-confirmed-banner strong {
  font-size: 0.88rem;
  color: #166534;
  display: block;
}
.contract-confirmed-banner p {
  font-size: 0.78rem;
  color: #15803d;
  margin-top: 2px;
}

/* Acciones */
.modal-actions {
  display: flex;
  gap: 12px;
  padding: 18px 24px;
  background-color: #f8fafc;
  border-top: 1px solid var(--border-color);
}
.btn-action {
  flex: 1;
}
</style>

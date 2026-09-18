<template>
  <div v-if="quote" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <div>
          <span class="modal-category">Póliza Oficial</span>
          <h3 class="modal-title">
            {{ quote.status === 'Contratado' ? 'Póliza Emitida con Éxito' : 'Resumen de Cotización' }}
          </h3>
        </div>
        <button class="close-btn" title="Cerrar" @click="$emit('close')">✕</button>
      </div>

      <div class="modal-content">
        <!-- Tarjeta de Certificado Minimalista -->
        <div class="policy-receipt">
          <div class="receipt-top">
            <div class="receipt-status-pill">
              <span class="status-dot" :class="quote.status === 'Contratado' ? 'dot-green' : 'dot-gray'"></span>
              <span>{{ quote.status }}</span>
            </div>
            <span class="receipt-ref">VTQ-{{ String(quote.id).padStart(5, '0') }}</span>
          </div>

          <div class="receipt-hero-price">
            <span class="price-caption">VALOR TOTAL</span>
            <div class="price-val">${{ Number(quote.total_amount).toFixed(2) }} <span class="cur">USD</span></div>
          </div>

          <div class="receipt-divider"></div>

          <div class="receipt-details">
            <div class="r-row">
              <span class="r-label">Asegurado</span>
              <span class="r-val">{{ quote.first_name }} {{ quote.last_name }}</span>
            </div>
            <div class="r-row">
              <span class="r-label">Identificación / DNI</span>
              <span class="r-val">{{ quote.identification_number }}</span>
            </div>
            <div class="r-row">
              <span class="r-label">Destino</span>
              <span class="r-val">
                <img v-if="quote.destination_flag_url" :src="quote.destination_flag_url" class="mini-flag" />
                {{ quote.destination_country }} ({{ quote.destination_region }})
              </span>
            </div>
            <div class="r-row">
              <span class="r-label">Vigencia</span>
              <span class="r-val">{{ formatDate(quote.start_date) }} al {{ formatDate(quote.end_date) }}</span>
            </div>
            <div class="r-row">
              <span class="r-label">Duración</span>
              <span class="r-val">{{ quote.days_count }} días de cobertura</span>
            </div>
          </div>

          <div class="receipt-divider"></div>

          <div class="receipt-breakdown">
            <div class="b-row">
              <span>Tarifa Base ({{ quote.days_count }} d × $3.00):</span>
              <span>${{ Number(quote.base_amount).toFixed(2) }}</span>
            </div>
            <div class="b-row">
              <span>Recargo Regional ({{ quote.surcharge_percentage }}%):</span>
              <span>+${{ Number(quote.surcharge_amount).toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <div v-if="quote.status === 'Contratado'" class="status-notice-success">
          ✓ Póliza confirmada y registrada en el sistema. Válida para visado Schengen.
        </div>
      </div>

      <!-- Acciones Minimalistas -->
      <div class="modal-actions">
        <button class="btn btn-secondary btn-action" @click="downloadPdf">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          Descargar PDF
        </button>

        <button
          v-if="quote.status === 'Cotizado'"
          class="btn btn-primary btn-action"
          :disabled="store.actionLoading"
          @click="contractInsurance"
        >
          <span v-if="store.actionLoading">Confirmando...</span>
          <span v-else>Confirmar y Contratar ➔</span>
        </button>

        <button v-else class="btn btn-primary btn-action" @click="$emit('close')">
          Listo
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
  background-color: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 20px;
}

.modal-card {
  background-color: #ffffff;
  border-radius: var(--radius-xl);
  max-width: 500px;
  width: 100%;
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  border: 1px solid var(--border-color);
  animation: modalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  padding: 28px 28px 16px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.modal-category {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  display: block;
  margin-bottom: 4px;
}

.modal-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.02em;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: #717171;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  transition: color 0.15s ease;
}
.close-btn:hover {
  color: #111111;
}

.modal-content {
  padding: 0 28px 28px;
}

.policy-receipt {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 24px;
}

.receipt-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.receipt-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #111111;
  background: #ffffff;
  padding: 4px 10px;
  border-radius: var(--radius-full);
  border: 1px solid var(--border-color);
}
.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.dot-green { background-color: #15803d; }
.dot-gray { background-color: #717171; }

.receipt-ref {
  font-size: 0.75rem;
  font-family: monospace;
  font-weight: 700;
  color: #717171;
}

.receipt-hero-price {
  text-align: center;
  padding: 12px 0;
}
.price-caption {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #717171;
}
.price-val {
  font-size: 2.2rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin-top: 2px;
}
.price-val .cur {
  font-size: 1rem;
  font-weight: 600;
  color: #717171;
}

.receipt-divider {
  height: 1px;
  background-color: var(--border-color);
  margin: 14px 0;
}

.receipt-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.r-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
}
.r-label {
  color: #717171;
}
.r-val {
  font-weight: 600;
  color: #111111;
  display: flex;
  align-items: center;
  gap: 6px;
}
.mini-flag {
  width: 18px;
  height: 12px;
  object-fit: cover;
  border-radius: 2px;
}

.receipt-breakdown {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.8rem;
  color: #717171;
}
.b-row {
  display: flex;
  justify-content: space-between;
}

.status-notice-success {
  margin-top: 16px;
  padding: 12px 16px;
  background-color: var(--color-success-light);
  color: var(--color-success);
  font-size: 0.82rem;
  font-weight: 600;
  border-radius: var(--radius-md);
  border: 1px solid #bbf7d0;
  text-align: center;
}

.modal-actions {
  display: flex;
  gap: 12px;
  padding: 20px 28px;
  border-top: 1px solid var(--border-color);
  background-color: #ffffff;
}
.btn-action {
  flex: 1;
}
</style>

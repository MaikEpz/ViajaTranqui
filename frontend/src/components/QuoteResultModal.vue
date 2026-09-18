<template>
  <div v-if="quote" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <div class="status-indicator">
          <span
            class="badge"
            :class="quote.status === 'Contratado' ? 'badge-contracted' : 'badge-quoted'"
          >
            {{ quote.status }}
          </span>
        </div>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <div class="modal-content">
        <div class="congrats-header">
          <div class="icon-circle">
            <span v-if="quote.status === 'Contratado'">🛡️</span>
            <span v-else>📋</span>
          </div>
          <h3 class="modal-title">
            {{ quote.status === 'Contratado' ? '¡Seguro Contratado!' : 'Cotización Generada' }}
          </h3>
          <p class="modal-ref">Referencia: <strong>#VTQ-{{ String(quote.id).padStart(5, '0') }}</strong></p>
        </div>

        <div class="summary-details">
          <div class="detail-row">
            <span class="detail-label">Asegurado:</span>
            <span class="detail-val">{{ quote.first_name }} {{ quote.last_name }} ({{ quote.identification_number }})</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Destino:</span>
            <span class="detail-val">
              <img v-if="quote.destination_flag_url" :src="quote.destination_flag_url" class="mini-flag" />
              {{ quote.destination_country }} ({{ quote.destination_region }})
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Fechas:</span>
            <span class="detail-val">{{ quote.start_date }} al {{ quote.end_date }} ({{ quote.days_count }} días)</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Tarifa Base:</span>
            <span class="detail-val">${{ Number(quote.base_amount).toFixed(2) }} USD</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Recargo Región ({{ quote.surcharge_percentage }}%):</span>
            <span class="detail-val">+${{ Number(quote.surcharge_amount).toFixed(2) }} USD</span>
          </div>
          <div class="detail-row total-highlight">
            <span class="detail-label">Total a Pagar:</span>
            <span class="detail-val total-price">${{ Number(quote.total_amount).toFixed(2) }} USD</span>
          </div>
        </div>

        <div v-if="quote.status === 'Contratado'" class="contract-confirmed-banner">
          ✓ Póliza contratada y registrada en el sistema.
        </div>
      </div>

      <div class="modal-actions">
        <!-- PDF Download Button -->
        <button class="btn btn-secondary btn-action" @click="downloadPdf">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          Descargar PDF
        </button>

        <!-- Contract Insurance Button -->
        <button
          v-if="quote.status === 'Cotizado'"
          class="btn btn-success btn-action"
          :disabled="store.actionLoading"
          @click="contractInsurance"
        >
          <span v-if="store.actionLoading">Procesando...</span>
          <span v-else>✓ Confirmar y Contratar Seguro</span>
        </button>

        <button v-else class="btn btn-primary btn-action" @click="$emit('close')">
          Listo
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useQuotationStore } from '../stores/quotationStore';

const props = defineProps({
  quote: {
    type: Object,
    default: null,
  },
});

defineEmits(['close']);
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
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 16px;
}

.modal-card {
  background-color: #ffffff;
  border-radius: var(--radius-lg);
  max-width: 500px;
  width: 100%;
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  animation: modalFadeIn 0.2s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color);
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: var(--text-muted);
  cursor: pointer;
}

.modal-content {
  padding: 24px;
}

.congrats-header {
  text-align: center;
  margin-bottom: 20px;
}
.icon-circle {
  font-size: 32px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: var(--color-primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 12px;
}
.modal-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--color-accent);
}
.modal-ref {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 4px;
}

.summary-details {
  background-color: #f8fafc;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}
.detail-label {
  color: var(--text-muted);
}
.detail-val {
  font-weight: 600;
  color: var(--text-main);
  display: flex;
  align-items: center;
  gap: 6px;
}

.mini-flag {
  width: 20px;
  height: 14px;
  object-fit: cover;
  border-radius: 2px;
}

.total-highlight {
  border-top: 1px solid var(--border-color);
  padding-top: 10px;
  margin-top: 4px;
}
.total-price {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--color-primary);
}

.contract-confirmed-banner {
  margin-top: 16px;
  padding: 10px;
  background-color: var(--color-success-light);
  color: #15803d;
  font-weight: 600;
  text-align: center;
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
}

.modal-actions {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  background-color: #f8fafc;
  border-top: 1px solid var(--border-color);
}
.btn-action {
  flex: 1;
}
</style>

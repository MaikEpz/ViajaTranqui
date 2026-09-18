<template>
  <div class="app-layout">
    <Navbar />

    <main class="container main-content">
      <!-- Alertas Globales -->
      <div v-if="store.errorMessage" class="alert alert-danger">
        <span>{{ store.errorMessage }}</span>
        <button class="alert-close" @click="store.clearAlerts">✕</button>
      </div>

      <div v-if="store.successMessage" class="alert alert-success">
        <span>✓ {{ store.successMessage }}</span>
        <button class="alert-close" @click="store.clearAlerts">✕</button>
      </div>

      <!-- Pestaña 1: Nueva Cotización -->
      <section v-if="store.activeTab === 'create'">
        <!-- Hero Editorial con Cápsula de Búsqueda Flotante -->
        <TravelHero
          @selectDestination="onSelectDestination"
          @updateDates="onUpdateDates"
        />

        <!-- Formulario Detallado & Resumen de Cotización -->
        <QuoteForm ref="quoteFormRef" @quoteCreated="onQuoteCreated" />
      </section>

      <!-- Pestaña 2: Consulta y Gestión de Seguros -->
      <section v-else-if="store.activeTab === 'list'">
        <QuotationsList />
      </section>
    </main>

    <!-- Modal de Resultado / Certificado de Póliza -->
    <QuoteResultModal
      v-if="modalQuote"
      :quote="modalQuote"
      @close="closeModal"
    />

    <footer class="app-footer">
      <div class="container footer-container">
        <div class="footer-brand-row">
          <span class="footer-logo">ViajaTranqui</span>
          <span class="footer-divider">•</span>
          <span>Cobertura médica internacional 24/7 en más de 190 países</span>
        </div>
        <p class="footer-sub">
          Sistema de Cotización y Venta de Seguro de Viaje • Laravel 11 + Vue 3 + TypeScript
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useQuotationStore } from './stores/quotationStore';
import Navbar from './components/Navbar.vue';
import TravelHero from './components/TravelHero.vue';
import QuoteForm from './components/QuoteForm.vue';
import QuoteResultModal from './components/QuoteResultModal.vue';
import QuotationsList from './components/QuotationsList.vue';
import type { Quotation } from './types/quotation';

const store = useQuotationStore();
const modalQuote = ref<Quotation | null>(null);
const quoteFormRef = ref<InstanceType<typeof QuoteForm> | null>(null);

function onSelectDestination(countryCode: string): void {
  if (quoteFormRef.value) {
    quoteFormRef.value.selectCountryByCode(countryCode);
  }
}

function onUpdateDates(payload: { start: string; end: string }): void {
  if (quoteFormRef.value) {
    quoteFormRef.value.setDates(payload.start, payload.end);
  }
}

function onQuoteCreated(quotation: Quotation): void {
  modalQuote.value = quotation;
}

function closeModal(): void {
  modalQuote.value = null;
}
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  padding-top: 32px;
  padding-bottom: 64px;
}

.alert-close {
  background: transparent;
  border: none;
  font-size: 1.15rem;
  cursor: pointer;
  color: inherit;
  margin-left: 14px;
  opacity: 0.65;
  transition: opacity 0.15s ease;
}
.alert-close:hover {
  opacity: 1;
}

.app-footer {
  background-color: #ffffff;
  border-top: 1px solid #f0f0f0;
  padding: 36px 0;
  margin-top: auto;
  text-align: center;
  font-size: 0.88rem;
  color: var(--text-muted);
}

.footer-brand-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-weight: 600;
  color: #111111;
  flex-wrap: wrap;
}

.footer-logo {
  font-weight: 800;
  letter-spacing: -0.02em;
}

.footer-divider {
  color: #d4d4d4;
}

.footer-sub {
  font-size: 0.8rem;
  color: #a1a1aa;
  margin-top: 8px;
}
</style>

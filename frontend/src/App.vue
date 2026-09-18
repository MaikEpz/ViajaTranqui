<template>
  <div class="app-layout">
    <Navbar />

    <main class="container main-content">
      <!-- Alertas Globales -->
      <div v-if="store.errorMessage" class="alert alert-danger">
        <span>⚠️ {{ store.errorMessage }}</span>
        <button class="alert-close" @click="store.clearAlerts">✕</button>
      </div>

      <div v-if="store.successMessage" class="alert alert-success">
        <span>✓ {{ store.successMessage }}</span>
        <button class="alert-close" @click="store.clearAlerts">✕</button>
      </div>

      <!-- Pestaña 1: Nueva Cotización de Viaje -->
      <section v-if="store.activeTab === 'create'">
        <!-- Hero de Viajes con Sugerencias y Beneficios -->
        <TravelHero @selectDestination="onSelectDestination" />
        <QuoteForm ref="quoteFormRef" @quoteCreated="onQuoteCreated" />
      </section>

      <!-- Pestaña 2: Listado y Gestión de Seguros -->
      <section v-else-if="store.activeTab === 'list'">
        <QuotationsList />
      </section>
    </main>

    <!-- Modal de Resultado / Voucher de Póliza -->
    <QuoteResultModal
      v-if="modalQuote"
      :quote="modalQuote"
      @close="closeModal"
    />

    <footer class="app-footer">
      <div class="container footer-container">
        <div class="footer-brand-row">
          <span class="footer-logo">✈️ ViajaTranqui Seguros</span>
          <span class="footer-divider">•</span>
          <span>Protección médica internacional 24/7 en más de 190 países</span>
        </div>
        <p class="footer-sub">
          Sistema de Cotización y Venta de Seguro de Viaje • Laravel 11 + Vue 3 + TypeScript + Docker
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
  padding-top: 36px;
  padding-bottom: 56px;
}

.alert-close {
  background: transparent;
  border: none;
  font-size: 1.15rem;
  cursor: pointer;
  color: inherit;
  margin-left: 14px;
  opacity: 0.75;
  transition: opacity 0.15s ease;
}
.alert-close:hover {
  opacity: 1;
}

.app-footer {
  background-color: #ffffff;
  border-top: 1px solid var(--border-color);
  padding: 30px 0;
  margin-top: auto;
  text-align: center;
  font-size: 0.88rem;
  color: var(--text-muted);
}

.footer-brand-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-weight: 700;
  color: var(--color-accent);
  flex-wrap: wrap;
}

.footer-logo {
  color: var(--color-primary);
}

.footer-divider {
  color: #cbd5e1;
}

.footer-sub {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 6px;
}
</style>

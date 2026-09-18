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

      <!-- VISTA 1: HOME / PRINCIPAL (Estilo Editorial Minimalista) -->
      <section v-if="store.activeTab === 'home'">
        <!-- Hero Editorial con Cápsula Elevada y CTA Único de Cotización -->
        <TravelHero
          @selectDestination="onSelectDestination"
          @updateDates="onUpdateDates"
        />

        <!-- Estructura y Tarifas de Seguros en Vista Principal -->
        <InsurancePricingSection
          :livePreview="livePreview"
          :liveCountryName="liveCountryName"
          @requestQuote="onPriceQuoteRequest"
        />
      </section>

      <!-- VISTA 2: CHECKOUT / EMISIÓN Y REVISIÓN (Nueva Vista dentro de la misma página) -->
      <section v-else-if="store.activeTab === 'checkout'">
        <CheckoutView @quoteCreated="onQuoteCreated" />
      </section>

      <!-- VISTA 3: CONSULTAR SEGUROS Y MÉTRICAS -->
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
import { ref, onMounted } from 'vue';
import { useQuotationStore } from './stores/quotationStore';
import Navbar from './components/Navbar.vue';
import TravelHero from './components/TravelHero.vue';
import InsurancePricingSection from './components/InsurancePricingSection.vue';
import CheckoutView from './components/CheckoutView.vue';
import QuotationsList from './components/QuotationsList.vue';
import QuoteResultModal from './components/QuoteResultModal.vue';
import type { Quotation, PricingBreakdown } from './types/quotation';

const store = useQuotationStore();
const modalQuote = ref<Quotation | null>(null);
const livePreview = ref<PricingBreakdown | null>(null);
const liveCountryName = ref<string>('');

onMounted(async () => {
  await store.fetchCountries();
  refreshPreview();
});

async function refreshPreview() {
  const code = store.tripDraft?.countryCode || 'ESP';
  const country = store.countries.find((c) => c.code === code);
  if (country) {
    liveCountryName.value = `${country.name} (${country.region})`;
    const start = store.tripDraft?.startDate;
    const end = store.tripDraft?.endDate;
    if (start && end && end >= start) {
      livePreview.value = await store.calculatePreview(country.region, start, end);
    }
  }
}

function onSelectDestination(countryCode: string): void {
  store.setTripDraft({ countryCode });
  refreshPreview();
}

function onUpdateDates(payload: { start: string; end: string }): void {
  store.setTripDraft({ startDate: payload.start, endDate: payload.end });
  refreshPreview();
}

function onPriceQuoteRequest(): void {
  store.goToCheckout();
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
  border-top: 1px solid var(--border-color);
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

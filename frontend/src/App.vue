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
        <TravelHero />

        <!-- Estructura Compacta de Tarifas (Ultra-minimalista) -->
        <InsurancePricingSection />
      </section>

      <!-- VISTA 2: CONSULTAR SEGUROS Y MÉTRICAS -->
      <section v-else-if="store.activeTab === 'list'">
        <QuotationsList />
      </section>
    </main>

    <!-- MODAL POP-UP DE EMISIÓN DE PÓLIZA (Pantalla Difuminada / Backdrop Blur) -->
    <QuoteModal
      v-if="store.isQuoteModalOpen"
      @close="store.closeQuoteModal()"
    />

    <footer class="app-footer">
      <div class="container footer-container">
        <div class="footer-brand-row">
          <span class="footer-logo">ViajaTranqui</span>
          <span class="footer-divider">•</span>
          <span>Cobertura médica internacional con validez consular en más de 190 países</span>
        </div>
        <p class="footer-sub">
          Sistema de Cotización y Venta de Seguro de Viaje • Laravel 11 + Vue 3 + TypeScript
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useQuotationStore } from './stores/quotationStore';
import Navbar from './components/Navbar.vue';
import TravelHero from './components/TravelHero.vue';
import InsurancePricingSection from './components/InsurancePricingSection.vue';
import QuoteModal from './components/QuoteModal.vue';
import QuotationsList from './components/QuotationsList.vue';

const store = useQuotationStore();

onMounted(async () => {
  await store.fetchCountries();
});
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  padding-top: 24px;
  padding-bottom: 12px;
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
  padding: 22px 0;
  margin-top: 20px;
  text-align: center;
  font-size: 0.85rem;
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

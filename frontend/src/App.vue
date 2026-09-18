<template>
  <div class="app-layout">
    <Navbar />

    <main class="container main-content">
      <!-- VISTA 1: HOME / PRINCIPAL (Estilo Editorial Minimalista) -->
      <section v-if="store.activeTab === 'home'" class="view-section">
        <!-- Hero Editorial con Cápsula Elevada y CTA Único de Cotización -->
        <TravelHero />

        <!-- Estructura Compacta de Tarifas (Ultra-minimalista) -->
        <InsurancePricingSection />
      </section>

      <!-- VISTA 2: CONSULTAR SEGUROS -->
      <section v-else-if="store.activeTab === 'list'" class="view-section">
        <QuotationsList />
      </section>
    </main>

    <!-- MODAL POP-UP DE EMISIÓN DE PÓLIZA (Pantalla Difuminada / Backdrop Blur) -->
    <QuoteModal
      v-if="store.isQuoteModalOpen"
      @close="store.closeQuoteModal()"
    />

    <!-- TOAST POP-UP FLOTANTE (Esquina Inferior Derecha con Auto-dismiss) -->
    <div class="toast-container" aria-live="polite">
      <!-- Notificación de Éxito -->
      <div v-if="store.successMessage" class="toast-card toast-success">
        <div class="toast-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="toast-body">
          <span class="toast-title">Éxito</span>
          <span class="toast-message">{{ store.successMessage }}</span>
        </div>
        <button type="button" class="toast-close" title="Cerrar" @click="store.clearAlerts">✕</button>
      </div>

      <!-- Notificación de Error -->
      <div v-if="store.errorMessage" class="toast-card toast-error">
        <div class="toast-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <div class="toast-body">
          <span class="toast-title">Atención</span>
          <span class="toast-message">{{ store.errorMessage }}</span>
        </div>
        <button type="button" class="toast-close" title="Cerrar" @click="store.clearAlerts">✕</button>
      </div>
    </div>

    <footer
      class="app-footer"
      :class="{
        'footer-home-scroll': store.activeTab === 'home',
        'is-revealed': isFooterRevealed
      }"
    >
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
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useQuotationStore } from './stores/quotationStore';
import Navbar from './components/Navbar.vue';
import TravelHero from './components/TravelHero.vue';
import InsurancePricingSection from './components/InsurancePricingSection.vue';
import QuoteModal from './components/QuoteModal.vue';
import QuotationsList from './components/QuotationsList.vue';

const store = useQuotationStore();
const isFooterRevealed = ref(false);

let toastTimer: any = null;

// Auto-cerrar el toast flotante después de 4.5 segundos
watch(
  () => [store.successMessage, store.errorMessage],
  ([success, error]) => {
    if (success || error) {
      if (toastTimer) clearTimeout(toastTimer);
      toastTimer = setTimeout(() => {
        store.clearAlerts();
      }, 4500);
    }
  }
);

function handleScroll() {
  if (window.scrollY <= 30) {
    isFooterRevealed.value = false;
  } else {
    isFooterRevealed.value = true;
  }
}

onMounted(async () => {
  await store.fetchCountries();
  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  if (toastTimer) clearTimeout(toastTimer);
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
  padding-bottom: 32px;
  width: 100%;
  flex: 1;
}

.view-section {
  width: 100%;
}

/* ============================================================
   TOAST POP-UPS FLOTANTES (ESQUINA INFERIOR DERECHA)
   ============================================================ */
.toast-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}

.toast-card {
  pointer-events: auto;
  display: flex;
  align-items: center;
  gap: 12px;
  background-color: #ffffff;
  border-radius: var(--radius-lg);
  padding: 12px 16px;
  box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.16), 0 0 0 1px rgba(0, 0, 0, 0.08);
  max-width: 380px;
  min-width: 280px;
  animation: toastSlideUp 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes toastSlideUp {
  from {
    opacity: 0;
    transform: translateY(16px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.toast-icon {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-success .toast-icon {
  background-color: #f0fdf4;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.toast-error .toast-icon {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.toast-body {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.toast-title {
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #111111;
  line-height: 1.1;
}

.toast-message {
  font-size: 0.82rem;
  font-weight: 500;
  color: #444444;
  margin-top: 2px;
  line-height: 1.35;
}

.toast-close {
  background: transparent;
  border: none;
  font-size: 0.95rem;
  color: #a1a1aa;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
  transition: color 0.15s ease;
  margin-left: 4px;
}
.toast-close:hover {
  color: #111111;
}

/* ============================================================
   FOOTER
   ============================================================ */
.app-footer {
  background-color: #ffffff;
  border-top: 1px solid var(--border-color);
  padding: 22px 0;
  margin-top: 20px;
  text-align: center;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.footer-home-scroll {
  opacity: 0;
  transform: translateY(24px);
  transition:
    opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
  pointer-events: none;
}

.footer-home-scroll.is-revealed {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
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

@media (max-width: 600px) {
  .toast-container {
    bottom: 16px;
    right: 16px;
    left: 16px;
  }
  .toast-card {
    min-width: unset;
    max-width: 100%;
  }
}
</style>

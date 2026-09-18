<template>
  <div class="app-layout">
    <Navbar />

    <main class="container main-content">
      <!-- Global Alerts -->
      <div v-if="store.errorMessage" class="alert alert-danger">
        <span>⚠️ {{ store.errorMessage }}</span>
        <button class="alert-close" @click="store.clearAlerts">✕</button>
      </div>

      <div v-if="store.successMessage" class="alert alert-success">
        <span>✓ {{ store.successMessage }}</span>
        <button class="alert-close" @click="store.clearAlerts">✕</button>
      </div>

      <!-- Tab 1: Quote Form -->
      <section v-if="store.activeTab === 'create'">
        <QuoteForm @quoteCreated="onQuoteCreated" />
      </section>

      <!-- Tab 2: Quotations List -->
      <section v-else-if="store.activeTab === 'list'">
        <QuotationsList />
      </section>
    </main>

    <!-- Modal for Result -->
    <QuoteResultModal
      v-if="modalQuote"
      :quote="modalQuote"
      @close="closeModal"
    />

    <footer class="app-footer">
      <div class="container footer-container">
        <p>© 2026 ViajaTranqui Seguros - Sistema de Cotización y Venta de Seguro de Viaje</p>
        <p class="footer-sub">Desarrollado con Laravel 11, Vue 3, MySQL y Docker</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useQuotationStore } from './stores/quotationStore';
import Navbar from './components/Navbar.vue';
import QuoteForm from './components/QuoteForm.vue';
import QuoteResultModal from './components/QuoteResultModal.vue';
import QuotationsList from './components/QuotationsList.vue';

const store = useQuotationStore();
const modalQuote = ref(null);

function onQuoteCreated(quotation) {
  modalQuote.value = quotation;
}

function closeModal() {
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
  padding-bottom: 48px;
}

.alert-close {
  background: transparent;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: inherit;
  margin-left: 12px;
}

.app-footer {
  background-color: #ffffff;
  border-top: 1px solid var(--border-color);
  padding: 24px 0;
  margin-top: auto;
  text-align: center;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.footer-sub {
  font-size: 0.78rem;
  color: #94a3b8;
  margin-top: 4px;
}
</style>

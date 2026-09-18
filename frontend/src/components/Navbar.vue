<template>
  <header class="navbar">
    <div class="container navbar-container">
      <div class="brand">
        <div class="brand-icon">✈️</div>
        <div>
          <span class="brand-name">ViajaTranqui</span>
          <span class="brand-tag">Seguro de Viaje</span>
        </div>
      </div>

      <nav class="nav-tabs">
        <button
          class="nav-tab-btn"
          :class="{ active: store.activeTab === 'create' }"
          @click="store.activeTab = 'create'"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          Nueva Cotización
        </button>

        <button
          class="nav-tab-btn"
          :class="{ active: store.activeTab === 'list' }"
          @click="navigateToList"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Consultar Seguros
        </button>
      </nav>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useQuotationStore } from '../stores/quotationStore';

const store = useQuotationStore();

function navigateToList() {
  store.activeTab = 'list';
  store.fetchQuotations();
}
</script>

<style scoped>
.navbar {
  background-color: #ffffff;
  border-bottom: 1px solid var(--border-color);
  padding: 14px 0;
  position: sticky;
  top: 0;
  z-index: 40;
  box-shadow: var(--shadow-sm);
}

.navbar-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-icon {
  font-size: 24px;
  background-color: var(--color-primary-light);
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-name {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--color-accent);
  display: block;
  line-height: 1.1;
}

.brand-tag {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 500;
}

.nav-tabs {
  display: flex;
  gap: 8px;
  background-color: #f1f5f9;
  padding: 4px;
  border-radius: var(--radius-md);
}

.nav-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-muted);
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.15s ease;
}

.nav-tab-btn.active {
  background-color: #ffffff;
  color: var(--color-primary);
  box-shadow: var(--shadow-sm);
}

@media (max-width: 640px) {
  .navbar-container {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
  .nav-tabs {
    width: 100%;
  }
  .nav-tab-btn {
    flex: 1;
    justify-content: center;
  }
}
</style>

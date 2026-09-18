<template>
  <header class="navbar">
    <div class="container navbar-container">
      <div class="brand" @click="store.activeTab = 'create'">
        <div class="brand-icon">
          <span class="airplane-emoji">✈️</span>
        </div>
        <div class="brand-info">
          <span class="brand-name">ViajaTranqui</span>
          <span class="brand-tag">Seguro de Viaje Internacional</span>
        </div>
      </div>

      <nav class="nav-tabs">
        <button
          class="nav-tab-btn"
          :class="{ active: store.activeTab === 'create' }"
          @click="store.activeTab = 'create'"
        >
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          Nueva Cotización
        </button>

        <button
          class="nav-tab-btn"
          :class="{ active: store.activeTab === 'list' }"
          @click="navigateToList"
        >
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Consultar Seguros
          <span v-if="store.quotations.length > 0" class="tab-badge">{{ store.meta.total }}</span>
        </button>
      </nav>

      <!-- Asistencia 24/7 Pill en Desktop -->
      <div class="nav-trust-pill">
        <span class="trust-dot"></span>
        <span class="trust-text">Soporte Médico 24/7</span>
      </div>
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
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-color);
  padding: 12px 0;
  position: sticky;
  top: 0;
  z-index: 40;
  box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
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
  cursor: pointer;
  user-select: none;
}

.brand-icon {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
  transition: transform 0.2s ease;
}
.brand:hover .brand-icon {
  transform: rotate(-6deg) scale(1.05);
}

.airplane-emoji {
  font-size: 1.4rem;
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--color-accent);
  letter-spacing: -0.4px;
  line-height: 1.1;
}

.brand-tag {
  font-size: 0.72rem;
  color: var(--color-primary);
  font-weight: 700;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.nav-tabs {
  display: flex;
  gap: 6px;
  background-color: #f1f5f9;
  padding: 5px;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
}

.nav-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--text-muted);
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.nav-tab-btn:hover {
  color: var(--color-accent);
}

.nav-tab-btn.active {
  background-color: #ffffff;
  color: var(--color-primary);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tab-badge {
  background: var(--color-primary-light);
  color: var(--color-primary);
  font-size: 0.72rem;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: var(--radius-full);
}

.nav-trust-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 6px 14px;
  border-radius: var(--radius-full);
  font-size: 0.8rem;
  font-weight: 700;
  color: #15803d;
}

.trust-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #16a34a;
  box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
}

@media (max-width: 800px) {
  .nav-trust-pill {
    display: none;
  }
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
    padding: 8px 12px;
  }
}
</style>

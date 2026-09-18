<template>
  <header class="navbar">
    <div class="container navbar-container">
      <div class="brand" @click="store.goHome()">
        <span class="brand-logo-text">ViajaTranqui</span>
        <span class="brand-dot"></span>
        <span class="brand-sub">Insurance</span>
      </div>

      <nav class="nav-tabs">
        <button
          type="button"
          class="nav-tab-btn"
          :class="{ active: store.activeTab === 'home' }"
          @click="store.goHome()"
        >
          <span>Inicio / Cotizar</span>
        </button>

        <button
          type="button"
          class="nav-tab-btn"
          :class="{ active: store.activeTab === 'list' }"
          @click="store.goToList()"
        >
          <span>Consultar Seguros</span>
          <span v-if="store.quotations.length > 0" class="tab-badge">{{ store.meta.total }}</span>
        </button>
      </nav>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useQuotationStore } from '../stores/quotationStore';

const store = useQuotationStore();
</script>

<style scoped>
.navbar {
  background-color: #ffffff;
  border-bottom: 1px solid var(--border-color);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 40;
}

.navbar-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  user-select: none;
}

.brand-logo-text {
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #111111;
}

.brand-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background-color: #111111;
}

.brand-sub {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
}

.nav-tabs {
  display: flex;
  align-items: center;
  background-color: var(--bg-subtle);
  padding: 4px;
  border-radius: var(--radius-full);
}

.nav-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 20px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #717171;
  background: transparent;
  border: none;
  border-radius: var(--radius-full);
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  letter-spacing: -0.01em;
}

.nav-tab-btn:hover {
  color: #111111;
}

.nav-tab-btn.active {
  background-color: #ffffff;
  color: #111111;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tab-badge {
  background-color: #111111;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: var(--radius-full);
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

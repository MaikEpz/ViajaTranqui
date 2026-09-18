<template>
  <div class="minimal-pricing-bar">
    <div class="pricing-header-compact">
      <span class="pricing-label">Estructura de Tarifas</span>
      <span class="pricing-sub">Tarifa base diaria computada por días continuos y recargo regional oficial</span>
    </div>

    <div class="pricing-body-compact">
      <!-- Tarifa Base Diaria Minimalista -->
      <div class="rate-base-box">
        <span class="box-micro-label">TARIFA BASE</span>
        <div class="box-rate-val">
          <span class="currency">USD</span> $3.00 <span class="unit">/ día</span>
        </div>
      </div>

      <div class="bar-divider"></div>

      <!-- Matriz por Continente Minimalista en Pills Horizontales -->
      <div class="rate-surcharges-box">
        <span class="box-micro-label">RECARGOS POR CONTINENTE</span>
        <div class="surcharges-pills">
          <div class="s-pill">
            <span class="s-name">América del Sur</span>
            <span class="s-pct">0%</span>
          </div>
          <div class="s-pill">
            <span class="s-name">Norteamérica</span>
            <span class="s-pct">+15%</span>
          </div>
          <div class="s-pill pill-europe">
            <span class="s-name">Europa</span>
            <span class="s-pct">+20%</span>
          </div>
          <div class="s-pill">
            <span class="s-name">África</span>
            <span class="s-pct">+20%</span>
          </div>
          <div class="s-pill">
            <span class="s-name">Asia / Oceanía</span>
            <span class="s-pct">+25%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Sección puramente minimalista de tarifas informativas
</script>

<style scoped>
.minimal-pricing-bar {
  margin-top: 48px;
  margin-bottom: 48px;
  background-color: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 24px 32px;
  box-shadow: var(--shadow-sm);
}

.pricing-header-compact {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--border-subtle);
  flex-wrap: wrap;
  gap: 8px;
}

.pricing-label {
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #111111;
}

.pricing-sub {
  font-size: 0.82rem;
  color: #717171;
  font-weight: 500;
}

.pricing-body-compact {
  display: flex;
  align-items: center;
  gap: 32px;
}

.rate-base-box {
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.box-micro-label {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  margin-bottom: 4px;
}

.box-rate-val {
  font-size: 1.85rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1;
}

.box-rate-val .currency {
  font-size: 0.9rem;
  color: #717171;
  font-weight: 600;
}

.box-rate-val .unit {
  font-size: 0.82rem;
  color: #717171;
  font-weight: 500;
}

.bar-divider {
  width: 1px;
  height: 44px;
  background-color: var(--border-subtle);
  flex-shrink: 0;
}

.rate-surcharges-box {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.surcharges-pills {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.s-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  padding: 6px 14px;
  border-radius: var(--radius-full);
  font-size: 0.8rem;
  transition: border-color 0.15s ease;
}

.s-pill:hover {
  border-color: #111111;
}

.s-name {
  color: #444444;
  font-weight: 500;
}

.s-pct {
  color: #111111;
  font-weight: 700;
  font-size: 0.78rem;
  background: #ffffff;
  padding: 2px 6px;
  border-radius: var(--radius-full);
  border: 1px solid var(--border-subtle);
}

.pill-europe {
  border-color: #d1d5db;
}

@media (max-width: 860px) {
  .pricing-body-compact {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .bar-divider {
    display: none;
  }
}
</style>

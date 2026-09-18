<template>
  <div class="insurance-pricing-section">
    <div class="pricing-section-header">
      <span class="editorial-tag">Transparencia Total</span>
      <h2 class="section-title">Estructura de Tarifas y Coberturas</h2>
      <p class="section-subtitle">
        Cálculo actuarial transparente basado en los días efectivos de estancia y la matriz oficial de recargos por continente.
      </p>
    </div>

    <!-- 3 Columnas Minimalistas -->
    <div class="pricing-columns-grid">
      <!-- Columna 1: Tarifa Base -->
      <div class="pricing-card">
        <div class="card-inner-tag">Tarifa Base Diaria</div>
        <div class="huge-rate">
          <span class="currency">USD</span> $3.00
          <span class="per-unit">/ día</span>
        </div>
        <p class="rate-desc">
          Aplicable a cada día de viaje, computado de manera inclusiva desde la fecha de salida hasta la fecha de retorno.
        </p>
        <ul class="rate-checklist">
          <li>✓ Días completos de cobertura continua</li>
          <li>✓ Sin costos ocultos de emisión</li>
          <li>✓ Tarifa fija internacional</li>
        </ul>
      </div>

      <!-- Columna 2: Recargos Continentales -->
      <div class="pricing-card">
        <div class="card-inner-tag">Matriz por Continente</div>
        <div class="surcharge-table">
          <div class="surcharge-row">
            <span>América del Sur</span>
            <span class="rate-pill pill-zero">0% (Base)</span>
          </div>
          <div class="surcharge-row">
            <span>Norteamérica</span>
            <span class="rate-pill">+15%</span>
          </div>
          <div class="surcharge-row highlight-schengen">
            <span>Europa (Schengen)</span>
            <span class="rate-pill pill-europe">+20%</span>
          </div>
          <div class="surcharge-row">
            <span>África</span>
            <span class="rate-pill">+20%</span>
          </div>
          <div class="surcharge-row">
            <span>Asia & Oceanía</span>
            <span class="rate-pill">+25%</span>
          </div>
        </div>
      </div>

      <!-- Columna 3: Simulador / Ejemplo de Negocio -->
      <div class="pricing-card card-highlight">
        <div class="card-inner-tag">Simulación Oficial</div>
        
        <div v-if="livePreview" class="simulation-content">
          <div class="sim-country">
            <span class="sim-flag">✈️</span>
            <strong>{{ liveCountryName || 'Destino seleccionado' }}</strong>
          </div>
          <div class="sim-days">{{ livePreview.days_count }} días calculados</div>
          
          <div class="sim-total-box">
            <span class="sim-total-label">VALOR ESTIMADO</span>
            <div class="sim-total-amount">${{ livePreview.total_amount.toFixed(2) }} <small>USD</small></div>
          </div>

          <div class="sim-math">
            Base ${{ livePreview.base_amount.toFixed(2) }} + Recargo ${{ livePreview.surcharge_amount.toFixed(2) }}
          </div>
        </div>

        <div v-else class="simulation-content">
          <div class="sim-country">
            <span class="sim-flag">🇪🇸</span>
            <strong>Ejemplo: España (Europa)</strong>
          </div>
          <div class="sim-days">10 días de estancia</div>

          <div class="sim-total-box">
            <span class="sim-total-label">TOTAL NETO</span>
            <div class="sim-total-amount">$36.00 <small>USD</small></div>
          </div>

          <div class="sim-math">
            Base: 10d × $3 = $30.00 | Recargo 20%: $6.00
          </div>
        </div>

        <button type="button" class="btn btn-primary btn-sim-cta" @click="$emit('requestQuote')">
          Cotizar con esta tarifa ➔
        </button>
      </div>
    </div>

    <!-- Garantías Incluidas Strip -->
    <div class="included-guarantees-strip">
      <div class="guarantee-item">
        <span class="g-icon">🛡️</span>
        <div class="g-text">
          <strong>Hasta $50,000 USD</strong>
          <span>Gastos médicos y hospitalización</span>
        </div>
      </div>

      <div class="guarantee-item">
        <span class="g-icon">🏥</span>
        <div class="g-text">
          <strong>Repatriación 24/7</strong>
          <span>Sanitaria y funeraria sin límites</span>
        </div>
      </div>

      <div class="guarantee-item">
        <span class="g-icon">🧳</span>
        <div class="g-text">
          <strong>Pérdida de Equipaje</strong>
          <span>Compensación por extravío o demora</span>
        </div>
      </div>

      <div class="guarantee-item">
        <span class="g-icon">🇪🇺</span>
        <div class="g-text">
          <strong>Certificado Schengen</strong>
          <span>Válido para consulados y visados</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PricingBreakdown } from '../types/quotation';

defineProps<{
  livePreview?: PricingBreakdown | null;
  liveCountryName?: string;
}>();

defineEmits<{
  (e: 'requestQuote'): void;
}>();
</script>

<style scoped>
.insurance-pricing-section {
  margin-top: 56px;
  margin-bottom: 64px;
}

.pricing-section-header {
  margin-bottom: 32px;
}

.editorial-tag {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  margin-bottom: 6px;
}

.section-title {
  font-size: 1.85rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #111111;
  line-height: 1.2;
}

.section-subtitle {
  font-size: 0.98rem;
  color: #717171;
  max-width: 650px;
  margin-top: 6px;
  line-height: 1.5;
}

/* Grilla de Columnas */
.pricing-columns-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 36px;
}

.pricing-card {
  background-color: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 32px;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pricing-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.card-highlight {
  background-color: var(--bg-subtle);
  border-color: #111111;
}

.card-inner-tag {
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  margin-bottom: 14px;
}

.huge-rate {
  font-size: 2.8rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.04em;
  line-height: 1;
  margin-bottom: 12px;
}
.huge-rate .currency {
  font-size: 1.1rem;
  font-weight: 600;
  color: #717171;
}
.huge-rate .per-unit {
  font-size: 0.95rem;
  font-weight: 500;
  color: #717171;
}

.rate-desc {
  font-size: 0.88rem;
  color: #717171;
  line-height: 1.5;
  margin-bottom: 20px;
}

.rate-checklist {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: auto;
  font-size: 0.82rem;
  font-weight: 600;
  color: #222222;
}

/* Tabla de Recargos */
.surcharge-table {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 6px;
}
.surcharge-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.88rem;
  font-weight: 500;
  color: #222222;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border-subtle);
}
.surcharge-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.rate-pill {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: var(--radius-full);
  background: #f4f4f5;
  color: #111111;
}
.pill-zero {
  background: #f0fdf4;
  color: #15803d;
}
.pill-europe {
  background: #eff6ff;
  color: #1d4ed8;
}

/* Simulación */
.simulation-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
}

.sim-country {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1.05rem;
  color: #111111;
}
.sim-flag {
  font-size: 1.2rem;
}

.sim-days {
  font-size: 0.82rem;
  color: #717171;
}

.sim-total-box {
  background-color: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: 14px;
  text-align: center;
  margin: 6px 0;
}
.sim-total-label {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
}
.sim-total-amount {
  font-size: 1.9rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin-top: 2px;
}
.sim-total-amount small {
  font-size: 0.95rem;
  color: #717171;
}

.sim-math {
  font-size: 0.75rem;
  color: #717171;
  text-align: center;
}

.btn-sim-cta {
  width: 100%;
  margin-top: auto;
  padding: 14px 20px;
  font-size: 0.92rem;
}

/* Franja de Garantías */
.included-guarantees-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 24px 32px;
}

.guarantee-item {
  display: flex;
  align-items: center;
  gap: 12px;
}
.g-icon {
  font-size: 1.5rem;
}
.g-text {
  display: flex;
  flex-direction: column;
}
.g-text strong {
  font-size: 0.85rem;
  color: #111111;
  font-weight: 700;
}
.g-text span {
  font-size: 0.75rem;
  color: #717171;
}

@media (max-width: 960px) {
  .pricing-columns-grid {
    grid-template-columns: 1fr;
  }
  .included-guarantees-strip {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .included-guarantees-strip {
    grid-template-columns: 1fr;
  }
}
</style>

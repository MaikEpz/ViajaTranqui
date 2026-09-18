<template>
  <div
    ref="sectionRef"
    class="minimal-pricing-bar"
    :class="{ 'is-revealed': isRevealed }"
  >
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
          <div class="s-pill" style="--stagger-idx: 0;">
            <span class="s-name">América del Sur</span>
            <span class="s-pct">0%</span>
          </div>
          <div class="s-pill" style="--stagger-idx: 1;">
            <span class="s-name">Norteamérica</span>
            <span class="s-pct">+15%</span>
          </div>
          <div class="s-pill pill-europe" style="--stagger-idx: 2;">
            <span class="s-name">Europa</span>
            <span class="s-pct">+20%</span>
          </div>
          <div class="s-pill" style="--stagger-idx: 3;">
            <span class="s-name">África</span>
            <span class="s-pct">+20%</span>
          </div>
          <div class="s-pill" style="--stagger-idx: 4;">
            <span class="s-name">Asia / Oceanía</span>
            <span class="s-pct">+25%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const sectionRef = ref<HTMLElement | null>(null);
const isRevealed = ref(false);

function updateRevealState() {
  // Al llegar al tope superior de la página, desaparece de nuevo
  if (window.scrollY <= 30) {
    isRevealed.value = false;
    return;
  }

  // Al scrollear hacia abajo y entrar en viewport, aparece
  if (sectionRef.value) {
    const rect = sectionRef.value.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    if (rect.top < windowHeight - 30 && rect.bottom > 0) {
      isRevealed.value = true;
    } else if (rect.top >= windowHeight) {
      isRevealed.value = false;
    }
  }
}

onMounted(() => {
  window.addEventListener('scroll', updateRevealState, { passive: true });
  window.addEventListener('resize', updateRevealState, { passive: true });
  updateRevealState();
});

onUnmounted(() => {
  window.removeEventListener('scroll', updateRevealState);
  window.removeEventListener('resize', updateRevealState);
});
</script>

<style scoped>
.minimal-pricing-bar {
  margin-top: 36px;
  margin-bottom: 16px;
  background-color: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 24px 32px;
  box-shadow: var(--shadow-sm);

  /* Animación bidireccional: aparece al scrollear y desaparece al llegar al tope */
  opacity: 0;
  transform: translateY(38px);
  transition:
    opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: opacity, transform;
  pointer-events: none;
}

.minimal-pricing-bar.is-revealed {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
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
  opacity: 0;
  transform: translateY(16px);
  transition:
    opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.12s,
    transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.12s;
}

.minimal-pricing-bar.is-revealed .rate-base-box {
  opacity: 1;
  transform: translateY(0);
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
  opacity: 0;
  transition: opacity 0.5s ease 0.18s;
}

.minimal-pricing-bar.is-revealed .bar-divider {
  opacity: 1;
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
  transition:
    border-color 0.15s ease,
    opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  opacity: 0;
  transform: translateY(14px);
}

.minimal-pricing-bar.is-revealed .s-pill {
  opacity: 1;
  transform: translateY(0);
  transition-delay: calc(0.2s + (var(--stagger-idx, 0) * 0.06s));
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

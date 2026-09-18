<template>
  <div class="travel-hero">
    <!-- Fondo decorativo con ondas y estela de vuelo -->
    <div class="hero-backdrop">
      <div class="flight-path-glow"></div>
    </div>

    <div class="hero-content">
      <div class="hero-badge-pill">
        <span class="badge-pulse"></span>
        <span class="badge-icon">✈️</span>
        <span class="badge-text">Protección Integral de Viaje • Asistencia Global 24/7</span>
      </div>

      <h1 class="hero-title">
        Tu tranquilidad en cada <span class="highlight-word">destino del mundo</span>
      </h1>

      <p class="hero-description">
        Calcula al instante tu póliza personalizada con tarifa base de <strong>USD $3.00/día</strong>, 
        recargos oficiales por región y emisión de comprobante en PDF con validez consular Schengen.
      </p>

      <!-- Garantías / Beneficios de Viaje -->
      <div class="travel-perks-grid">
        <div class="perk-item">
          <div class="perk-icon-wrapper">🏥</div>
          <div class="perk-text">
            <strong>Asistencia Médica</strong>
            <span>Accidentes y enfermedad</span>
          </div>
        </div>

        <div class="perk-item">
          <div class="perk-icon-wrapper">🧳</div>
          <div class="perk-text">
            <strong>Equipaje Protegido</strong>
            <span>Pérdida o demoras</span>
          </div>
        </div>

        <div class="perk-item">
          <div class="perk-icon-wrapper">🇪🇺</div>
          <div class="perk-text">
            <strong>Normativa Schengen</strong>
            <span>100% Aprobada para visados</span>
          </div>
        </div>

        <div class="perk-item">
          <div class="perk-icon-wrapper">⚡</div>
          <div class="perk-text">
            <strong>Emisión Inmediata</strong>
            <span>Descarga de póliza en PDF</span>
          </div>
        </div>
      </div>

      <!-- Accesos directos a Destinos Populares -->
      <div class="popular-destinations-bar">
        <span class="destinations-label">🌍 Destinos frecuentes:</span>
        <div class="destination-chips">
          <button
            v-for="dest in popularDestinations"
            :key="dest.code"
            type="button"
            class="dest-chip"
            @click="$emit('selectDestination', dest.code)"
          >
            <span class="chip-flag">{{ dest.flag }}</span>
            <span class="chip-name">{{ dest.name }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineEmits<{
  (e: 'selectDestination', code: string): void;
}>();

const popularDestinations = [
  { name: 'España', code: 'ESP', flag: '🇪🇸' },
  { name: 'Francia', code: 'FRA', flag: '🇫🇷' },
  { name: 'Estados Unidos', code: 'USA', flag: '🇺🇸' },
  { name: 'Argentina', code: 'ARG', flag: '🇦🇷' },
  { name: 'Japón', code: 'JPN', flag: '🇯🇵' },
  { name: 'Colombia', code: 'COL', flag: '🇨🇴' },
  { name: 'Italia', code: 'ITA', flag: '🇮🇹' },
];
</script>

<style scoped>
.travel-hero {
  position: relative;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
  border-radius: var(--radius-xl);
  padding: 40px 36px;
  color: #ffffff;
  margin-bottom: 32px;
  box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25);
  overflow: hidden;
}

.hero-backdrop {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.flight-path-glow {
  position: absolute;
  top: -40%;
  right: -20%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(2, 132, 199, 0) 70%);
  border-radius: 50%;
  filter: blur(40px);
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 880px;
}

.hero-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  padding: 6px 14px;
  border-radius: var(--radius-full);
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  margin-bottom: 16px;
}

.badge-pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #38bdf8;
  box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7);
  animation: pulseDot 2s infinite;
}

@keyframes pulseDot {
  0% {
    box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(56, 189, 248, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(56, 189, 248, 0);
  }
}

.hero-title {
  font-size: 2.35rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 12px;
  letter-spacing: -0.5px;
}

.highlight-word {
  background: linear-gradient(135deg, #38bdf8 0%, #7dd3fc 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-description {
  font-size: 1.05rem;
  color: #cbd5e1;
  line-height: 1.6;
  margin-bottom: 24px;
}
.hero-description strong {
  color: #ffffff;
}

/* Grilla de Garantías de Viaje */
.travel-perks-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}

.perk-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: var(--radius-md);
  padding: 10px 12px;
  backdrop-filter: blur(4px);
  transition: background 0.2s ease, transform 0.2s ease;
}
.perk-item:hover {
  background: rgba(255, 255, 255, 0.14);
  transform: translateY(-2px);
}

.perk-icon-wrapper {
  font-size: 1.4rem;
  flex-shrink: 0;
}

.perk-text {
  display: flex;
  flex-direction: column;
}
.perk-text strong {
  font-size: 0.85rem;
  color: #ffffff;
  font-weight: 700;
}
.perk-text span {
  font-size: 0.72rem;
  color: #94a3b8;
}

/* Destinos Frecuentes */
.popular-destinations-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
}

.destinations-label {
  font-size: 0.85rem;
  color: #cbd5e1;
  font-weight: 600;
}

.destination-chips {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.dest-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  padding: 6px 12px;
  border-radius: var(--radius-full);
  font-size: 0.825rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.dest-chip:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: #38bdf8;
  transform: translateY(-1px);
}

.chip-flag {
  font-size: 1rem;
}

@media (max-width: 900px) {
  .travel-perks-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .hero-title {
    font-size: 1.85rem;
  }
}

@media (max-width: 600px) {
  .travel-hero {
    padding: 24px 20px;
  }
  .travel-perks-grid {
    grid-template-columns: 1fr;
  }
  .hero-title {
    font-size: 1.5rem;
  }
}
</style>

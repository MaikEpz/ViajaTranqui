<template>
  <div class="editorial-hero">
    <!-- Imagen de fondo cinematográfica estilo revista de viajes -->
    <div class="hero-image-wrapper">
      <img
        src="/images/travel_hero.jpg"
        alt="Editorial Travel Background"
        class="hero-bg-img"
      />
      <div class="hero-scrim"></div>
    </div>

    <!-- Contenido Editorial Centrado -->
    <div class="hero-inner-content">
      <h1 class="editorial-headline">
        Tu tranquilidad en cada destino
      </h1>
      <p class="editorial-subheading">
        Las ciudades que sueñas, los rincones que descubres: viaja seguro con asistencia médica internacional y validez consular.
      </p>

      <div class="hero-center-cta">
        <button type="button" class="editorial-pill-btn" @click="scrollToForm">
          Cotizar ahora
        </button>
      </div>
    </div>

    <!-- Barra Cápsula Flotante (Inspirada en Mr & Mrs Smith / Airbnb Luxe) -->
    <div class="floating-capsule-wrapper">
      <div class="search-capsule">
        <!-- Columna 1: DESTINO -->
        <div class="capsule-col col-destination">
          <label class="col-label" for="capsule_dest">DESTINO</label>
          <select
            id="capsule_dest"
            v-model="selectedCode"
            class="capsule-input-select"
            @change="onCountrySelect"
          >
            <option value="" disabled>¿A dónde deseas viajar?</option>
            <option
              v-for="country in store.countries"
              :key="country.code"
              :value="country.code"
            >
              {{ country.name }} ({{ country.region }})
            </option>
          </select>
        </div>

        <div class="capsule-divider"></div>

        <!-- Columna 2: FECHAS -->
        <div class="capsule-col col-dates">
          <label class="col-label">FECHAS</label>
          <div class="dates-inline">
            <input
              v-model="startDate"
              type="date"
              class="capsule-date-input"
              :min="todayDate"
              title="Fecha de salida"
              @change="emitDates"
            />
            <span class="date-arrow">→</span>
            <input
              v-model="endDate"
              type="date"
              class="capsule-date-input"
              :min="startDate || todayDate"
              title="Fecha de regreso"
              @change="emitDates"
            />
          </div>
        </div>

        <div class="capsule-divider"></div>

        <!-- Columna 3: VIAJERO / COBERTURA -->
        <div class="capsule-col col-guests">
          <label class="col-label">COBERTURA</label>
          <div class="guests-text">
            <span>1 Viajero (USD $3.00/día)</span>
          </div>
        </div>

        <!-- Botón Píldora Negra con Flecha -->
        <div class="capsule-col col-action">
          <button type="button" class="capsule-action-btn" @click="scrollToForm">
            <span>Cotizar</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useQuotationStore } from '../stores/quotationStore';

const emit = defineEmits<{
  (e: 'selectDestination', code: string): void;
  (e: 'updateDates', payload: { start: string; end: string }): void;
}>();

const store = useQuotationStore();

const todayDate = new Date().toISOString().split('T')[0];
const selectedCode = ref<string>('');
const startDate = ref<string>('');
const endDate = ref<string>('');

function onCountrySelect() {
  if (selectedCode.value) {
    emit('selectDestination', selectedCode.value);
  }
}

function emitDates() {
  emit('updateDates', { start: startDate.value, end: endDate.value });
}

function scrollToForm() {
  const formElement = document.getElementById('quotation-interactive-form');
  if (formElement) {
    formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
</script>

<style scoped>
.editorial-hero {
  position: relative;
  border-radius: var(--radius-xl);
  overflow: visible;
  margin-bottom: 72px;
}

.hero-image-wrapper {
  position: relative;
  width: 100%;
  height: 480px;
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.12);
}

.hero-bg-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transform: scale(1.02);
  transition: transform 6s cubic-bezier(0.16, 1, 0.3, 1);
}
.editorial-hero:hover .hero-bg-img {
  transform: scale(1.05);
}

.hero-scrim {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    rgba(0, 0, 0, 0.25) 0%,
    rgba(0, 0, 0, 0.45) 50%,
    rgba(0, 0, 0, 0.65) 100%
  );
}

.hero-inner-content {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #ffffff;
  padding: 0 24px;
  z-index: 2;
}

.editorial-headline {
  font-size: 3.2rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.15;
  max-width: 820px;
  margin-bottom: 14px;
  color: #ffffff;
  text-shadow: 0 2px 14px rgba(0, 0, 0, 0.3);
}

.editorial-subheading {
  font-size: 1.15rem;
  font-weight: 400;
  max-width: 680px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 24px;
  text-shadow: 0 1px 8px rgba(0, 0, 0, 0.3);
}

.hero-center-cta {
  display: flex;
  justify-content: center;
}

.editorial-pill-btn {
  background-color: #000000;
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 12px 28px;
  border-radius: var(--radius-full);
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  letter-spacing: -0.01em;
}
.editorial-pill-btn:hover {
  background-color: #ffffff;
  color: #000000;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

/* ============================================================
   LA CÁPSULA FLOTANTE DE BÚSQUEDA (ESTILO MR & MRS SMITH / AIRBNB)
   ============================================================ */
.floating-capsule-wrapper {
  position: absolute;
  left: 0;
  right: 0;
  bottom: -40px;
  display: flex;
  justify-content: center;
  padding: 0 20px;
  z-index: 10;
}

.search-capsule {
  display: flex;
  align-items: center;
  background-color: #ffffff;
  border-radius: var(--radius-full);
  box-shadow: var(--shadow-capsule);
  border: 1px solid rgba(0, 0, 0, 0.08);
  padding: 8px 10px 8px 32px;
  width: 100%;
  max-width: 960px;
  transition: box-shadow 0.2s ease;
}

.search-capsule:hover {
  box-shadow: 0 24px 50px -10px rgba(0, 0, 0, 0.22);
}

.capsule-col {
  display: flex;
  flex-direction: column;
}

.col-destination {
  flex: 1.4;
  padding-right: 16px;
}

.col-dates {
  flex: 1.5;
  padding: 0 16px;
}

.col-guests {
  flex: 1.1;
  padding: 0 16px;
}

.col-action {
  flex-shrink: 0;
}

.col-label {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #111111;
  margin-bottom: 2px;
}

.capsule-input-select {
  border: none;
  background: transparent;
  font-size: 0.92rem;
  font-weight: 500;
  color: #111111;
  padding: 4px 0;
  outline: none;
  cursor: pointer;
  width: 100%;
  font-family: inherit;
  border-bottom: 1px solid #e5e5e5;
  transition: border-color 0.15s ease;
}
.capsule-input-select:focus {
  border-bottom-color: #111111;
}

.dates-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #e5e5e5;
  padding-bottom: 3px;
}
.capsule-date-input {
  border: none;
  background: transparent;
  font-size: 0.88rem;
  font-weight: 500;
  color: #111111;
  outline: none;
  font-family: inherit;
  cursor: pointer;
  flex: 1;
}
.date-arrow {
  font-size: 0.8rem;
  color: #888888;
}

.guests-text {
  border-bottom: 1px solid #e5e5e5;
  padding-bottom: 4px;
  font-size: 0.88rem;
  font-weight: 500;
  color: #444444;
  white-space: nowrap;
}

.capsule-divider {
  width: 1px;
  height: 38px;
  background-color: #eaeaea;
}

.capsule-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background-color: #000000;
  color: #ffffff;
  border: none;
  border-radius: var(--radius-full);
  padding: 14px 28px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  letter-spacing: -0.01em;
}

.capsule-action-btn:hover {
  background-color: #222222;
  transform: scale(1.03);
}

@media (max-width: 900px) {
  .search-capsule {
    border-radius: var(--radius-lg);
    flex-direction: column;
    padding: 20px;
    gap: 16px;
  }
  .capsule-divider {
    display: none;
  }
  .capsule-col {
    width: 100%;
    padding: 0 !important;
  }
  .capsule-action-btn {
    width: 100%;
    justify-content: center;
  }
  .floating-capsule-wrapper {
    position: static;
    margin-top: -30px;
  }
  .editorial-hero {
    margin-bottom: 30px;
  }
  .editorial-headline {
    font-size: 2.2rem;
  }
}
</style>

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

    <!-- Contenido Editorial Centrado (Sin botón redundante, estilo minimalista puro) -->
    <div class="hero-inner-content">
      <h1 class="editorial-headline">
        Tu tranquilidad en cada destino
      </h1>
      <p class="editorial-subheading">
        Las ciudades que sueñas, los rincones que descubres: viaja seguro con asistencia médica internacional y validez consular.
      </p>
    </div>

    <!-- Barra Cápsula Flotante (Elevada más arriba, única acción principal de cotización) -->
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

        <!-- Botón Píldora Negra con Flecha (El ÚNICO Call To Action) -->
        <div class="capsule-col col-action">
          <button
            type="button"
            id="btn-capsule-cotizar"
            class="capsule-action-btn"
            @click="onCotizarClick"
          >
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
import { ref, onMounted } from 'vue';
import { useQuotationStore } from '../stores/quotationStore';

const emit = defineEmits<{
  (e: 'selectDestination', code: string): void;
  (e: 'updateDates', payload: { start: string; end: string }): void;
}>();

const store = useQuotationStore();

const todayDate = new Date().toISOString().split('T')[0];
const selectedCode = ref<string>(store.tripDraft?.countryCode || '');
const startDate = ref<string>(store.tripDraft?.startDate || '');
const endDate = ref<string>(store.tripDraft?.endDate || '');

onMounted(async () => {
  await store.fetchCountries();
  if (!selectedCode.value && store.countries.length > 0) {
    selectedCode.value = 'ESP'; // España por defecto según requerimiento técnico
    store.setTripDraft({ countryCode: 'ESP' });
    emit('selectDestination', 'ESP');
  }
  if (!startDate.value) {
    const d1 = new Date();
    d1.setDate(d1.getDate() + 7);
    const d2 = new Date(d1);
    d2.setDate(d2.getDate() + 9);
    startDate.value = d1.toISOString().split('T')[0];
    endDate.value = d2.toISOString().split('T')[0];
    store.setTripDraft({ startDate: startDate.value, endDate: endDate.value });
    emit('updateDates', { start: startDate.value, end: endDate.value });
  }
});

function onCountrySelect() {
  if (selectedCode.value) {
    store.setTripDraft({ countryCode: selectedCode.value });
    emit('selectDestination', selectedCode.value);
  }
}

function emitDates() {
  store.setTripDraft({ startDate: startDate.value, endDate: endDate.value });
  emit('updateDates', { start: startDate.value, end: endDate.value });
}

function onCotizarClick() {
  // Abre el pop-up emergente sobre la pantalla difuminada
  store.openQuoteModal(selectedCode.value, startDate.value, endDate.value);
}
</script>

<style scoped>
.editorial-hero {
  position: relative;
  border-radius: var(--radius-xl);
  overflow: visible;
  margin-bottom: 54px;
}

.hero-image-wrapper {
  position: relative;
  width: 100%;
  height: 460px;
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
    rgba(0, 0, 0, 0.22) 0%,
    rgba(0, 0, 0, 0.40) 50%,
    rgba(0, 0, 0, 0.65) 100%
  );
}

.hero-inner-content {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 45px;
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
  font-size: 3.3rem;
  font-weight: 800;
  letter-spacing: -0.035em;
  line-height: 1.12;
  max-width: 840px;
  margin-bottom: 16px;
  color: #ffffff;
  text-shadow: 0 2px 14px rgba(0, 0, 0, 0.3);
}

.editorial-subheading {
  font-size: 1.15rem;
  font-weight: 400;
  max-width: 680px;
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.94);
  text-shadow: 0 1px 8px rgba(0, 0, 0, 0.3);
}

/* ============================================================
   LA CÁPSULA FLOTANTE DE BÚSQUEDA (SUBIDA MÁS ARRIBA)
   ============================================================ */
.floating-capsule-wrapper {
  position: absolute;
  left: 0;
  right: 0;
  bottom: -22px; /* Subida para que monte limpiamente sobre el límite del hero */
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
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.06);
  padding: 8px 10px 8px 32px;
  width: 100%;
  max-width: 960px;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.search-capsule:hover {
  box-shadow: 0 26px 55px -10px rgba(0, 0, 0, 0.24), 0 0 0 1px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
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
  font-weight: 600;
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
  font-weight: 600;
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
  font-weight: 600;
  color: #333333;
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
  background-color: #262626;
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
    margin-top: -20px;
  }
  .editorial-hero {
    margin-bottom: 30px;
  }
  .editorial-headline {
    font-size: 2.2rem;
  }
}
</style>

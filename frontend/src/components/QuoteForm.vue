<template>
  <div class="quote-form-layout">
    <!-- Form Card -->
    <div class="card form-card">
      <div class="card-header">
        <div class="header-tag">
          <span class="tag-plane">✈️</span>
          <span>Configurador de Póliza de Viaje</span>
        </div>
        <h2 class="card-title">Cotizar Seguro de Viaje Internacional</h2>
        <p class="card-subtitle">
          Personalice su cobertura completando los datos del pasajero y su itinerario de viaje.
        </p>
      </div>

      <form @submit.prevent="handleSubmit" novalidate>
        <!-- 1. Datos del Asegurado -->
        <div class="section-block">
          <div class="section-header">
            <span class="step-num">1</span>
            <div>
              <h3>Pasajero Principal (Asegurado)</h3>
              <p class="section-subtext">Datos oficiales para la emisión legal del comprobante</p>
            </div>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label" for="first_name">
                Nombres <span class="required">*</span>
              </label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.first_name }"
                placeholder="Ej. Carlos Andrés"
                required
              />
              <span v-if="errors.first_name" class="form-error">⚠️ {{ errors.first_name }}</span>
            </div>

            <div class="form-group">
              <label class="form-label" for="last_name">
                Apellidos <span class="required">*</span>
              </label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.last_name }"
                placeholder="Ej. Mendoza Morales"
                required
              />
              <span v-if="errors.last_name" class="form-error">⚠️ {{ errors.last_name }}</span>
            </div>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label" for="identification_number">
                N° de Identificación / Pasaporte / DNI <span class="required">*</span>
              </label>
              <input
                id="identification_number"
                v-model="form.identification_number"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.identification_number }"
                placeholder="Ej. 1728193842"
                required
              />
              <span v-if="errors.identification_number" class="form-error">⚠️ {{ errors.identification_number }}</span>
            </div>

            <div class="form-group">
              <label class="form-label" for="birth_date">
                Fecha de Nacimiento <span class="required">*</span>
              </label>
              <input
                id="birth_date"
                v-model="form.birth_date"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.birth_date }"
                :max="todayDate"
                required
              />
              <span v-if="errors.birth_date" class="form-error">⚠️ {{ errors.birth_date }}</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">
              Correo Electrónico para envío de póliza <span class="required">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-control"
              :class="{ 'is-invalid': errors.email }"
              placeholder="Ej. carlos.mendoza@email.com"
              required
            />
            <span v-if="errors.email" class="form-error">⚠️ {{ errors.email }}</span>
          </div>
        </div>

        <!-- 2. Datos del Viaje e Itinerario -->
        <div class="section-block">
          <div class="section-header">
            <span class="step-num">2</span>
            <div>
              <h3>Itinerario y Destino del Viaje</h3>
              <p class="section-subtext">Seleccione país receptor y fechas de salida y retorno</p>
            </div>
          </div>

          <!-- Selector de País con sugerencias rápidas -->
          <div class="form-group">
            <label class="form-label" for="destination_country">
              País de Destino (Catálogo Oficial REST Countries) <span class="required">*</span>
            </label>

            <!-- Píldoras de selección veloz -->
            <div class="quick-dest-pills">
              <span class="pills-title">Sugerencias:</span>
              <button
                v-for="quick in quickCountries"
                :key="quick.code"
                type="button"
                class="quick-pill"
                :class="{ active: selectedCountryCode === quick.code }"
                @click="selectCountryByCode(quick.code)"
              >
                {{ quick.flag }} {{ quick.name }}
              </button>
            </div>

            <div class="country-select-wrapper">
              <select
                id="destination_country"
                v-model="selectedCountryCode"
                class="form-control country-select"
                :class="{ 'is-invalid': errors.destination_country }"
                @change="onCountryChange"
                required
              >
                <option value="" disabled>Seleccione un país de destino del mundo...</option>
                <option
                  v-for="country in store.countries"
                  :key="country.code"
                  :value="country.code"
                >
                  {{ country.name }} ({{ country.region }})
                </option>
              </select>

              <!-- Tarjeta visual de país seleccionado con bandera de alta resolución -->
              <div v-if="selectedCountry" class="country-preview-card">
                <img
                  v-if="selectedCountry.flag_url"
                  :src="selectedCountry.flag_url"
                  :alt="selectedCountry.name"
                  class="flag-img"
                />
                <div class="country-preview-info">
                  <div class="country-title-row">
                    <span class="country-preview-name">{{ selectedCountry.name }}</span>
                    <span class="country-iso-badge">ISO: {{ selectedCountry.code }}</span>
                  </div>
                  <div class="country-region-meta">
                    <span class="region-badge">Región: {{ selectedCountry.region }}</span>
                    <span class="surcharge-notice">
                      Recargo tarifario: {{ getRegionSurchargeText(selectedCountry.region) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <span v-if="errors.destination_country" class="form-error">⚠️ {{ errors.destination_country }}</span>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label" for="start_date">
                Fecha de Salida <span class="required">*</span>
              </label>
              <input
                id="start_date"
                v-model="form.start_date"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.start_date }"
                :min="todayDate"
                @change="triggerRecalculation"
                required
              />
              <span v-if="errors.start_date" class="form-error">⚠️ {{ errors.start_date }}</span>
            </div>

            <div class="form-group">
              <label class="form-label" for="end_date">
                Fecha de Regreso <span class="required">*</span>
              </label>
              <input
                id="end_date"
                v-model="form.end_date"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.end_date }"
                :min="form.start_date || todayDate"
                @change="triggerRecalculation"
                required
              />
              <span v-if="errors.end_date" class="form-error">⚠️ {{ errors.end_date }}</span>
            </div>
          </div>

          <!-- Chip interactivo de duración si las fechas son válidas -->
          <div v-if="preview" class="itinerary-duration-banner">
            <div class="duration-icon">🗓️</div>
            <div class="duration-text">
              <strong>Itinerario confirmado: {{ preview.days_count }} días de viaje continuo</strong>
              <span>Desde el {{ formatDate(form.start_date) }} hasta el {{ formatDate(form.end_date) }}</span>
            </div>
          </div>
        </div>

        <button
          type="submit"
          class="btn btn-primary btn-submit"
          :disabled="store.actionLoading"
        >
          <span v-if="store.actionLoading" class="btn-spinner-content">
            <span class="spinner-small"></span> Calculando y guardando póliza...
          </span>
          <span v-else class="btn-content">
            🛡️ Generar Cotización Oficial (PDF) →
          </span>
        </button>
      </form>
    </div>

    <!-- Sidebar: Tarjeta de Cotización estilo "Boarding Pass / Voucher" -->
    <div class="sidebar-pricing">
      <div class="boarding-pass-card">
        <!-- Cabecera de Ticket de Vuelo -->
        <div class="boarding-pass-header">
          <div class="pass-brand">
            <span class="pass-icon">✈️</span>
            <div>
              <span class="pass-title">VIAJATRANQUI AIRWAYS</span>
              <span class="pass-type">POLIZA DE VIAJE & COBERTURA</span>
            </div>
          </div>
          <span class="ticket-status-pill">Tarifa Oficial</span>
        </div>

        <!-- Ruta visual de viaje -->
        <div class="flight-route-strip">
          <div class="route-city">
            <span class="city-code">ORIGEN</span>
            <span class="city-name">Ecuador</span>
          </div>

          <div class="flight-arrow-indicator">
            <span class="flight-line"></span>
            <span class="plane-flight">✈</span>
            <span class="flight-line"></span>
          </div>

          <div class="route-city text-right">
            <span class="city-code">{{ selectedCountry ? selectedCountry.code : 'DEST' }}</span>
            <span class="city-name">{{ selectedCountry ? selectedCountry.name : 'Por seleccionar' }}</span>
          </div>
        </div>

        <!-- Línea perforada de ticket con muescas circulares -->
        <div class="ticket-tear-line">
          <div class="tear-notch notch-left"></div>
          <div class="tear-dashed"></div>
          <div class="tear-notch notch-right"></div>
        </div>

        <!-- Cuerpo del Desglose de Precios -->
        <div class="boarding-pass-body">
          <div v-if="preview" class="breakdown-list">
            <div class="breakdown-row">
              <span class="b-label">Días de Cobertura:</span>
              <span class="b-val highlight-days">{{ preview.days_count }} días</span>
            </div>

            <div class="breakdown-row">
              <span class="b-label">Tarifa Base ($3.00/día):</span>
              <span class="b-val">${{ preview.base_amount.toFixed(2) }} USD</span>
            </div>

            <div class="breakdown-row">
              <span class="b-label">
                Recargo {{ selectedCountry?.region }} ({{ preview.surcharge_percentage }}%):
              </span>
              <span class="b-val text-warning">+${{ preview.surcharge_amount.toFixed(2) }} USD</span>
            </div>

            <div class="pass-total-box">
              <div class="total-caption">VALOR TOTAL DE LA PÓLIZA</div>
              <div class="total-big-amount">${{ preview.total_amount.toFixed(2) }} <span class="currency">USD</span></div>
              <div class="all-inclusive-tag">✓ Tarifa neta todo incluido (Sin sorpresas)</div>
            </div>
          </div>

          <div v-else class="empty-pass-placeholder">
            <div class="compass-icon">🧭</div>
            <h4>Esperando itinerario</h4>
            <p>Seleccione el país de destino y las fechas de viaje para calcular el precio exacto.</p>
          </div>

          <!-- Beneficios incluidos de viaje -->
          <div class="included-perks-box">
            <div class="perks-title">Garantías incluidas en esta póliza:</div>
            <ul class="perks-checklist">
              <li><span class="check-icon">✓</span> Hasta $50,000 en asistencia médica internacional</li>
              <li><span class="check-icon">✓</span> Repatriación sanitaria y funeraria 24/7</li>
              <li><span class="check-icon">✓</span> Compensación por pérdida o retraso de equipaje</li>
              <li><span class="check-icon">✓</span> Aprobado y válido para visados Schengen</li>
            </ul>
          </div>

          <!-- Referencia de recargos por continente -->
          <div class="regional-surcharges-compact">
            <div class="rules-caption">Matriz oficial de recargos por continente:</div>
            <div class="rules-badges">
              <span class="rule-badge">Sudamérica: <strong>0%</strong></span>
              <span class="rule-badge">Norteamérica: <strong>15%</strong></span>
              <span class="rule-badge">Europa: <strong>20%</strong></span>
              <span class="rule-badge">África: <strong>20%</strong></span>
              <span class="rule-badge">Asia / Oceanía: <strong>25%</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useQuotationStore } from '../stores/quotationStore';
import type { Country } from '../types/country';
import type { PricingBreakdown, Quotation, QuotationPayload } from '../types/quotation';

const emit = defineEmits<{
  (e: 'quoteCreated', quotation: Quotation): void;
}>();

const store = useQuotationStore();

const todayDate = new Date().toISOString().split('T')[0];

const selectedCountryCode = ref<string>('');
const selectedCountry = ref<Country | null>(null);
const preview = ref<PricingBreakdown | null>(null);

const quickCountries = [
  { name: 'España', code: 'ESP', flag: '🇪🇸' },
  { name: 'Francia', code: 'FRA', flag: '🇫🇷' },
  { name: 'EE.UU.', code: 'USA', flag: '🇺🇸' },
  { name: 'Argentina', code: 'ARG', flag: '🇦🇷' },
  { name: 'Japón', code: 'JPN', flag: '🇯🇵' },
  { name: 'Colombia', code: 'COL', flag: '🇨🇴' },
];

const form = reactive({
  first_name: '',
  last_name: '',
  identification_number: '',
  email: '',
  birth_date: '',
  start_date: '',
  end_date: '',
});

const errors = reactive<Record<string, string>>({
  first_name: '',
  last_name: '',
  identification_number: '',
  email: '',
  birth_date: '',
  destination_country: '',
  start_date: '',
  end_date: '',
});

onMounted(async () => {
  await store.fetchCountries();
});

function selectCountryByCode(code: string) {
  selectedCountryCode.value = code;
  onCountryChange();
}

function onCountryChange() {
  selectedCountry.value = store.countries.find((c: Country) => c.code === selectedCountryCode.value) || null;
  triggerRecalculation();
}

function getRegionSurchargeText(region: string): string {
  const map: Record<string, string> = {
    'South America': '0% (Tarifa Base)',
    'North America': '+15%',
    'Europe': '+20%',
    'Asia': '+25%',
    'Oceania': '+25%',
    'Africa': '+20%',
  };
  return map[region] || '0%';
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return dateStr;
}

async function triggerRecalculation() {
  if (selectedCountry.value && form.start_date && form.end_date) {
    if (form.end_date >= form.start_date) {
      preview.value = await store.calculatePreview(
        selectedCountry.value.region,
        form.start_date,
        form.end_date
      );
    } else {
      preview.value = null;
    }
  } else {
    preview.value = null;
  }
}

function validateForm(): boolean {
  let valid = true;
  Object.keys(errors).forEach((k) => (errors[k] = ''));

  if (!form.first_name.trim()) {
    errors.first_name = 'Ingrese los nombres del asegurado.';
    valid = false;
  }
  if (!form.last_name.trim()) {
    errors.last_name = 'Ingrese los apellidos del asegurado.';
    valid = false;
  }
  if (!form.identification_number.trim()) {
    errors.identification_number = 'Ingrese el número de identificación.';
    valid = false;
  }
  if (!form.email.trim() || !form.email.includes('@')) {
    errors.email = 'Ingrese un correo electrónico válido.';
    valid = false;
  }
  if (!form.birth_date) {
    errors.birth_date = 'Seleccione la fecha de nacimiento.';
    valid = false;
  } else if (form.birth_date >= todayDate) {
    errors.birth_date = 'La fecha de nacimiento debe ser anterior a hoy.';
    valid = false;
  }

  if (!selectedCountry.value) {
    errors.destination_country = 'Seleccione el país de destino.';
    valid = false;
  }

  if (!form.start_date) {
    errors.start_date = 'Seleccione la fecha de salida.';
    valid = false;
  } else if (form.start_date < todayDate) {
    errors.start_date = 'La salida no puede ser anterior a la fecha actual.';
    valid = false;
  }

  if (!form.end_date) {
    errors.end_date = 'Seleccione la fecha de regreso.';
    valid = false;
  } else if (form.end_date < form.start_date) {
    errors.end_date = 'El regreso debe ser igual o posterior a la salida.';
    valid = false;
  }

  return valid;
}

async function handleSubmit(): Promise<void> {
  if (!validateForm() || !selectedCountry.value) return;

  const payload: QuotationPayload = {
    first_name: form.first_name,
    last_name: form.last_name,
    identification_number: form.identification_number,
    email: form.email,
    birth_date: form.birth_date,
    start_date: form.start_date,
    end_date: form.end_date,
    destination_country: selectedCountry.value.name,
    destination_country_code: selectedCountry.value.code,
    destination_region: selectedCountry.value.region,
    destination_flag_url: selectedCountry.value.flag_url,
  };

  try {
    const quotation = await store.createQuotation(payload);
    emit('quoteCreated', quotation);
  } catch (err) {
    // El error es gestionado y notificado en el store de Pinia
  }
}

defineExpose({
  selectCountryByCode,
});
</script>

<style scoped>
.quote-form-layout {
  display: grid;
  grid-template-columns: 1fr 410px;
  gap: 28px;
  align-items: start;
}

.form-card {
  background: #ffffff;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  padding: 32px;
}

.card-header {
  margin-bottom: 28px;
}

.header-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--color-sky-light);
  color: var(--color-primary);
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  margin-bottom: 8px;
  border: 1px solid rgba(2, 132, 199, 0.15);
}

.card-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--color-accent);
  letter-spacing: -0.3px;
}
.card-subtitle {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-top: 4px;
}

.section-block {
  padding-bottom: 24px;
  margin-bottom: 24px;
  border-bottom: 1px solid var(--border-color);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.step-num {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #fff;
  font-size: 0.95rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
  flex-shrink: 0;
}

.section-header h3 {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--color-accent);
}
.section-subtext {
  font-size: 0.8rem;
  color: var(--text-muted);
}

/* Píldoras de selección rápida */
.quick-dest-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}
.pills-title {
  font-size: 0.78rem;
  color: var(--text-muted);
  font-weight: 700;
}
.quick-pill {
  background: #f8fafc;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);
  padding: 4px 10px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  color: var(--text-main);
  font-family: inherit;
}
.quick-pill:hover {
  background: var(--color-sky-light);
  border-color: var(--color-primary);
  color: var(--color-primary);
}
.quick-pill.active {
  background: var(--color-primary-light);
  border-color: var(--color-primary);
  color: var(--color-primary);
  font-weight: 700;
}

.country-select-wrapper {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Tarjeta de País Seleccionado con Bandera */
.country-preview-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
  border: 1.5px solid rgba(2, 132, 199, 0.25);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  animation: fadeIn 0.2s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

.flag-img {
  width: 44px;
  height: 28px;
  object-fit: cover;
  border-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
  border: 1px solid rgba(0,0,0,0.05);
}

.country-preview-info {
  flex: 1;
}
.country-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.country-preview-name {
  font-weight: 800;
  font-size: 1.05rem;
  color: var(--color-accent);
}
.country-iso-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: #e2e8f0;
  color: #475569;
  padding: 2px 6px;
  border-radius: 4px;
}
.country-region-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.8rem;
  margin-top: 3px;
}
.region-badge {
  color: var(--color-primary);
  font-weight: 600;
}
.surcharge-notice {
  color: #d97706;
  font-weight: 700;
  background: #fef3c7;
  padding: 1px 8px;
  border-radius: var(--radius-full);
  font-size: 0.72rem;
}

/* Banner de itinerario confirmado */
.itinerary-duration-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%);
  border: 1px solid #bbf7d0;
  border-radius: var(--radius-md);
  padding: 12px 16px;
  margin-top: 14px;
  animation: fadeIn 0.25s ease;
}
.duration-icon {
  font-size: 1.5rem;
}
.duration-text {
  display: flex;
  flex-direction: column;
}
.duration-text strong {
  font-size: 0.88rem;
  color: #065f46;
}
.duration-text span {
  font-size: 0.78rem;
  color: #047857;
}

.btn-submit {
  width: 100%;
  padding: 16px;
  font-size: 1.08rem;
  margin-top: 10px;
  border-radius: var(--radius-md);
}
.btn-spinner-content {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.spinner-small {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ============================================================
   BOARDING PASS / VOUCHER DE SEGURO (SIDEBAR)
   ============================================================ */
.sidebar-pricing {
  position: sticky;
  top: 90px;
}

.boarding-pass-card {
  background: #ffffff;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-travel);
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.boarding-pass-header {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #ffffff;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.pass-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}
.pass-icon {
  font-size: 1.6rem;
}
.pass-title {
  display: block;
  font-size: 0.95rem;
  font-weight: 800;
  letter-spacing: 0.5px;
}
.pass-type {
  display: block;
  font-size: 0.68rem;
  color: #94a3b8;
  font-weight: 600;
  letter-spacing: 0.8px;
}

.ticket-status-pill {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
  padding: 3px 8px;
  border-radius: var(--radius-full);
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Franja de Ruta de Vuelo */
.flight-route-strip {
  background: #f8fafc;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
}
.route-city {
  display: flex;
  flex-direction: column;
}
.city-code {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--color-accent);
}
.city-name {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 600;
  max-width: 110px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.flight-arrow-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--color-primary);
}
.flight-line {
  width: 24px;
  height: 1.5px;
  background: #cbd5e1;
}
.plane-flight {
  font-size: 1.1rem;
}

/* Perforación de boleto aéreo con semicírculos laterales */
.ticket-tear-line {
  position: relative;
  display: flex;
  align-items: center;
  height: 20px;
  background: #ffffff;
}
.tear-dashed {
  flex: 1;
  border-top: 2px dashed #cbd5e1;
  margin: 0 16px;
}
.tear-notch {
  width: 14px;
  height: 20px;
  background-color: var(--bg-app);
  position: absolute;
}
.notch-left {
  left: 0;
  border-radius: 0 10px 10px 0;
  border-right: 1px solid var(--border-color);
}
.notch-right {
  right: 0;
  border-radius: 10px 0 0 10px;
  border-left: 1px solid var(--border-color);
}

/* Cuerpo del Boarding Pass */
.boarding-pass-body {
  padding: 20px;
}

.breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.breakdown-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
}
.b-label {
  color: var(--text-muted);
  font-size: 0.85rem;
}
.b-val {
  font-weight: 700;
  color: var(--text-main);
}
.highlight-days {
  color: var(--color-primary);
  background: var(--color-primary-light);
  padding: 2px 8px;
  border-radius: var(--radius-full);
  font-size: 0.8rem;
}

/* Caja de Total Grande */
.pass-total-box {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 1.5px solid rgba(2, 132, 199, 0.25);
  border-radius: var(--radius-md);
  padding: 16px;
  text-align: center;
  margin-top: 14px;
}
.total-caption {
  font-size: 0.72rem;
  font-weight: 800;
  color: #0369a1;
  letter-spacing: 0.8px;
}
.total-big-amount {
  font-size: 2rem;
  font-weight: 800;
  color: #0369a1;
  line-height: 1.1;
  margin: 4px 0;
}
.total-big-amount .currency {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0284c7;
}
.all-inclusive-tag {
  font-size: 0.75rem;
  color: #047857;
  font-weight: 700;
}

/* Estado de Espera / Placeholder */
.empty-pass-placeholder {
  text-align: center;
  padding: 24px 10px;
  color: var(--text-muted);
}
.compass-icon {
  font-size: 3rem;
  margin-bottom: 10px;
}
.empty-pass-placeholder h4 {
  font-size: 1rem;
  font-weight: 800;
  color: var(--color-accent);
}
.empty-pass-placeholder p {
  font-size: 0.825rem;
  margin-top: 4px;
}

/* Garantías incluidas */
.included-perks-box {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
}
.perks-title {
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--color-accent);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}
.perks-checklist {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.perks-checklist li {
  font-size: 0.78rem;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 6px;
}
.check-icon {
  color: var(--color-success);
  font-weight: 800;
}

/* Matriz de recargos compacta */
.regional-surcharges-compact {
  margin-top: 16px;
  padding: 12px;
  background-color: #f8fafc;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-color);
}
.rules-caption {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text-muted);
  margin-bottom: 6px;
  text-transform: uppercase;
}
.rules-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}
.rule-badge {
  font-size: 0.7rem;
  color: #334155;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 2px 6px;
  border-radius: 4px;
}

@media (max-width: 960px) {
  .quote-form-layout {
    grid-template-columns: 1fr;
  }
  .sidebar-pricing {
    position: static;
  }
}
</style>

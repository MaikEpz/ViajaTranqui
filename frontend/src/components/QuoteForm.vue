<template>
  <div id="quotation-interactive-form" class="quote-form-layout">
    <!-- Form Card -->
    <div class="card form-card">
      <div class="card-header">
        <span class="editorial-category">Cotizador Oficial</span>
        <h2 class="card-title">Configuración de la Póliza</h2>
        <p class="card-subtitle">
          Ingrese los datos requeridos para generar el cálculo actuarial y emitir el certificado oficial de cobertura.
        </p>
      </div>

      <form @submit.prevent="handleSubmit" novalidate>
        <!-- 1. Datos del Asegurado -->
        <div class="section-block">
          <div class="section-header">
            <span class="step-num">01</span>
            <div>
              <h3>Información del Asegurado</h3>
              <p class="section-subtext">Titular de la póliza y beneficiario principal</p>
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
              <span v-if="errors.first_name" class="form-error">{{ errors.first_name }}</span>
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
              <span v-if="errors.last_name" class="form-error">{{ errors.last_name }}</span>
            </div>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label" for="identification_number">
                Cédula / Pasaporte / DNI <span class="required">*</span>
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
              <span v-if="errors.identification_number" class="form-error">{{ errors.identification_number }}</span>
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
              <span v-if="errors.birth_date" class="form-error">{{ errors.birth_date }}</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">
              Correo Electrónico <span class="required">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-control"
              :class="{ 'is-invalid': errors.email }"
              placeholder="carlos.mendoza@email.com"
              required
            />
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>
        </div>

        <!-- 2. Datos del Viaje -->
        <div class="section-block">
          <div class="section-header">
            <span class="step-num">02</span>
            <div>
              <h3>Itinerario y Cobertura</h3>
              <p class="section-subtext">Seleccione país receptor y fechas de salida y retorno</p>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="destination_country">
              País de Destino (REST Countries API) <span class="required">*</span>
            </label>

            <div class="country-select-wrapper">
              <select
                id="destination_country"
                v-model="selectedCountryCode"
                class="form-control country-select"
                :class="{ 'is-invalid': errors.destination_country }"
                @change="onCountryChange"
                required
              >
                <option value="" disabled>Seleccione un país de destino...</option>
                <option
                  v-for="country in store.countries"
                  :key="country.code"
                  :value="country.code"
                >
                  {{ country.name }} ({{ country.region }})
                </option>
              </select>

              <!-- Vista previa limpia de país seleccionado -->
              <div v-if="selectedCountry" class="country-pill-preview">
                <img
                  v-if="selectedCountry.flag_url"
                  :src="selectedCountry.flag_url"
                  :alt="selectedCountry.name"
                  class="flag-img"
                />
                <span class="preview-country-name">{{ selectedCountry.name }}</span>
                <span class="preview-country-region">{{ selectedCountry.region }}</span>
                <span class="preview-surcharge-rate">{{ getRegionSurchargeText(selectedCountry.region) }}</span>
              </div>
            </div>
            <span v-if="errors.destination_country" class="form-error">{{ errors.destination_country }}</span>
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
              <span v-if="errors.start_date" class="form-error">{{ errors.start_date }}</span>
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
              <span v-if="errors.end_date" class="form-error">{{ errors.end_date }}</span>
            </div>
          </div>
        </div>

        <button
          type="submit"
          class="btn btn-primary btn-submit"
          :disabled="store.actionLoading"
        >
          <span v-if="store.actionLoading">Procesando cotización...</span>
          <span v-else>Generar Cotización y Póliza Oficial ➔</span>
        </button>
      </form>
    </div>

    <!-- Sidebar: Resumen Editorial Minimalista -->
    <div class="sidebar-pricing">
      <div class="card pricing-card">
        <span class="editorial-category">Resumen Actuarial</span>
        <h3 class="pricing-title">Tarifa del Seguro</h3>

        <div v-if="preview" class="breakdown-list">
          <div class="price-hero-box">
            <span class="price-hero-label">TOTAL ESTIMADO</span>
            <div class="price-hero-val">
              ${{ preview.total_amount.toFixed(2) }} <span class="currency">USD</span>
            </div>
            <span class="price-hero-sub">Todo incluido • Tarifa neta garantizada</span>
          </div>

          <div class="editorial-divider"></div>

          <div class="breakdown-row">
            <span class="b-label">Destino</span>
            <span class="b-val">{{ selectedCountry ? selectedCountry.name : '-' }}</span>
          </div>

          <div class="breakdown-row">
            <span class="b-label">Región Continental</span>
            <span class="b-val">{{ selectedCountry ? selectedCountry.region : '-' }}</span>
          </div>

          <div class="breakdown-row">
            <span class="b-label">Días de Cobertura</span>
            <span class="b-val"><strong>{{ preview.days_count }} días continuos</strong></span>
          </div>

          <div class="breakdown-row">
            <span class="b-label">Tarifa Base ($3.00/día)</span>
            <span class="b-val">${{ preview.base_amount.toFixed(2) }}</span>
          </div>

          <div class="breakdown-row">
            <span class="b-label">Recargo Continental ({{ preview.surcharge_percentage }}%)</span>
            <span class="b-val">+${{ preview.surcharge_amount.toFixed(2) }}</span>
          </div>

          <div class="editorial-divider"></div>

          <div class="minimal-perks">
            <div class="perk-line">✓ Cobertura médica internacional 24/7</div>
            <div class="perk-line">✓ Repatriación sanitaria y funeraria</div>
            <div class="perk-line">✓ Aprobado para visados Schengen</div>
            <div class="perk-line">✓ Descarga inmediata en PDF</div>
          </div>
        </div>

        <div v-else class="pricing-empty">
          <p class="empty-note">
            Seleccione el país de destino y las fechas del viaje para visualizar el desglose en tiempo real.
          </p>
        </div>

        <div class="surcharge-reference-box">
          <span class="ref-title">Recargos oficiales por región</span>
          <div class="ref-items">
            <div>América del Sur: <strong>0%</strong></div>
            <div>Norteamérica: <strong>15%</strong></div>
            <div>Europa: <strong>20%</strong></div>
            <div>Asia / Oceanía: <strong>25%</strong></div>
            <div>África: <strong>20%</strong></div>
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

function setDates(start: string, end: string) {
  if (start) form.start_date = start;
  if (end) form.end_date = end;
  triggerRecalculation();
}

function onCountryChange() {
  selectedCountry.value = store.countries.find((c: Country) => c.code === selectedCountryCode.value) || null;
  triggerRecalculation();
}

function getRegionSurchargeText(region: string): string {
  const map: Record<string, string> = {
    'South America': 'Recargo 0% (Tarifa Base)',
    'North America': 'Recargo +15%',
    'Europe': 'Recargo +20%',
    'Asia': 'Recargo +25%',
    'Oceania': 'Recargo +25%',
    'Africa': 'Recargo +20%',
  };
  return map[region] || 'Recargo 0%';
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
  setDates,
});
</script>

<style scoped>
.quote-form-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 32px;
  align-items: start;
}

.form-card {
  background: #ffffff;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  padding: 40px;
  box-shadow: var(--shadow-sm);
}

.card-header {
  margin-bottom: 32px;
}

.editorial-category {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  margin-bottom: 6px;
}

.card-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: var(--color-accent);
  letter-spacing: -0.03em;
  line-height: 1.15;
}
.card-subtitle {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-top: 6px;
  line-height: 1.5;
}

.section-block {
  padding-bottom: 28px;
  margin-bottom: 28px;
  border-bottom: 1px solid var(--border-subtle);
}

.section-header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 22px;
}

.step-num {
  font-size: 0.82rem;
  font-weight: 800;
  color: #111111;
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  padding: 4px 8px;
  border-radius: 6px;
  letter-spacing: 0.05em;
}

.section-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-accent);
  letter-spacing: -0.02em;
}
.section-subtext {
  font-size: 0.82rem;
  color: var(--text-muted);
  margin-top: 2px;
}

.country-select-wrapper {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.country-pill-preview {
  display: flex;
  align-items: center;
  gap: 10px;
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  padding: 8px 14px;
  border-radius: var(--radius-full);
  font-size: 0.85rem;
}
.flag-img {
  width: 22px;
  height: 15px;
  object-fit: cover;
  border-radius: 2px;
}
.preview-country-name {
  font-weight: 700;
  color: #111111;
}
.preview-country-region {
  color: #717171;
}
.preview-surcharge-rate {
  margin-left: auto;
  font-weight: 600;
  color: #111111;
  font-size: 0.78rem;
}

.btn-submit {
  width: 100%;
  padding: 16px;
  font-size: 1rem;
  border-radius: var(--radius-full);
}

/* Sidebar Editorial */
.sidebar-pricing {
  position: sticky;
  top: 90px;
}

.pricing-card {
  background-color: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 32px;
  box-shadow: var(--shadow-md);
}

.pricing-title {
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--color-accent);
  margin-bottom: 20px;
}

.price-hero-box {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 20px;
  text-align: center;
  margin-bottom: 20px;
}
.price-hero-label {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
}
.price-hero-val {
  font-size: 2.2rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin: 4px 0;
}
.price-hero-val .currency {
  font-size: 1.05rem;
  font-weight: 600;
  color: #717171;
}
.price-hero-sub {
  font-size: 0.75rem;
  color: #15803d;
  font-weight: 600;
}

.editorial-divider {
  height: 1px;
  background-color: var(--border-subtle);
  margin: 16px 0;
}

.breakdown-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.88rem;
  margin-bottom: 10px;
}
.b-label {
  color: #717171;
}
.b-val {
  font-weight: 600;
  color: #111111;
}

.minimal-perks {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 0.8rem;
  color: #555555;
  font-weight: 500;
}

.pricing-empty {
  padding: 24px 0;
  text-align: center;
}
.empty-note {
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.5;
}

.surcharge-reference-box {
  margin-top: 24px;
  padding: 16px;
  background-color: var(--bg-subtle);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
}
.ref-title {
  display: block;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #717171;
  margin-bottom: 8px;
}
.ref-items {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.75rem;
  color: #444444;
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

<template>
  <div class="quote-form-layout">
    <!-- Form Card -->
    <div class="card form-card">
      <div class="card-header">
        <h2 class="card-title">Cotizar Seguro de Viaje</h2>
        <p class="card-subtitle">Complete la información para calcular la cobertura de su viaje.</p>
      </div>

      <form @submit.prevent="handleSubmit" novalidate>
        <!-- 1. Datos del Asegurado -->
        <div class="section-block">
          <div class="section-header">
            <span class="step-num">1</span>
            <h3>Datos del Asegurado</h3>
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
                N° de Identificación / DNI <span class="required">*</span>
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
              placeholder="Ej. carlos.mendoza@email.com"
              required
            />
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>
        </div>

        <!-- 2. Datos del Viaje -->
        <div class="section-block">
          <div class="section-header">
            <span class="step-num">2</span>
            <h3>Datos del Viaje</h3>
          </div>

          <div class="form-group">
            <label class="form-label" for="destination_country">
              País de Destino (REST Countries) <span class="required">*</span>
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

              <!-- Selected Country preview with Flag -->
              <div v-if="selectedCountry" class="country-preview">
                <img
                  v-if="selectedCountry.flag_url"
                  :src="selectedCountry.flag_url"
                  :alt="selectedCountry.name"
                  class="flag-img"
                />
                <div>
                  <span class="country-preview-name">{{ selectedCountry.name }}</span>
                  <span class="region-tag">{{ selectedCountry.region }}</span>
                </div>
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
          <span v-if="store.actionLoading">Calculando y guardando...</span>
          <span v-else>Cotizar Seguro de Viaje →</span>
        </button>
      </form>
    </div>

    <!-- Live Pricing Breakdown Sidebar -->
    <div class="sidebar-pricing">
      <div class="card pricing-card">
        <h3 class="pricing-title">Resumen de Cotización</h3>
        <p class="pricing-subtitle">Cálculo dinámico en tiempo real</p>

        <div v-if="preview" class="breakdown-list">
          <div class="breakdown-item">
            <span class="item-label">Destino</span>
            <span class="item-val">{{ selectedCountry ? selectedCountry.name : 'Por seleccionar' }}</span>
          </div>
          <div class="breakdown-item">
            <span class="item-label">Región Continental</span>
            <span class="item-val badge badge-quoted">{{ selectedCountry ? selectedCountry.region : '-' }}</span>
          </div>
          <div class="breakdown-item">
            <span class="item-label">Días de Cobertura</span>
            <span class="item-val"><strong>{{ preview.days_count }} días</strong></span>
          </div>

          <div class="divider"></div>

          <div class="breakdown-item">
            <span class="item-label">Tarifa Base ($3.00/día)</span>
            <span class="item-val">${{ preview.base_amount.toFixed(2) }}</span>
          </div>
          <div class="breakdown-item">
            <span class="item-label">Recargo Región ({{ preview.surcharge_percentage }}%)</span>
            <span class="item-val">+${{ preview.surcharge_amount.toFixed(2) }}</span>
          </div>

          <div class="divider"></div>

          <div class="total-box">
            <span class="total-label">Total a Pagar</span>
            <span class="total-amount">${{ preview.total_amount.toFixed(2) }} USD</span>
          </div>
        </div>

        <div v-else class="pricing-empty">
          <div class="empty-icon">🗓️</div>
          <p>Seleccione el país y las fechas del viaje para visualizar el cálculo detallado de la tarifa.</p>
        </div>

        <div class="surcharge-info-box">
          <div class="surcharge-header">Reglas de Recargo por Región:</div>
          <div class="surcharge-grid">
            <span>South America: <strong>0%</strong></span>
            <span>North America: <strong>15%</strong></span>
            <span>Europe: <strong>20%</strong></span>
            <span>Asia / Oceania: <strong>25%</strong></span>
            <span>Africa: <strong>20%</strong></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useQuotationStore } from '../stores/quotationStore';

const emit = defineEmits(['quoteCreated']);
const store = useQuotationStore();

const todayDate = new Date().toISOString().split('T')[0];

const selectedCountryCode = ref('');
const selectedCountry = ref(null);
const preview = ref(null);

const form = reactive({
  first_name: '',
  last_name: '',
  identification_number: '',
  email: '',
  birth_date: '',
  start_date: '',
  end_date: '',
});

const errors = reactive({
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

function onCountryChange() {
  selectedCountry.value = store.countries.find(c => c.code === selectedCountryCode.value) || null;
  triggerRecalculation();
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

function validateForm() {
  let valid = true;
  Object.keys(errors).forEach(k => errors[k] = '');

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

async function handleSubmit() {
  if (!validateForm()) return;

  const payload = {
    ...form,
    destination_country: selectedCountry.value.name,
    destination_country_code: selectedCountry.value.code,
    destination_region: selectedCountry.value.region,
    destination_flag_url: selectedCountry.value.flag_url,
  };

  try {
    const quotation = await store.createQuotation(payload);
    emit('quoteCreated', quotation);
  } catch (err) {
    // Errors handled in store
  }
}
</script>

<style scoped>
.quote-form-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
  align-items: start;
}

.card-header {
  margin-bottom: 24px;
}
.card-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--color-accent);
}
.card-subtitle {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-top: 4px;
}

.section-block {
  padding-bottom: 20px;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border-color);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
}
.step-num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background-color: var(--color-primary);
  color: #fff;
  font-size: 0.85rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.section-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-accent);
}

.country-select-wrapper {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.country-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  background-color: #f8fafc;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
}

.flag-img {
  width: 32px;
  height: 20px;
  object-fit: cover;
  border-radius: 3px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.country-preview-name {
  font-weight: 700;
  font-size: 0.95rem;
  display: block;
}

.region-tag {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.btn-submit {
  width: 100%;
  padding: 14px;
  font-size: 1.05rem;
  margin-top: 10px;
}

/* Sidebar */
.pricing-card {
  position: sticky;
  top: 90px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}

.pricing-title {
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--color-accent);
}
.pricing-subtitle {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 18px;
}

.breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
}
.item-label {
  color: var(--text-muted);
}
.item-val {
  color: var(--text-main);
}

.divider {
  height: 1px;
  background-color: var(--border-color);
  margin: 10px 0;
}

.total-box {
  background-color: var(--color-primary-light);
  padding: 14px;
  border-radius: var(--radius-md);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.total-label {
  font-weight: 700;
  color: #0369a1;
  font-size: 0.95rem;
}
.total-amount {
  font-size: 1.35rem;
  font-weight: 800;
  color: #0369a1;
}

.pricing-empty {
  text-align: center;
  padding: 30px 10px;
  color: var(--text-muted);
  font-size: 0.88rem;
}
.empty-icon {
  font-size: 36px;
  margin-bottom: 10px;
}

.surcharge-info-box {
  margin-top: 20px;
  padding: 12px;
  background-color: #f1f5f9;
  border-radius: var(--radius-sm);
  font-size: 0.78rem;
  color: var(--text-muted);
}
.surcharge-header {
  font-weight: 700;
  margin-bottom: 6px;
  color: var(--text-main);
}
.surcharge-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
}

@media (max-width: 900px) {
  .quote-form-layout {
    grid-template-columns: 1fr;
  }
  .pricing-card {
    position: static;
  }
}
</style>

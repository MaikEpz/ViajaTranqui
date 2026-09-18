<template>
  <div class="checkout-view">
    <!-- Barra Superior de Retorno -->
    <div class="checkout-nav-bar">
      <button type="button" class="btn-back-link" @click="store.goHome()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        <span>Volver a buscar</span>
      </button>

      <span class="step-badge">Paso 2 de 2: Emisión Oficial</span>
    </div>

    <div class="checkout-layout">
      <!-- Columna Izquierda: Itinerario y Datos del Asegurado -->
      <div class="card checkout-main-card">
        <div class="card-header">
          <span class="editorial-category">Paso Final</span>
          <h2 class="card-title">Datos del Asegurado</h2>
          <p class="card-subtitle">
            Complete la información legal del pasajero para emitir la póliza y el certificado consular en PDF.
          </p>
        </div>

        <!-- Resumen del Itinerario Seleccionado -->
        <div class="itinerary-summary-box">
          <div class="itinerary-header">
            <span class="box-title">Itinerario Confirmado</span>
            <button type="button" class="edit-btn" @click="toggleEditItinerary">
              {{ editingItinerary ? 'Guardar Cambios' : 'Editar' }}
            </button>
          </div>

          <div v-if="!editingItinerary" class="itinerary-details">
            <div class="itinerary-item">
              <span class="i-label">Destino</span>
              <span class="i-val">
                <img v-if="selectedCountry?.flag_url" :src="selectedCountry.flag_url" class="mini-flag" />
                {{ selectedCountry ? selectedCountry.name : 'Por seleccionar' }}
                <small class="region-badge">({{ selectedCountry ? selectedCountry.region : '-' }})</small>
              </span>
            </div>

            <div class="itinerary-item">
              <span class="i-label">Vigencia</span>
              <span class="i-val">{{ formatDate(form.start_date) }} al {{ formatDate(form.end_date) }}</span>
            </div>

            <div class="itinerary-item">
              <span class="i-label">Duración</span>
              <span class="i-val highlight-days">{{ preview ? preview.days_count : 0 }} días continuos</span>
            </div>
          </div>

          <!-- Edición rápida de itinerario -->
          <div v-else class="itinerary-edit-form">
            <div class="form-group">
              <label class="form-label">País de Destino</label>
              <select v-model="selectedCountryCode" class="form-control" @change="onCountryChange">
                <option v-for="c in store.countries" :key="c.code" :value="c.code">
                  {{ c.name }} ({{ c.region }})
                </option>
              </select>
            </div>

            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Fecha de Salida</label>
                <input v-model="form.start_date" type="date" class="form-control" :min="todayDate" @change="triggerRecalculation" />
              </div>
              <div class="form-group">
                <label class="form-label">Fecha de Regreso</label>
                <input v-model="form.end_date" type="date" class="form-control" :min="form.start_date || todayDate" @change="triggerRecalculation" />
              </div>
            </div>
          </div>
        </div>

        <!-- Formulario de Información Personal -->
        <form @submit.prevent="handleSubmit" novalidate>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label" for="c_first_name">
                Nombres Completos <span class="required">*</span>
              </label>
              <input
                id="c_first_name"
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
              <label class="form-label" for="c_last_name">
                Apellidos Completos <span class="required">*</span>
              </label>
              <input
                id="c_last_name"
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
              <label class="form-label" for="c_id">
                N° Identificación / Cédula / Pasaporte <span class="required">*</span>
              </label>
              <input
                id="c_id"
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
              <label class="form-label" for="c_birth">
                Fecha de Nacimiento <span class="required">*</span>
              </label>
              <input
                id="c_birth"
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
            <label class="form-label" for="c_email">
              Correo Electrónico (para recepción de póliza en PDF) <span class="required">*</span>
            </label>
            <input
              id="c_email"
              v-model="form.email"
              type="email"
              class="form-control"
              :class="{ 'is-invalid': errors.email }"
              placeholder="carlos.mendoza@email.com"
              required
            />
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>

          <button
            type="submit"
            class="btn btn-primary btn-submit-checkout"
            :disabled="store.actionLoading"
          >
            <span v-if="store.actionLoading">Emitiendo póliza...</span>
            <span v-else>Confirmar y Emitir Seguro de Viaje ➔</span>
          </button>
        </form>
      </div>

      <!-- Columna Derecha: Tarifa de Seguro en la Nueva Vista (Requerimiento Explícito del Usuario) -->
      <div class="sidebar-pricing">
        <div class="card pricing-checkout-card">
          <span class="editorial-category">Tarifa del Seguro</span>
          <h3 class="pricing-title">Resumen de Liquidación</h3>

          <div v-if="preview" class="breakdown-content">
            <div class="total-highlight-card">
              <span class="th-caption">PRIMA TOTAL A PAGAR</span>
              <div class="th-amount">
                ${{ preview.total_amount.toFixed(2) }} <span class="cur">USD</span>
              </div>
              <span class="th-guarantee">Tarifa neta garantizada • Sin deducibles</span>
            </div>

            <div class="editorial-divider"></div>

            <div class="calc-line">
              <span class="cl-label">Días de Cobertura:</span>
              <span class="cl-val">{{ preview.days_count }} días</span>
            </div>

            <div class="calc-line">
              <span class="cl-label">Tarifa Base ($3.00/día):</span>
              <span class="cl-val">${{ preview.base_amount.toFixed(2) }} USD</span>
            </div>

            <div class="calc-line">
              <span class="cl-label">Recargo {{ selectedCountry?.region }} ({{ preview.surcharge_percentage }}%):</span>
              <span class="cl-val">+${{ preview.surcharge_amount.toFixed(2) }} USD</span>
            </div>

            <div class="editorial-divider"></div>

            <div class="checkout-perks-list">
              <div class="perk-row">✓ Asistencia médica internacional 24/7</div>
              <div class="perk-row">✓ Válido para visados Schengen</div>
              <div class="perk-row">✓ Repatriación y extravío de equipaje</div>
              <div class="perk-row">✓ Descarga inmediata en PDF</div>
            </div>
          </div>

          <div v-else class="preview-warning">
            <p>Seleccione el país y fechas en el panel de itinerario para liquidar la tarifa.</p>
          </div>

          <div class="surcharge-guide-card">
            <span class="sg-title">Reglas de recargo aplicadas</span>
            <div class="sg-list">
              <div>América del Sur: <strong>0%</strong></div>
              <div>Norteamérica: <strong>15%</strong></div>
              <div>Europa: <strong>20%</strong></div>
              <div>África: <strong>20%</strong></div>
              <div>Asia / Oceanía: <strong>25%</strong></div>
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
const selectedCountryCode = ref<string>(store.tripDraft?.countryCode || '');
const selectedCountry = ref<Country | null>(null);
const preview = ref<PricingBreakdown | null>(null);
const editingItinerary = ref<boolean>(false);

const form = reactive({
  first_name: '',
  last_name: '',
  identification_number: '',
  email: '',
  birth_date: '',
  start_date: store.tripDraft?.startDate || '',
  end_date: store.tripDraft?.endDate || '',
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
  if (selectedCountryCode.value) {
    onCountryChange();
  } else if (store.countries.length > 0) {
    // Si no seleccionó país en la barra, pre-seleccionar España por defecto como en la prueba técnica
    selectedCountryCode.value = 'ESP';
    onCountryChange();
  }
  // Si no había fechas, sugerir 10 días desde hoy
  if (!form.start_date) {
    const d1 = new Date();
    d1.setDate(d1.getDate() + 5);
    const d2 = new Date(d1);
    d2.setDate(d2.getDate() + 9);
    form.start_date = d1.toISOString().split('T')[0];
    form.end_date = d2.toISOString().split('T')[0];
  }
  triggerRecalculation();
});

function toggleEditItinerary() {
  editingItinerary.value = !editingItinerary.value;
  if (!editingItinerary.value) {
    triggerRecalculation();
  }
}

function onCountryChange() {
  selectedCountry.value = store.countries.find((c: Country) => c.code === selectedCountryCode.value) || null;
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

function formatDate(dateStr: string): string {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return dateStr;
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
    errors.start_date = 'La salida no puede ser anterior a hoy.';
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
    // Manejado en store
  }
}
</script>

<style scoped>
.checkout-view {
  animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

.checkout-nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.btn-back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  color: #111111;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: var(--radius-full);
  transition: all 0.15s ease;
  font-family: inherit;
}
.btn-back-link:hover {
  background-color: var(--bg-subtle);
  transform: translateX(-3px);
}

.step-badge {
  font-size: 0.75rem;
  font-weight: 700;
  color: #717171;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 32px;
  align-items: start;
}

.checkout-main-card {
  background: #ffffff;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  padding: 40px;
  box-shadow: var(--shadow-sm);
}

.card-header {
  margin-bottom: 28px;
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
}

/* Caja de Itinerario */
.itinerary-summary-box {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 20px 24px;
  margin-bottom: 32px;
}

.itinerary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}
.box-title {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #111111;
}
.edit-btn {
  background: transparent;
  border: none;
  color: #111111;
  font-size: 0.8rem;
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
}

.itinerary-details {
  display: grid;
  grid-template-columns: 1.4fr 1.4fr 1fr;
  gap: 16px;
}
.itinerary-item {
  display: flex;
  flex-direction: column;
}
.i-label {
  font-size: 0.7rem;
  color: #717171;
  text-transform: uppercase;
  font-weight: 700;
}
.i-val {
  font-size: 0.92rem;
  font-weight: 700;
  color: #111111;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}
.mini-flag {
  width: 20px;
  height: 13px;
  object-fit: cover;
  border-radius: 2px;
}
.region-badge {
  font-size: 0.72rem;
  color: #717171;
  font-weight: 500;
}
.highlight-days {
  color: #15803d;
}

.btn-submit-checkout {
  width: 100%;
  padding: 16px;
  font-size: 1rem;
  margin-top: 12px;
}

/* Sidebar de Tarifa en Checkout */
.sidebar-pricing {
  position: sticky;
  top: 90px;
}

.pricing-checkout-card {
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
  color: #111111;
  margin-bottom: 20px;
}

.total-highlight-card {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 20px;
  text-align: center;
}
.th-caption {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
}
.th-amount {
  font-size: 2.2rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin: 4px 0;
}
.th-amount .cur {
  font-size: 1rem;
  color: #717171;
}
.th-guarantee {
  font-size: 0.75rem;
  color: #15803d;
  font-weight: 600;
}

.editorial-divider {
  height: 1px;
  background-color: var(--border-subtle);
  margin: 16px 0;
}

.calc-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.88rem;
  margin-bottom: 10px;
}
.cl-label {
  color: #717171;
}
.cl-val {
  font-weight: 600;
  color: #111111;
}

.checkout-perks-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 0.8rem;
  color: #555555;
  font-weight: 500;
}

.preview-warning {
  padding: 20px 0;
  text-align: center;
  font-size: 0.85rem;
  color: #717171;
}

.surcharge-guide-card {
  margin-top: 24px;
  padding: 16px;
  background-color: var(--bg-subtle);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
}
.sg-title {
  display: block;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #717171;
  margin-bottom: 8px;
}
.sg-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.75rem;
  color: #444444;
}

@media (max-width: 960px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
  .sidebar-pricing {
    position: static;
  }
  .itinerary-details {
    grid-template-columns: 1fr;
  }
}
</style>

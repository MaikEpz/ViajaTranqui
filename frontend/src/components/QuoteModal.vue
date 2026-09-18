<template>
  <div class="modal-backdrop-blur" @click.self="closeModal">
    <div class="modal-dialog-card">
      <!-- Botón de Cierre Superior -->
      <button type="button" class="btn-close-modal" title="Cerrar" @click="closeModal">
        ✕
      </button>

      <!-- FASE 1: Formulario de Datos y Desglose de Tarifa -->
      <div v-if="!issuedQuote" class="modal-phase-form">
        <div class="modal-intro">
          <span class="modal-eyebrow">Paso Final</span>
          <h2 class="modal-headline">Emisión de Seguro de Viaje</h2>
          <p class="modal-caption">
            Verifique su itinerario e ingrese los datos del pasajero para generar la póliza oficial.
          </p>
        </div>

        <!-- Resumen Rápido del Itinerario -->
        <div class="itinerary-pill-box">
          <div class="itinerary-main-info">
            <span class="itinerary-destination">
              <img v-if="selectedCountry?.flag_url" :src="selectedCountry.flag_url" class="country-flag" />
              <strong>{{ selectedCountry ? selectedCountry.name : 'Destino' }}</strong>
              <small>({{ selectedCountry ? selectedCountry.region : '-' }})</small>
            </span>
            <span class="itinerary-separator">•</span>
            <span class="itinerary-dates">
              {{ formatDate(form.start_date) }} al {{ formatDate(form.end_date) }}
            </span>
            <span class="itinerary-separator">•</span>
            <span class="itinerary-days">
              <strong>{{ preview ? preview.days_count : 0 }}</strong> días
            </span>
          </div>

          <button type="button" class="btn-toggle-edit" @click="editingItinerary = !editingItinerary">
            {{ editingItinerary ? 'Ocultar' : 'Modificar' }}
          </button>
        </div>

        <!-- Campos para editar itinerario si el usuario lo desea -->
        <div v-if="editingItinerary" class="itinerary-edit-row">
          <div class="infield-group">
            <label class="infield-label">País de Destino</label>
            <select v-model="selectedCountryCode" class="infield-input infield-select" @change="onCountryChange">
              <option v-for="c in store.countries" :key="c.code" :value="c.code">
                {{ c.name }} ({{ c.region }})
              </option>
            </select>
          </div>
          <div class="infield-group">
            <label class="infield-label">Fecha de Salida</label>
            <input v-model="form.start_date" type="date" class="infield-input" :min="todayDate" @change="triggerRecalculation" />
          </div>
          <div class="infield-group">
            <label class="infield-label">Fecha de Regreso</label>
            <input v-model="form.end_date" type="date" class="infield-input" :min="form.start_date || todayDate" @change="triggerRecalculation" />
          </div>
        </div>

        <!-- Cuerpo con Formulario y Liquidación -->
        <div class="modal-split-layout">
          <!-- Columna Formulario con Etiquetas Integradas dentro del campo -->
          <form class="passenger-form-col" @submit.prevent="handleSubmit" novalidate>
            <div class="grid-2">
              <div class="infield-group" :class="{ 'has-error': errors.first_name }">
                <label class="infield-label" for="m_first_name">Nombres <span class="required">*</span></label>
                <input
                  id="m_first_name"
                  v-model="form.first_name"
                  type="text"
                  class="infield-input"
                  placeholder="Ej. Sofía"
                  required
                />
                <span v-if="errors.first_name" class="infield-error">{{ errors.first_name }}</span>
              </div>

              <div class="infield-group" :class="{ 'has-error': errors.last_name }">
                <label class="infield-label" for="m_last_name">Apellidos <span class="required">*</span></label>
                <input
                  id="m_last_name"
                  v-model="form.last_name"
                  type="text"
                  class="infield-input"
                  placeholder="Ej. Morales"
                  required
                />
                <span v-if="errors.last_name" class="infield-error">{{ errors.last_name }}</span>
              </div>
            </div>

            <div class="grid-2">
              <div class="infield-group" :class="{ 'has-error': errors.identification_number }">
                <label class="infield-label" for="m_id">Cédula / Pasaporte <span class="required">*</span></label>
                <input
                  id="m_id"
                  v-model="form.identification_number"
                  type="text"
                  class="infield-input"
                  placeholder="Ej. 1754829103"
                  required
                />
                <span v-if="errors.identification_number" class="infield-error">{{ errors.identification_number }}</span>
              </div>

              <div class="infield-group" :class="{ 'has-error': errors.birth_date }">
                <label class="infield-label" for="m_birth">Fecha de Nacimiento <span class="required">*</span></label>
                <input
                  id="m_birth"
                  v-model="form.birth_date"
                  type="date"
                  class="infield-input"
                  :max="todayDate"
                  required
                />
                <span v-if="errors.birth_date" class="infield-error">{{ errors.birth_date }}</span>
              </div>
            </div>

            <div class="infield-group" :class="{ 'has-error': errors.email }">
              <label class="infield-label" for="m_email">Correo Electrónico (Recepción PDF) <span class="required">*</span></label>
              <input
                id="m_email"
                v-model="form.email"
                type="email"
                class="infield-input"
                placeholder="sofia.morales@example.com"
                required
              />
              <span v-if="errors.email" class="infield-error">{{ errors.email }}</span>
            </div>

            <button
              type="submit"
              class="btn btn-primary btn-modal-submit"
              :disabled="store.actionLoading"
            >
              <span v-if="store.actionLoading">Generando póliza oficial...</span>
              <span v-else>Confirmar y Emitir Póliza ➔</span>
            </button>
          </form>

          <!-- Columna Resumen de Tarifa -->
          <div class="rate-summary-col">
            <div class="rate-summary-box">
              <span class="rs-eyebrow">Tarifa Liquidada</span>
              <div class="rs-amount-block">
                <div class="rs-total">
                  ${{ preview ? preview.total_amount.toFixed(2) : '0.00' }}
                  <small>USD</small>
                </div>
                <span class="rs-total-sub">Tarifa final neta • Sin deducibles</span>
              </div>

              <div class="rs-divider"></div>

              <div class="rs-lines">
                <div class="rs-line">
                  <span>Días de Cobertura:</span>
                  <strong>{{ preview ? preview.days_count : 0 }} días</strong>
                </div>
                <div class="rs-line">
                  <span>Tarifa Base ($3.00/día):</span>
                  <strong>${{ preview ? preview.base_amount.toFixed(2) : '0.00' }} USD</strong>
                </div>
                <div class="rs-line">
                  <span>Recargo {{ selectedCountry?.region }} ({{ preview?.surcharge_percentage }}%):</span>
                  <strong>+${{ preview ? preview.surcharge_amount.toFixed(2) : '0.00' }} USD</strong>
                </div>
              </div>

              <div class="rs-divider"></div>

              <div class="rs-perks">
                <div>✓ Asistencia médica internacional</div>
                <div>✓ Certificado consular en PDF</div>
                <div>✓ Validez oficial Schengen</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FASE 2: Póliza Emitida con Éxito (Recibo y Acciones PDF / Contratar) -->
      <div v-else class="modal-phase-success">
        <div class="modal-intro">
          <span class="modal-eyebrow">Emisión Confirmada</span>
          <h2 class="modal-headline">
            {{ issuedQuote.status === 'Contratado' ? 'Póliza Contratada y Registrada' : 'Póliza Emitida con Éxito' }}
          </h2>
          <p class="modal-caption">
            Su seguro ha sido registrado oficialmente en el sistema. Puede descargar el certificado consular en PDF.
          </p>
        </div>

        <div class="success-receipt-card">
          <div class="rcpt-header">
            <div class="rcpt-pill">
              <span class="rcpt-dot" :class="issuedQuote.status === 'Contratado' ? 'dot-active' : 'dot-pending'"></span>
              <span>{{ issuedQuote.status }}</span>
            </div>
            <span class="rcpt-code">VTQ-{{ String(issuedQuote.id).padStart(5, '0') }}</span>
          </div>

          <div class="rcpt-total-hero">
            <span class="rcpt-tot-label">PRIMA TOTAL</span>
            <div class="rcpt-tot-val">${{ Number(issuedQuote.total_amount).toFixed(2) }} <small>USD</small></div>
          </div>

          <div class="rs-divider"></div>

          <div class="rcpt-grid">
            <div class="rg-item">
              <span class="rg-label">Asegurado</span>
              <span class="rg-val">{{ issuedQuote.first_name }} {{ issuedQuote.last_name }}</span>
            </div>
            <div class="rg-item">
              <span class="rg-label">Identificación</span>
              <span class="rg-val">{{ issuedQuote.identification_number }}</span>
            </div>
            <div class="rg-item">
              <span class="rg-label">Destino</span>
              <span class="rg-val">
                <img v-if="issuedQuote.destination_flag_url" :src="issuedQuote.destination_flag_url" class="mini-flag" />
                {{ issuedQuote.destination_country }} ({{ issuedQuote.destination_region }})
              </span>
            </div>
            <div class="rg-item">
              <span class="rg-label">Vigencia</span>
              <span class="rg-val">{{ formatDate(issuedQuote.start_date) }} al {{ formatDate(issuedQuote.end_date) }}</span>
            </div>
          </div>
        </div>

        <div class="success-actions-row">
          <button type="button" class="btn btn-secondary btn-action-lg" @click="downloadPdf">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
            </svg>
            <span>Descargar PDF Oficial</span>
          </button>

          <button
            v-if="issuedQuote.status === 'Cotizado'"
            type="button"
            class="btn btn-primary btn-action-lg"
            :disabled="store.actionLoading"
            @click="contractInsurance"
          >
            <span v-if="store.actionLoading">Contratando...</span>
            <span v-else>Confirmar y Contratar Seguro ➔</span>
          </button>

          <button v-else type="button" class="btn btn-primary btn-action-lg" @click="closeModal">
            Finalizar
          </button>
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
  (e: 'close'): void;
}>();

const store = useQuotationStore();

const todayDate = new Date().toISOString().split('T')[0];
const selectedCountryCode = ref<string>(store.tripDraft?.countryCode || 'ESP');
const selectedCountry = ref<Country | null>(null);
const preview = ref<PricingBreakdown | null>(null);
const editingItinerary = ref<boolean>(false);
const issuedQuote = ref<Quotation | null>(null);

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
});

onMounted(async () => {
  await store.fetchCountries();
  if (selectedCountryCode.value) {
    onCountryChange();
  } else if (store.countries.length > 0) {
    selectedCountryCode.value = 'ESP';
    onCountryChange();
  }

  if (!form.start_date) {
    const d1 = new Date();
    d1.setDate(d1.getDate() + 7);
    const d2 = new Date(d1);
    d2.setDate(d2.getDate() + 9);
    form.start_date = d1.toISOString().split('T')[0];
    form.end_date = d2.toISOString().split('T')[0];
  }
  triggerRecalculation();
});

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
  const clean = dateStr.split('T')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
}

function validate(): boolean {
  let valid = true;
  Object.keys(errors).forEach((k) => (errors[k] = ''));

  if (!form.first_name.trim()) {
    errors.first_name = 'Ingrese los nombres.';
    valid = false;
  }
  if (!form.last_name.trim()) {
    errors.last_name = 'Ingrese los apellidos.';
    valid = false;
  }
  if (!form.identification_number.trim()) {
    errors.identification_number = 'Ingrese identificación / pasaporte.';
    valid = false;
  }
  if (!form.email.trim() || !form.email.includes('@')) {
    errors.email = 'Ingrese un correo electrónico válido.';
    valid = false;
  }
  if (!form.birth_date) {
    errors.birth_date = 'Seleccione fecha de nacimiento.';
    valid = false;
  } else if (form.birth_date >= todayDate) {
    errors.birth_date = 'Debe ser anterior a hoy.';
    valid = false;
  }

  return valid;
}

async function handleSubmit(): Promise<void> {
  if (!validate() || !selectedCountry.value) return;

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
    const q = await store.createQuotation(payload);
    issuedQuote.value = q;
  } catch (err) {
    // Manejado en store
  }
}

function downloadPdf() {
  if (issuedQuote.value) {
    store.downloadPdf(issuedQuote.value.id);
  }
}

async function contractInsurance() {
  if (issuedQuote.value) {
    const updated = await store.contractQuotation(issuedQuote.value.id);
    issuedQuote.value = updated;
  }
}

function closeModal() {
  store.closeQuoteModal();
  emit('close');
}
</script>

<style scoped>
.modal-backdrop-blur {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 24px;
  overflow-y: auto;
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-dialog-card {
  background-color: #ffffff;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.3);
  max-width: 860px;
  width: 100%;
  position: relative;
  padding: 36px 40px;
  max-height: 90vh;
  overflow-y: auto;
  animation: popScale 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes popScale {
  from { opacity: 0; transform: scale(0.96) translateY(12px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.btn-close-modal {
  position: absolute;
  top: 20px;
  right: 20px;
  background: transparent;
  border: none;
  font-size: 1.25rem;
  color: #717171;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  line-height: 1;
  transition: all 0.15s ease;
}
.btn-close-modal:hover {
  color: #111111;
  background-color: var(--bg-subtle);
}

.modal-intro {
  margin-bottom: 20px;
}

.modal-eyebrow {
  display: inline-block;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  margin-bottom: 4px;
}

.modal-headline {
  font-size: 1.65rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1.15;
}

.modal-caption {
  font-size: 0.88rem;
  color: #717171;
  margin-top: 4px;
}

/* Pastilla de Itinerario */
.itinerary-pill-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 12px 20px;
  margin-bottom: 24px;
}

.itinerary-main-info {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.88rem;
  color: #111111;
  flex-wrap: wrap;
}

.country-flag {
  width: 18px;
  height: 12px;
  object-fit: cover;
  border-radius: 2px;
  margin-right: 6px;
}

.itinerary-separator {
  color: #d1d5db;
}

.itinerary-days strong {
  color: #15803d;
}

.btn-toggle-edit {
  background: transparent;
  border: none;
  font-size: 0.8rem;
  font-weight: 700;
  color: #111111;
  text-decoration: underline;
  cursor: pointer;
}

.itinerary-edit-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 12px;
  background-color: var(--bg-subtle);
  border: 1px dashed var(--border-color);
  border-radius: var(--radius-md);
  padding: 14px 18px;
  margin-bottom: 20px;
}

/* Layout partido */
.modal-split-layout {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 28px;
  align-items: start;
}

.passenger-form-col {
  display: flex;
  flex-direction: column;
}

/* Entradas con Etiquetas Integradas (In-field Labels) estilo Airbnb / Stripe */
.infield-group {
  background-color: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: 8px 12px 6px;
  display: flex;
  flex-direction: column;
  transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
  margin-bottom: 12px;
}

.infield-group:focus-within {
  border-color: #111111;
  box-shadow: 0 0 0 1px #111111, 0 3px 8px rgba(0, 0, 0, 0.04);
}

.infield-group.has-error {
  border-color: #dc2626;
  background-color: #fef2f2;
}

.infield-label {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #717171;
  margin-bottom: 2px;
  line-height: 1;
  user-select: none;
  cursor: pointer;
}

.infield-group:focus-within .infield-label {
  color: #111111;
}

.infield-group.has-error .infield-label {
  color: #dc2626;
}

.infield-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.92rem;
  font-weight: 600;
  color: #111111;
  font-family: inherit;
  padding: 0;
  width: 100%;
  min-width: 0;
  line-height: 1.25;
}

.infield-input::placeholder {
  color: #a1a1aa;
  font-weight: 400;
  font-size: 0.88rem;
}

.infield-select {
  cursor: pointer;
  background: transparent;
}

.infield-error {
  font-size: 0.68rem;
  color: #dc2626;
  font-weight: 600;
  margin-top: 4px;
}

.btn-modal-submit {
  width: 100%;
  padding: 14px;
  font-size: 0.95rem;
  margin-top: 8px;
}

/* Columna de Resumen de Tarifa */
.rate-summary-col {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 24px;
}

.rs-eyebrow {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
  display: block;
}

.rs-amount-block {
  margin: 6px 0 12px;
}

.rs-total {
  font-size: 2.2rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  line-height: 1;
}

.rs-total small {
  font-size: 1rem;
  color: #717171;
}

.rs-total-sub {
  font-size: 0.72rem;
  color: #15803d;
  font-weight: 600;
  display: block;
  margin-top: 4px;
}

.rs-divider {
  height: 1px;
  background-color: var(--border-subtle);
  margin: 14px 0;
}

.rs-lines {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 0.82rem;
}

.rs-line {
  display: flex;
  justify-content: space-between;
  color: #717171;
}

.rs-line strong {
  color: #111111;
}

.rs-perks {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.75rem;
  color: #444444;
  font-weight: 500;
}

/* Fase de Éxito / Póliza */
.success-receipt-card {
  background-color: var(--bg-subtle);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 24px;
  margin-bottom: 24px;
}

.rcpt-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.rcpt-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid var(--border-color);
  padding: 4px 10px;
  border-radius: var(--radius-full);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #111111;
}

.rcpt-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.dot-active { background-color: #15803d; }
.dot-pending { background-color: #717171; }

.rcpt-code {
  font-family: monospace;
  font-weight: 700;
  font-size: 0.8rem;
  color: #717171;
}

.rcpt-total-hero {
  text-align: center;
  padding: 8px 0;
}

.rcpt-tot-label {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #717171;
}

.rcpt-tot-val {
  font-size: 2.2rem;
  font-weight: 800;
  color: #111111;
  letter-spacing: -0.03em;
  margin-top: 2px;
}

.rcpt-tot-val small {
  font-size: 1rem;
  color: #717171;
}

.rcpt-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.rg-item {
  display: flex;
  flex-direction: column;
}

.rg-label {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #717171;
}

.rg-val {
  font-size: 0.85rem;
  font-weight: 600;
  color: #111111;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.mini-flag {
  width: 16px;
  height: 11px;
  object-fit: cover;
  border-radius: 2px;
}

.success-actions-row {
  display: flex;
  gap: 12px;
}

.btn-action-lg {
  flex: 1;
  padding: 14px 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

@media (max-width: 760px) {
  .modal-backdrop-blur {
    padding: 14px 10px;
  }
  .modal-dialog-card {
    padding: 24px 18px;
    border-radius: var(--radius-lg);
  }
  .modal-split-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .rate-summary-col {
    order: -1;
    padding: 16px;
    border-radius: var(--radius-md);
  }
  .rs-total {
    font-size: 1.85rem;
  }
  .rs-perks {
    display: none;
  }
  .rs-divider {
    margin: 10px 0;
  }
  .itinerary-edit-row {
    grid-template-columns: 1fr;
    padding: 12px;
  }
  .rcpt-grid {
    grid-template-columns: 1fr;
  }
  .success-actions-row {
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .modal-backdrop-blur {
    padding: 8px;
  }
  .modal-dialog-card {
    padding: 20px 14px;
    max-height: 94vh;
  }
  .modal-headline {
    font-size: 1.35rem;
    padding-right: 28px;
  }
  .modal-caption {
    font-size: 0.82rem;
  }
  .itinerary-pill-box {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 12px;
  }
  .itinerary-main-info {
    gap: 6px;
    font-size: 0.8rem;
  }
  .itinerary-separator {
    display: none;
  }
  .itinerary-destination,
  .itinerary-dates,
  .itinerary-days {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    padding: 3px 7px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-subtle);
  }
  .btn-toggle-edit {
    align-self: flex-end;
    font-size: 0.78rem;
  }
  .success-receipt-card {
    padding: 16px 14px;
  }
  .rcpt-tot-val {
    font-size: 1.85rem;
  }
  .btn-action-lg {
    padding: 12px 16px;
    font-size: 0.88rem;
  }
}
</style>

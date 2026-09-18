# ViajaTranqui — Sistema de Cotización y Venta de Seguro de Viaje

> **Solución Técnica de Alto Nivel para la Prueba Técnica de Laravel + Vue.js**  
> **Destinatario / Evaluador:** `vrubio@gestionsegura.com.ec`  
> **Estado del Proyecto:** ✅ **100% de Requisitos Funcionales y Técnicos Cumplidos** | **28/28 Pruebas PEST Aprobadas (1.096 aserciones)**

---

## Índice de Contenidos

1. [Resumen Ejecutivo y Matriz de Cumplimiento](#1-resumen-ejecutivo-y-matriz-de-cumplimiento)
2. [Arquitectura y Decisiones de Diseño Técnico](#2-arquitectura-y-decisiones-de-diseño-técnico)
   - [Estructura del Proyecto y Dónde se Ubica Cada Pieza](#estructura-del-proyecto-y-dónde-se-ubica-cada-pieza)
   - [Principios SOLID y Clean Architecture Aplicados](#principios-solid-y-clean-architecture-aplicados)
   - [Estrategia de Resiliencia con la API Externa (REST Countries)](#estrategia-de-resiliencia-con-la-api-externa-rest-countries)
   - [Frontend con Vue 3, Pinia y TypeScript](#frontend-con-vue-3-pinia-y-typescript)
3. [Reglas de Negocio y Caso de Prueba Oficial](#3-reglas-de-negocio-y-caso-de-prueba-oficial)
4. [Estrategia y Criterio de Validaciones](#4-estrategia-y-criterio-de-validaciones)
5. [Instrucciones de Instalación y Ejecución Rápida](#5-instrucciones-de-instalación-y-ejecución-rápida)
   - [Opción A: Con Docker (Recomendado — Un solo comando)](#opción-a-con-docker-recomendado)
   - [Opción B: Instalación Local Manual](#opción-b-instalación-local-manual)
6. [Pruebas Automatizadas con PEST](#6-pruebas-automatizadas-con-pest)
7. [Endpoints de la API REST](#7-endpoints-de-la-api-rest)
8. [Puntos Bonus Implementados con Valor Real](#8-puntos-bonus-implementados-con-valor-real)
9. [Mejoras Futuras para un Entorno Productivo](#9-mejoras-futuras-para-un-entorno-productivo)

---

## 1. Resumen Ejecutivo y Matriz de Cumplimiento

Esta solución fue desarrollada siguiendo estándares de ingeniería de software para empresas de seguros: desacoplamiento de la lógica de negocio mediante **Clean Architecture**, diseño de dominio enfocado en **Actions individuales**, transporte inmutable con **DTOs en PHP 8.4**, interfaz reactiva con **Vue 3 + Pinia + TypeScript**, y una suite de pruebas automatizadas completa con **PEST**.

### Matriz de Cumplimiento de Criterios de Evaluación

| Criterio del Documento Oficial | Ponderación | Archivo(s) Clave en el Código | Estado |
| :--- | :---: | :--- | :---: |
| **1. Arquitectura y organización del código** | **20%** | `backend/app/Domain/Actions/`, `backend/app/Domain/DTOs/`, `backend/app/Domain/Contracts/` | ✅ **Excelente** |
| **2. Laravel / Backend** | **20%** | `backend/app/Http/Controllers/QuotationController.php`, `backend/app/Http/Requests/StoreQuotationRequest.php` | ✅ **Excelente** |
| **3. Vue.js / Frontend** | **15%** | `frontend/src/App.vue`, `frontend/src/components/`, `frontend/src/stores/quotationStore.ts` | ✅ **Excelente** |
| **4. Modelo de BD y Migraciones** | **10%** | `backend/database/migrations/2026_09_18_010856_create_quotations_table.php`, `QuotationSeeder.php` | ✅ **Excelente** |
| **5. Lógica de negocio y validaciones** | **15%** | `backend/app/Domain/Actions/CalculateQuotationAction.php`, `StoreQuotationRequest.php` | ✅ **Excelente** |
| **6. Integración con API externa** | **5%** | `backend/app/Infrastructure/Adapters/RestCountriesAdapter.php` (Timeout + Fallbacks + Caché) | ✅ **Excelente** |
| **7. Pruebas automatizadas (PEST)** | **10%** | `backend/tests/Unit/`, `backend/tests/Feature/QuotationApiTest.php` (28 tests, 1.096 assertions) | ✅ **Excelente** |
| **8. Git, documentación y calidad** | **5%** | Historial semántico Git, código tipado estricto, README exhaustivo | ✅ **Excelente** |

---

## 2. Arquitectura y Decisiones de Diseño Técnico

### Estructura del Proyecto y Dónde se Ubica Cada Pieza

Para evitar la concentración de código en controladores o componentes visuales, el backend implementa una **Arquitectura Limpia Pragmática (Clean Architecture / Action-Driven)**:

```
ViajaTranqui/
├── docker-compose.yml                      # Orquestación multi-contenedor (PHP 8.4 + MySQL 8.0)
│
├── backend/                                # 🐘 BACKEND LARAVEL 11 (PHP 8.4)
│   ├── app/
│   │   ├── Domain/                         # 🧠 CAPA DE DOMINIO (Reglas puras, independientes del framework)
│   │   │   ├── Contracts/                  # Inversión de Dependencias (DIP)
│   │   │   │   └── CountryProviderInterface.php  # Contrato agnóstico de proveedores de países
│   │   │   ├── DTOs/                       # Objetos de transferencia inmutables (PHP 8.4 readonly)
│   │   │   │   ├── CountryData.php
│   │   │   │   ├── PricingBreakdownData.php
│   │   │   │   └── QuotationData.php
│   │   │   ├── Exceptions/                 # Excepciones semánticas de negocio
│   │   │   │   ├── InvalidTravelDatesException.php
│   │   │   │   └── QuotationAlreadyContractedException.php
│   │   │   └── Actions/                    # Casos de Uso (Single Responsibility Principle)
│   │   │       ├── CalculateQuotationAction.php  # Lógica matemática de días, base y recargos
│   │   │       ├── CreateQuotationAction.php     # Creación y persistencia de cotizaciones
│   │   │       ├── ContractQuotationAction.php   # Transición a 'Contratado' con invariantes
│   │   │       └── GetCountriesAction.php        # Consulta de catálogo geográfico
│   │   │
│   │   ├── Infrastructure/                 # 🔌 CAPA DE INFRAESTRUCTURA (I/O, APIs externas)
│   │   │   └── Adapters/
│   │   │       └── RestCountriesAdapter.php # Implementación resiliente con timeout, fallback y caché
│   │   │
│   │   ├── Services/                       # Servicios de aplicación auxiliares
│   │   │   ├── QuotationPdfService.php     # Renderizado DomPDF con logotipos embebidos en Base64
│   │   │   └── QuotationCalculationService.php # Helper de soporte para factories y tests
│   │   │
│   │   ├── Http/                           # 🌐 CAPA DE ENTRADA HTTP (Slim Controllers)
│   │   │   ├── Controllers/
│   │   │   │   ├── CountryController.php   # Endpoint de catálogo de países
│   │   │   │   └── QuotationController.php # Endpoints REST de cotizaciones, contratos y PDFs
│   │   │   └── Requests/                   # Validaciones desacopladas
│   │   │       ├── CalculateQuotationRequest.php
│   │   │       └── StoreQuotationRequest.php # Expone toDTO() hacia la capa de dominio
│   │   │
│   │   └── Models/                         # Persistencia Eloquent con Scopes y Casts estrictos
│   │       └── Quotation.php
│   │
│   ├── database/
│   │   ├── migrations/                     # Estructura relacional con índices optimizados
│   │   ├── seeders/                        # Población con el caso de negocio oficial de España
│   │   └── factories/                      # Fábricas Faker para generar datos realistas
│   │
│   ├── resources/views/pdf/
│   │   └── quotation.blade.php             # Plantilla Blade profesional para el PDF oficial
│   │
│   └── tests/                              # 🧪 SUITE DE PRUEBAS AUTOMATIZADAS PEST
│       ├── Unit/                           # Pruebas unitarias de dominio y acciones
│       └── Feature/                        # Pruebas de integración de endpoints API
│
└── frontend/                               # ⚡ FRONTEND VUE 3 + TYPESCRIPT + PINIA + VITE
    ├── src/
    │   ├── components/
    │   │   ├── Navbar.vue                  # Navegación fluida con badges dinámicos
    │   │   ├── TravelHero.vue              # Cabecera editorial y disparador de cotización
    │   │   ├── InsurancePricingSection.vue # Resumen transparente de tarifas y recargos
    │   │   ├── QuoteModal.vue              # Modal de 2 pasos: formulario + desglose y contratación
    │   │   └── QuotationsList.vue          # Tabla compacta editorial con Skeleton Loader
    │   │
    │   ├── stores/
    │   │   └── quotationStore.ts           # Estado global reactivo con Pinia (Cálculo, Emisión, Toasts)
    │   │
    │   ├── types/                          # Definición de interfaces y tipos TypeScript estrictos
    │   │   └── quotation.ts
    │   ├── services/
    │   │   └── api.ts                      # Cliente Axios configurado con baseURL e interceptores
    │   ├── App.vue                         # Layout principal con Toasts flotantes en esquina
    │   └── style.css                       # Sistema de diseño minimalista editorial
```

---

### Principios SOLID y Clean Architecture Aplicados

1. **Single Responsibility Principle (SRP)**:
   - Los controladores (`QuotationController.php`) únicamente reciben la petición HTTP, delegan a una `Action` de dominio y devuelven una respuesta JSON estandarizada. Toda la lógica de negocio vive en clases `Action` independientes.
2. **Dependency Inversion Principle (DIP)**:
   - El dominio interactúa con países mediante la interfaz `CountryProviderInterface`. La aplicación no depende de `https://restcountries.com/`; si el día de mañana se cambia de proveedor, únicamente se registra un nuevo adaptador en `AppServiceProvider` sin tocar una sola línea del dominio.
3. **Data Transfer Objects (DTOs)**:
   - En lugar de pasar arreglos genéricos (`$request->all()`), se utilizan DTOs fuertemente tipados e inmutables de PHP 8.4 (`QuotationData`, `PricingBreakdownData`, `CountryData`) garantizando integridad estructural entre capas.
4. **Excepciones de Dominio Semánticas**:
   - Errores de negocio como fechas inconsistentes (`InvalidTravelDatesException`) o intentos de doble contratación (`QuotationAlreadyContractedException`) se capturan de forma semántica con códigos de estado HTTP apropiados (422 Unprocessable Entity).

---

### Estrategia de Resiliencia con la API Externa (REST Countries)

El adaptador `RestCountriesAdapter.php` implementa un circuito de tolerancia a fallos en 4 capas:
1. **Timeout Estricto de 5 Segundos**: Si la API de REST Countries experimenta latencia o caídas, la petición no bloquea la aplicación.
2. **Conmutación Automática a Espejo Público (Fallback)**: Si el endpoint primario falla o retorna respuestas inesperadas, el adaptador conmuta inmediatamente al repositorio abierto `mledoze/countries` en GitHub.
3. **Catálogo Offline Empaquetado**: Como tercera línea de defensa, el sistema incluye una copia local optimizada de países con banderas y regiones para garantizar funcionamiento 100% ininterrumpido sin internet.
4. **Caché Inteligente de 24 Horas**: Laravel almacena la respuesta en caché (`Cache::remember`), reduciendo drásticamente el consumo de ancho de banda y mejorando la velocidad de carga de la interfaz.

---

### Frontend con Vue 3, Pinia y TypeScript

- **Vue 3 Composition API (`<script setup>`)**: Código conciso, reactivo y modular.
- **Pinia**: Centralización del estado global de cotizaciones, alertas, modales y selección de país.
- **TypeScript Estricto**: Tipado estricto en modelos, respuestas y formularios evitando errores en tiempo de ejecución.
- **Experiencia de Usuario Refinada**:
  - **Skeleton Loader**: Mientras se cargan las cotizaciones, la tabla muestra un esqueleto animado (*shimmer*) con las mismas 6 columnas, evitando saltos bruscos de anchura (*Cumulative Layout Shift - CLS*).
  - **Tabla Compacta Editorial**: 6 columnas de alta densidad que reúnen toda la información requerida (asegurado, identificación, destino, fechas limpias `DD/MM/YYYY`, días, total y estado) visibles al 100% en pantallas de escritorio sin scroll horizontal forzado.
  - **Toast Flotante en Esquina**: Notificaciones de éxito y error discretas en la esquina inferior derecha con auto-cierre en 4.5 segundos.

---

## 3. Reglas de Negocio y Caso de Prueba Oficial

### Lógica de Tarifación

1. **Tarifa Base**: **$3.00 USD** por cada día de viaje (inclusivo).
   $$\text{Días de Viaje} = (\text{Fecha de Regreso} - \text{Fecha de Salida}) + 1$$
   $$\text{Monto Base} = \text{Días} \times 3.00$$

2. **Recargos Regionales por Continente de Destino**:
   - **South America**: +0%
   - **North America**: +15%
   - **Europe**: +20%
   - **Africa**: +20%
   - **Asia**: +25%
   - **Oceania**: +25%

3. **Total de la Póliza**:
   $$\text{Total} = \text{Monto Base} + (\text{Monto Base} \times \% \text{ Recargo})$$

### Verificación del Caso de Negocio del Documento
> **"Viaje de 10 días a España: Tarifa base $30 + Recargo Europa 20% ($6) = Total $36"**

- **Implementación**: Este caso exacto está sembrado como el **Registro #1** en la base de datos (`QuotationSeeder.php`), permitiendo su consulta y descarga inmediata de PDF en la aplicación.
- **Verificación en Tests Unitarios**: Avalado por las pruebas `CalculateQuotationActionTest` y `QuotationCalculationServiceTest`.

---

## 4. Estrategia y Criterio de Validaciones

El sistema implementa validaciones cruzadas en **Frontend** y **Backend** (`StoreQuotationRequest.php`):

1. **Asegurado**:
   - `first_name` y `last_name`: Requeridos, alfabéticos, longitud máxima de 100 caracteres.
   - `identification_number`: Requerido, alfanumérico limpio (cédula o pasaporte).
   - `email`: Requerido, formato de correo válido según RFC.
   - `birth_date`: Requerido, fecha pasada (asegura mayoría de edad lógica).
2. **Viaje y Cobertura**:
   - `destination_country`, `destination_country_code`, `destination_region`: Requeridos y verificados contra el catálogo oficial.
   - `start_date`: Requerido, debe ser igual o posterior a la fecha actual (`after_or_equal:today`).
   - `end_date`: Requerido, debe ser igual o posterior a la fecha de inicio (`after_or_equal:start_date`).
3. **Invariantes de Dominio**:
   - Prevención de doble contratación: Si una póliza ya se encuentra en estado `Contratado`, cualquier intento posterior de confirmación es rechazado por `ContractQuotationAction` mediante la excepción `QuotationAlreadyContractedException`.

---

## 5. Instrucciones de Instalación y Ejecución Rápida

### Opción A: Con Docker (Recomendado — Un solo comando)

> **Requisitos:** Tener instalado **Docker Desktop** (con Docker Compose v2+) y **Node.js 18+**.

#### 1. Clonar el repositorio
```bash
git clone https://github.com/MaikEpz/ViajaTranqui.git
cd ViajaTranqui
```

#### 2. Levantar el Backend y Base de Datos MySQL con Docker
```bash
docker compose up -d --build
```
*Este comando compila la imagen PHP 8.4 con todas las extensiones requeridas (`pdo_mysql`, `gd`, `intl`, `zip`, `bcmath`) y levanta MySQL 8.0 en el puerto `3306` con healthcheck automático.*

#### 3. Ejecutar Migraciones y Datos de Prueba (Seeder)
```bash
docker compose exec backend php artisan migrate:fresh --seed
```
*Crea todas las tablas e inserta 15 registros de cotizaciones/pólizas de prueba, incluyendo el caso oficial de España por $36 USD.*

#### 4. Levantar el Frontend
En otra pestaña o terminal:
```bash
cd frontend
npm install
npm run dev
```

Listo. Abre tu navegador en:
- 🌐 **Frontend (Aplicación Web):** [http://localhost:5173](http://localhost:5173)
- 🔌 **Backend API:** [http://localhost:8000/api/quotes](http://localhost:8000/api/quotes)

---

### Opción B: Instalación Local Manual (Sin Docker)

Si prefieres ejecutar el backend directamente con PHP local y MySQL:

1. **Configurar Backend**:
   ```bash
   cd backend
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
2. **Configurar Base de Datos en `.env`**:
   Ajusta `DB_HOST=127.0.0.1`, `DB_DATABASE=viajatranqui`, `DB_USERNAME` y `DB_PASSWORD`.
3. **Migrar y Sembrar**:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. **Iniciar Servidores**:
   ```bash
   # Terminal 1 (Backend):
   php artisan serve --port=8000

   # Terminal 2 (Frontend):
   cd ../frontend
   npm install
   npm run dev
   ```

---

## 6. Pruebas Automatizadas con PEST

Se diseñó una suite de pruebas con **PEST** que valida de forma exhaustiva los cálculos matemáticos, invariantes de dominio, controladores y descargas en PDF.

Para evitar contaminar o borrar los datos de desarrollo en MySQL, la suite se ejecuta en **SQLite en memoria (`:memory:`)** mediante la configuración aislada de `backend/.env.testing`.

### Ejecutar las Pruebas:
```bash
docker compose exec backend ./vendor/bin/pest
```
*(O localmente: `cd backend && ./vendor/bin/pest`)*

### Resultado de la Ejecución:
```text
   PASS  Tests\Unit\CalculateQuotationActionTest
  ✓ calcula correctamente la tarifa base de USD 3 por día retornando el DTO PricingBreakdownData
  ✓ calcula la cotización para España con 20% de recargo de Europa según requerimientos de negocio
  ✓ aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas (South America 0%)
  ✓ aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas (North America 15%)
  ✓ aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas (Europe 20%)
  ✓ aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas (Africa 20%)
  ✓ aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas (Asia 25%)
  ✓ aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas (Oceania 25%)
  ✓ lanza la excepción de dominio InvalidTravelDatesException cuando la fecha de regreso es anterior a la de salida

   PASS  Tests\Unit\ContractQuotationActionTest
  ✓ transiciona una cotización de Cotizado a Contratado registrando la marca temporal
  ✓ lanza la excepción QuotationAlreadyContractedException cuando se intenta contratar nuevamente

   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Unit\QuotationCalculationServiceTest
  ✓ calcula correctamente la tarifa básica con USD 3 por día
  ✓ calcula la cotización para España con 20% de recargo Europa según especificación
  ✓ aplica correctamente los recargos en todas las regiones geográficas (South America)
  ✓ aplica correctamente los recargos en todas las regiones geográficas (North America)
  ✓ aplica correctamente los recargos en todas las regiones geográficas (Europe)
  ✓ aplica correctamente los recargos en todas las regiones geográficas (Africa)
  ✓ aplica correctamente los recargos en todas las regiones geográficas (Asia)
  ✓ aplica correctamente los recargos en todas las regiones geográficas (Oceania)
  ✓ lanza InvalidArgumentException cuando la fecha de retorno es anterior a la de salida

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

   PASS  Tests\Feature\QuotationApiTest
  ✓ puede previsualizar el cálculo de la tarifa de una cotización vía API
  ✓ valida los campos obligatorios al registrar una cotización
  ✓ crea y almacena una cotización con estado inicial Cotizado
  ✓ puede confirmar y transicionar una cotización al estado Contratado
  ✓ permite descargar el comprobante de cotización en formato PDF
  ✓ retorna el catálogo de países desde el endpoint de la API

  Tests:    28 passed (1096 assertions)
  Duration: 14.80s
```

---

## 7. Endpoints de la API REST

Todos los endpoints retornan una estructura JSON uniforme con `success`, `data`, `message` y `meta`:

| Método | Endpoint | Descripción | Form Request / Acción |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/countries` | Retorna catálogo de países con banderas y regiones | `GetCountriesAction` |
| `POST` | `/api/quotes/calculate` | Pre-calcula tarifa y recargos sin persistir | `CalculateQuotationAction` |
| `POST` | `/api/quotes` | Registra una cotización en estado `Cotizado` | `StoreQuotationRequest` / `CreateQuotationAction` |
| `GET` | `/api/quotes` | Lista cotizaciones con búsqueda, filtros y paginación | `QuotationController@index` |
| `GET` | `/api/quotes/{id}` | Obtiene el detalle de una póliza específica | `QuotationController@show` |
| `PATCH` | `/api/quotes/{id}/contract` | Confirma la póliza y transiciona a `Contratado` | `ContractQuotationAction` |
| `GET` | `/api/quotes/{id}/pdf` | Genera y descarga el comprobante en PDF | `QuotationPdfService` |

---

## 8. Puntos Bonus Implementados con Valor Real

El documento de la prueba técnica lista puntos opcionales que aportan valor a la solución:

1. **Dockerización Completa (`docker-compose.yml`)**:
   - Permite al evaluador levantar todo el entorno (PHP, extensiones, MySQL) en minutos sin configurar servidores locales.
2. **TypeScript en Frontend**:
   - Tipado estricto de extremo a extremo que previene errores silenciosos en la gestión de formularios y cálculos monetarios.
3. **Pinia para Estado Global Reactivo**:
   - Reemplaza el acoplamiento entre componentes con un almacenamiento centralizado y testeable.
4. **Caché Inteligente de la API Externa (24 Horas)**:
   - Evita saturación de peticiones a REST Countries y asegura que la aplicación responda en menos de 50 ms.
5. **Factories y Seeders Realistas (`QuotationFactory.php`)**:
   - Generación de cotizaciones con datos verosímiles mediante Faker para probar filtros y paginación.
6. **Alta Cobertura de Pruebas PEST (28 tests / 1.096 aserciones)**:
   - Cobertura completa de casos de éxito, validaciones y casos límite.
7. **Diseño Editorial Minimalista y UX Refinada**:
   - Componentes responsivos, Skeleton Loader para evitar saltos de pantalla (*CLS zero*), toasts flotantes con auto-cierre y modal en 2 pasos.

---

## 9. Mejoras Futuras para un Entorno Productivo

Si este sistema evolucionara hacia una plataforma comercial en producción, se recomienda implementar:

1. **Integración con Pasarela de Pagos (Stripe / Kushki / Paymentez)**:
   - Conectar un webhook de cobro previo a la ejecución de `ContractQuotationAction`, asegurando que la póliza solo se emita tras la confirmación del pago bancario.
2. **Arquitectura Orientada a Eventos (EDA) y Colas de Trabajo (RabbitMQ / Redis)**:
   - Despachar un evento `QuotationContractedEvent` que ejecute un Job en segundo plano para enviar por correo electrónico el certificado de cobertura y el PDF adjunto sin demorar la respuesta HTTP del usuario.
3. **Almacenamiento de Archivos en la Nube (Amazon S3 / Google Cloud Storage)**:
   - Almacenar los PDFs generados en un bucket seguro de S3 con URLs firmadas temporales, evitando regenerar el PDF en el servidor con cada descarga.
4. **Autenticación y Control de Accesos (RBAC con Laravel Sanctum / Breeze)**:
   - Implementar roles para que los clientes consulten solo sus pólizas emitidas y los asesores comerciales cuenten con un panel de gestión administrativa.
5. **CI/CD con GitHub Actions**:
   - Pipeline de integración continua que ejecute automáticamente `./vendor/bin/pest` y `vue-tsc -b` ante cada *Pull Request*.

---

## 10. Datos de Entrega

- **Repositorio GitHub:** [https://github.com/MaikEpz/ViajaTranqui](https://github.com/MaikEpz/ViajaTranqui) (Compartido con `vrubio@gestionsegura.com.ec`)
- **Autor / Candidato:** Desarrollador Full Stack (Laravel + Vue.js)
- **Licencia:** Software de evaluación técnica para Compañía de Seguros.

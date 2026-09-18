# ViajaTranqui — Sistema de Cotización y Venta de Seguro de Viaje

> **Solución Integral Desarrollada para la Prueba Técnica de Laravel + Vue.js**  
> **Destinatario / Evaluador:** `vrubio@gestionsegura.com.ec`  
> **Repositorio Oficial:** [https://github.com/MaikEpz/ViajaTranqui](https://github.com/MaikEpz/ViajaTranqui)  
> **Estado del Proyecto:** ✅ **100% de Requisitos Cumplidos** | **28/28 Pruebas PEST Aprobadas (1.096 aserciones)** | **Dockerizado**

---

## 🚀 1. Instalación y Puesta en Marcha Rápida

Para facilitar la revisión por parte del evaluador, el proyecto cuenta con un entorno **Docker multi-contenedor** listo para iniciar con un solo comando.

### Opción A: Con Docker (Recomendada — En 2 minutos)

> **Requisitos:** Tener instalado **Docker Desktop** (con Docker Compose v2+) y **Node.js 18+**.

#### Paso 1: Clonar el repositorio
```bash
git clone https://github.com/MaikEpz/ViajaTranqui.git
cd ViajaTranqui
```

#### Paso 2: Levantar el Backend y Base de Datos MySQL
```bash
docker compose up -d --build
```
*Este comando compila la imagen con PHP 8.4 y todas las extensiones requeridas (`pdo_mysql`, `gd`, `intl`, `zip`, `bcmath`), y levanta MySQL 8.0 en el puerto `3306` con healthcheck automático.*

#### Paso 3: Ejecutar Migraciones y Datos de Prueba (Seeder)
```bash
docker compose exec backend php artisan migrate:fresh --seed
```
*Crea la estructura relacional e inserta 15 registros de pólizas de prueba, incluyendo el caso oficial de España por $36 USD.*

#### Paso 4: Levantar el Frontend
En otra terminal o pestaña:
```bash
cd frontend
npm install
npm run dev
```

**¡Listo! Accede a los servicios en tu navegador:**
- 🌐 **Frontend (Aplicación Web Vue 3):** [http://localhost:5173](http://localhost:5173)
- 🔌 **Backend API (Laravel 11):** [http://localhost:8000/api/quotes](http://localhost:8000/api/quotes)

---

### Opción B: Instalación Local Manual (Sin Docker)

Si prefieres ejecutar los servicios directamente en tu entorno local:

1. **Configurar Backend**:
   ```bash
   cd backend
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
2. **Configurar Base de Datos en `.env`**:
   Ajusta tus credenciales locales de MySQL (`DB_HOST=127.0.0.1`, `DB_DATABASE=viajatranqui`, `DB_USERNAME`, `DB_PASSWORD`).
3. **Migrar y Poblar**:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. **Iniciar Servidores**:
   ```bash
   # Terminal 1: Backend Laravel
   php artisan serve --port=8000

   # Terminal 2: Frontend Vue 3
   cd ../frontend
   npm install
   npm run dev
   ```

---

### Ejecución de Pruebas Automatizadas (PEST)
Para ejecutar la suite completa de pruebas unitarias y de integración dentro de Docker:
```bash
docker compose exec backend ./vendor/bin/pest
```
*(O localmente desde `backend/`: `./vendor/bin/pest`)*.  
Las pruebas se ejecutan de manera aislada en **SQLite en memoria (`:memory:`)**, garantizando que nunca se modifique ni borre la base de datos de desarrollo en MySQL.

---

## 📋 2. Abordaje Punto por Punto del Documento de la Prueba Técnica

A continuación se detalla cómo se resolvió de forma rigurosa cada uno de los puntos especificados en el documento oficial de requerimientos:

---

### 📌 Punto 1 del Documento: Cotización

> *Crear una pantalla que permita ingresar como mínimo: Datos del asegurado (Nombres, Apellidos, Identificación, Correo, Fecha de nacimiento) y Datos del viaje (País de destino, Fecha de salida, Fecha de regreso). Implementar validaciones frontend y backend. Poder descargar la cotización en PDF.*

#### Cómo lo abordamos:
1. **Interfaz Intuitiva y Guiada ([QuoteModal.vue](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/frontend/src/components/QuoteModal.vue))**:
   - Diseñado como un modal en 2 pasos claros:
     - **Paso 1:** Formulario con selector interactivo de países (con búsqueda en vivo y banderas), validaciones de fechas (salida $\ge$ hoy, regreso $\ge$ salida) y cálculo dinámico en tiempo real sin recargar la página.
     - **Paso 2:** Desglose transparente de la cotización (días de viaje calculados, tarifa base de $3/día, recargo regional desglosado en porcentaje y valor, y monto total a pagar).
2. **Validación Robusta en Backend ([StoreQuotationRequest.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Http/Requests/StoreQuotationRequest.php))**:
   - Validaciones con reglas estrictas de Laravel: `required`, `email:rfc,dns`, `after_or_equal:today`, `after_or_equal:start_date`, `before:today` para fecha de nacimiento, e integridad de país de destino.
   - Cuenta con el método `toDTO()` que transforma la petición validada directamente en un `QuotationData` inmutable de PHP 8.4.
3. **Descarga Oficial en PDF ([QuotationPdfService.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Services/QuotationPdfService.php))**:
   - Implementado con `barryvdh/laravel-dompdf` bajo la plantilla [quotation.blade.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/resources/views/pdf/quotation.blade.php).
   - Genera un comprobante oficial de seguro con isotipo corporativo embebido en Base64, bandera del país destino, número de póliza `#VTQ-XXXXX`, datos completos del titular, vigencia y desglose financiero.

---

### 📌 Punto 2 del Documento: Integración con API Externa (REST Countries)

> *La lista de países deberá obtenerse desde una API pública (REST Countries API https://restcountries.com/). Se valorará el manejo adecuado de errores del servicio externo, timeouts, respuestas inesperadas y disponibilidad temporal del API.*

#### Cómo lo abordamos:
Se diseñó un **Circuito de Resiliencia en 4 Capas** desacoplado mediante la interfaz [CountryProviderInterface.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Domain/Contracts/CountryProviderInterface.php) y el adaptador [RestCountriesAdapter.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Infrastructure/Adapters/RestCountriesAdapter.php):

1. **Timeout Estricto de 5 Segundos**: Si `restcountries.com` tiene latencia o caídas, la petición no bloquea ni congela la aplicación (`Http::timeout(5)`).
2. **Fallback Automático a Espejo Público**: Si la API principal retorna error HTTP (5xx, 404) o respuestas inesperadas (como cambios en su esquema JSON), el adaptador conmuta inmediatamente al repositorio abierto `mledoze/countries` en GitHub.
3. **Catálogo Offline Empaquetado**: Si el servidor experimenta una pérdida total de conexión a internet, el sistema activa un catálogo local empaquetado con países, regiones y banderas, garantizando 100% de disponibilidad.
4. **Caché Inteligente de 24 Horas**: Laravel almacena la lista de países en caché (`Cache::remember('countries_catalog_v2', 86400)`), reduciendo drásticamente el consumo de red y logrando respuestas en menos de **40 ms**.

---

### 📌 Punto 3 del Documento: Cálculo de la Cotización

> *Tarifa base USD 3 por cada día de viaje (Ej: 10 días = $30). Recargo según región: South America 0%, North America 15%, Europe 20%, Asia 25%, Africa 20%, Oceania 25%. Ejemplo: Viaje de 10 días a España = $30 base + $6 Europa (20%) = $36 total. La forma en que esta lógica sea organizada dentro de Laravel queda a criterio del desarrollador; se evaluará especialmente este punto.*

#### Cómo lo abordamos:
1. **Caso de Uso Puro en Dominio ([CalculateQuotationAction.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Domain/Actions/CalculateQuotationAction.php))**:
   - La lógica matemática no reside en controladores ni en componentes Vue. Es una clase de dominio pura con una única responsabilidad (**SRP**).
   - Cálculo inclusivo de días: `days_count = Carbon::parse(start)->diffInDays(end) + 1`.
   - Cálculo de tarifa base: `base_amount = days_count * 3.00`.
   - Mapeo inmutable de recargos regionales por continente y cálculo del total:
     $$\text{Total} = \text{Monto Base} + (\text{Monto Base} \times \frac{\% \text{ Recargo}}{100})$$
   - Retorna el DTO inmutable [PricingBreakdownData.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Domain/DTOs/PricingBreakdownData.php).
2. **Verificación del Caso Oficial de Negocio**:
   - **España, 10 días:** Base $30.00 + Recargo Europa 20% ($6.00) = **$36.00 USD**.
   - Este caso exacto está insertado como el **Registro #1** de la base de datos (`QuotationSeeder.php`), listo para ser consultado y descargado en PDF.
   - Avalado por pruebas automatizadas específicas en `CalculateQuotationActionTest.php` y `QuotationCalculationServiceTest.php`.

---

### 📌 Punto 4 del Documento: Confirmación de Contratación

> *Después de generar una cotización, el usuario podrá seleccionar "Contratar seguro". Al confirmar deberá almacenarse: asegurado, destino, fechas del viaje, cantidad de días, tarifa base, porcentaje de recargo, valor total, fecha de contratación y estado (mínimos: Cotizado, Contratado). No es necesario pasarela de pagos real.*

#### Cómo lo abordamos:
1. **Acción Transaccional de Contratación ([ContractQuotationAction.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Domain/Actions/ContractQuotationAction.php))**:
   - Transiciona atómicamente el estado del registro de `Cotizado` a `Contratado` y sella la marca temporal `contracted_at = now()`.
2. **Invariante de Dominio contra Doble Contratación**:
   - Si una póliza ya fue confirmada previamente, la acción arroja de inmediato la excepción de dominio [QuotationAlreadyContractedException.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Domain/Exceptions/QuotationAlreadyContractedException.php), la cual es transformada en una respuesta HTTP semántica 422.
3. **Flujo de Usuario Ágil**:
   - El usuario puede contratar inmediatamente desde el modal tras ver la cotización, o posteriormente desde la tabla de consulta de seguros.
   - Notificación de confirmación mediante un **Toast flotante en la esquina** sin saltos de página.

---

### 📌 Punto 5 del Documento: Consulta de Contrataciones

> *Crear una pantalla donde se puedan visualizar las cotizaciones/seguros registrados: Cliente, Identificación, Destino, Fecha de salida, Fecha de regreso, Valor, Estado, Fecha de creación. Puede utilizarse paginación, filtros o búsqueda.*

#### Cómo lo abordamos:
1. **Componente de Consulta Editorial ([QuotationsList.vue](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/frontend/src/components/QuotationsList.vue))**:
   - Reúne el 100% de los campos requeridos en una **tabla compacta de 6 columnas de alta densidad**:
     - **Póliza & Emisión:** Código `#VTQ-XXXXX` + Fecha de creación.
     - **Asegurado & Contacto:** Nombre completo + Cédula/Pasaporte + Correo electrónico.
     - **Destino:** Bandera + País + Región geográfica.
     - **Vigencia & Días:** Rango limpio `DD/MM/YYYY → DD/MM/YYYY` + Badge con cantidad de días de cobertura.
     - **Total & Estado:** Importe monetario destacado en USD + Badge de estado (*Cotizado* o *Contratado*).
     - **Acciones:** Botón **PDF** (descarga instantánea) y botón **Contratar** (o badge `✓ Activa` si ya fue contratada).
2. **Skeleton Loader sin Salto de Pantalla (*CLS Zero*)**:
   - Durante la carga inicial de los datos, la pantalla muestra una tabla Skeleton con efecto *shimmer* que replica exactamente la estructura y anchura final, **eliminando cualquier salto brusco de tamaño (*layout shift*)**.
3. **Búsqueda y Filtros en Tiempo Real**:
   - Buscador por texto con *debounce* de 350 ms (búsqueda por nombre, identificación, país o correo).
   - Selector de filtro por estado (*Todos*, *Solo Cotizados*, *Solo Contratados*).
   - Paginación ágil en servidor (`per_page: 15`).

---

### 📌 Punto 6 del Documento: Base de Datos

> *La estructura de la base de datos deberá construirse utilizando Migraciones de Laravel, Relaciones Eloquent y Seeders. No se deberá entregar un archivo SQL como mecanismo principal.*

#### Cómo lo abordamos:
1. **Migración Relacional ([create_quotations_table.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/database/migrations/2026_09_18_010856_create_quotations_table.php))**:
   - Tipos de datos estrictos (`decimal:10,2` para montos, `date` para fechas).
   - Índices para acelerar búsquedas: `index(['status', 'created_at'])`, `index('identification_number')`, `index('destination_region')`.
2. **Modelo Eloquent con Scopes y Casts ([Quotation.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/app/Models/Quotation.php))**:
   - `scopeSearch($query, $term)`: Búsqueda flexible multicampo.
   - `scopeByStatus($query, $status)`: Filtro condicional por estado.
   - Casts estrictos `'start_date' => 'date:Y-m-d'` para garantizar que la API entregue fechas limpias sin basura ISO.
3. **Seeders y Factories ([QuotationSeeder.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/database/seeders/QuotationSeeder.php) y [QuotationFactory.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/database/factories/QuotationFactory.php))**:
   - Población de 15 registros iniciales coherentes utilizando Faker, respetando las reglas de cálculo regional.

---

### 📌 Punto 7 del Documento: Arquitectura

> *Libertad para definir la arquitectura. Debe evitar concentrar toda la lógica de negocio directamente dentro de Controllers o componentes Vue. Explicar brevemente en el README la arquitectura seleccionada y las razones de su elección.*

#### Por qué elegimos Clean Architecture Pragmática (Action-Driven):
- **Desacoplamiento Real**: La lógica de negocio no sabe si se ejecuta desde una petición HTTP, un comando Artisan de consola o una prueba unitaria.
- **Principio de Responsabilidad Única (SRP)**: Cada caso de uso es una clase individual (`CalculateQuotationAction`, `CreateQuotationAction`, `ContractQuotationAction`).
- **Inversión de Dependencias (DIP)**: El dominio interactúa con proveedores de datos a través de contratos (`CountryProviderInterface`).
- **Controladores Delgados (*Slim Controllers*)**: `QuotationController.php` solo orquesta y retorna respuestas JSON estandarizadas.

---

### 📌 Punto 8 del Documento: Backend Laravel

> *Se evaluará el uso adecuado de Laravel: Routing, Controllers, Form Requests, Models, Eloquent, Migrations, Services o Actions, Manejo de excepciones, Responses, Validaciones y Buenas prácticas.*

#### Elementos implementados:
- **Routing:** Rutas agrupadas y semánticas en [api.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/routes/api.php).
- **Form Requests:** `StoreQuotationRequest` y `CalculateQuotationRequest` con mensajes personalizados en español.
- **Manejo de Excepciones:** Errores de dominio capturados y transformados en respuestas JSON con código 422 y mensajes claros.
- **Respuestas JSON Consistentes:** Estructura unificada en toda la API: `{ success: bool, data: any, message: string, meta?: object }`.

---

### 📌 Punto 9 del Documento: Frontend Vue.js

> *La interfaz deberá desarrollarse utilizando Vue.js. No buscamos un diseño gráfico complejo. Debe funcionar correctamente en escritorio y dispositivos móviles.*

#### Elementos implementados:
- **Vue 3 Composition API (`<script setup>`)**: Estilo moderno y estándar de Vue.
- **Pinia ([quotationStore.ts](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/frontend/src/stores/quotationStore.ts))**: Gestión de estado centralizada para modales, alertas y datos.
- **Diseño Editorial Minimalista**: Tipografía Plus Jakarta Sans, paleta monocromática elegante, contrastes legibles y 100% responsivo para móviles y escritorios.
- **Toasts Flotantes**: Notificaciones compactas en la esquina inferior derecha con auto-cierre a los 4.5 segundos.

---

### 📌 Punto 10 del Documento: Pruebas Automatizadas con PEST

> *Implementar al menos algunas pruebas automatizadas, usar PEST. Se evaluará qué decide probar el desarrollador y cómo estructura las pruebas.*

#### Cobertura completa (28 pruebas / 1.096 aserciones):
1. **Pruebas Unitarias de Dominio ([CalculateQuotationActionTest.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/tests/Unit/CalculateQuotationActionTest.php))**:
   - Tarifa base de USD 3/día.
   - Recargo del caso oficial de España (20%).
   - Recargos de cada una de las 6 regiones geográficas (0%, 15%, 20%, 25%).
   - Excepción de fechas de viaje inconsistentes (`InvalidTravelDatesException`).
2. **Pruebas Unitarias de Contratación ([ContractQuotationActionTest.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/tests/Unit/ContractQuotationActionTest.php))**:
   - Transición de estado a `Contratado` y registro de fecha.
   - Bloqueo ante doble contratación (`QuotationAlreadyContractedException`).
3. **Pruebas de Integración y API ([QuotationApiTest.php](file:///c:/Users/maikpc/OneDrive/Desktop/Programas/ViajaTranqui/backend/tests/Feature/QuotationApiTest.php))**:
   - Cálculo preliminar vía API (`POST /api/quotes/calculate`).
   - Validación de campos requeridos (error 422).
   - Creación de cotización (`POST /api/quotes`).
   - Confirmación de contratación vía API (`PATCH /api/quotes/{id}/contract`).
   - Descarga de PDF verificando cabecera `application/pdf` (`GET /api/quotes/{id}/pdf`).
   - Consulta de catálogo de países (`GET /api/countries`).

---

### 📌 Punto 11 del Documento: Mejoras Futuras para Producción

> *Indicar qué aspectos mejoraría si el proyecto evolucionara hacia un sistema de producción.*

1. **Pasarela de Pagos en Línea (Stripe / Kushki / Paymentez)**:
   - Implementar webhooks de confirmación bancaria antes de ejecutar `ContractQuotationAction`.
2. **Eventos de Dominio y Colas Asíncronas (RabbitMQ / Redis / SQS)**:
   - Disparar `QuotationContractedEvent` para procesar en segundo plano el envío del correo electrónico con el certificado PDF adjunto sin afectar el tiempo de respuesta del usuario.
3. **Almacenamiento de PDFs en la Nube (Amazon S3 / Google Cloud Storage)**:
   - Almacenar los comprobantes emitidos en un bucket seguro con URLs firmadas temporales en lugar de renderizarlos al vuelo en cada descarga.
4. **Autenticación y Roles (Laravel Sanctum / Breeze)**:
   - Portal de autoservicio para asegurados y panel administrativo para agentes de ventas con control de accesos basado en roles (RBAC).
5. **Pipeline CI/CD con GitHub Actions**:
   - Automatización de pruebas PEST, análisis estático con PHPStan (nivel 8) y chequeo de tipos TypeScript (`vue-tsc`).

---

### 📌 Punto 12 del Documento: Puntos Bonus Implementados

| Bonus Opcional | Implementado | Detalle de Valor Agregado |
| :--- | :---: | :--- |
| **Docker** | **Sí** | `docker-compose.yml` multi-contenedor listo para producción y evaluación. |
| **TypeScript** | **Sí** | Tipado estricto en frontend con DTOs e interfaces de contratos. |
| **Pinia** | **Sí** | Store reactivo desacoplado para manejo del flujo de cotización. |
| **Factories** | **Sí** | `QuotationFactory.php` con Faker realista para pruebas. |
| **Caché de API externa** | **Sí** | Caché de 24 horas en Laravel con fallbacks escalonados. |
| **Mayor cobertura de pruebas** | **Sí** | 28 pruebas automatizadas PEST pasando con 1.096 aserciones. |
| **Diseño UI refinado** | **Sí** | Skeleton Loader, Toasts flotantes, diseño editorial minimalista y cero CLS. |

---

## 📬 3. Datos de Entrega

- **Repositorio en GitHub:** [https://github.com/MaikEpz/ViajaTranqui](https://github.com/MaikEpz/ViajaTranqui)
- **Invitación de Colaborador:** Enviada a `vrubio@gestionsegura.com.ec`
- **Autor / Candidato:** Desarrollador Full Stack (Laravel + Vue.js)
- **Licencia:** Código desarrollado para evaluación técnica de contratación.

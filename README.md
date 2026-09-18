# ViajaTranqui - Sistema de Cotización y Venta de Seguro de Viaje

> Solución integral desarrollada para la prueba técnica de **Laravel + Vue.js** para la compañía de seguros.
> **Evaluador:** `vrubio@gestionsegura.com.ec`

---

## 1. Arquitectura y Decisiones Técnicas: Clean Architecture Pragmática

Para maximizar la mantenibilidad, testeabilidad y cumplimiento estricto de los principios **SOLID**, el backend implementa una **Arquitectura Limpia Pragmática (Clean Architecture / Action-Driven)** desacoplada del framework Laravel:

```
backend/app/
├── Domain/                                 # 🧠 CAPA DE DOMINIO (Reglas de negocio puras)
│   ├── Contracts/                          # Inversión de Dependencias (DIP)
│   │   └── CountryProviderInterface.php    # Contrato desacoplado de proveedores de países
│   ├── DTOs/                               # Data Transfer Objects inmutables (PHP 8.4 readonly)
│   │   ├── CountryData.php
│   │   ├── PricingBreakdownData.php
│   │   └── QuotationData.php
│   ├── Exceptions/                         # Excepciones semánticas de dominio
│   │   ├── InvalidTravelDatesException.php
│   │   └── QuotationAlreadyContractedException.php
│   └── Actions/                            # Casos de Uso (Single Responsibility Principle)
│       ├── CalculateQuotationAction.php    # Cálculo puro de días, tarifa base y recargos
│       ├── CreateQuotationAction.php       # Orquestación de creación y persistencia
│       ├── ContractQuotationAction.php     # Transición de estado con invariantes de dominio
│       └── GetCountriesAction.php          # Caso de uso de obtención de catálogo
│
├── Infrastructure/                         # 🔌 CAPA DE INFRAESTRUCTURA (I/O, APIs externas, PDFs)
│   └── Adapters/
│       ├── RestCountriesAdapter.php        # Implementa CountryProviderInterface (Caché + Fallbacks)
│       └── DomPdfQuotationGenerator.php    # Adaptador para renderizado de PDFs
│
├── Http/                                   # 🌐 CAPA DE ENTRADA / PRESENTACIÓN
│   ├── Controllers/                        # Controladores delgados (Slim Controllers)
│   │   ├── CountryController.php
│   │   └── QuotationController.php
│   └── Requests/                           # Validaciones desacopladas + Transformación a DTO
│       ├── CalculateQuotationRequest.php
│       └── StoreQuotationRequest.php       # Expone método toDTO()
│
└── Models/                                 # Persistencia Eloquent con Scopes y Casts
    └── Quotation.php
```

### Principios de Clean Architecture Aplicados:

1. **Casos de Uso Aislados (*Actions*)**:
   - Cada operación clave del sistema es una clase individual con un único método ejecutor (`execute()`). Esto respeta el principio de responsabilidad única (**SRP**) y permite testear cada flujo sin acoplarse a controladores HTTP.
2. **Inversión de Dependencias (*DIP*) con Contratos**:
   - La capa de dominio define el contrato `CountryProviderInterface`.
   - La capa de infraestructura provee la implementación `RestCountriesAdapter` que se registra en el contenedor de servicios de Laravel (`AppServiceProvider`). El dominio desconoce si los países provienen de una API externa, una base de datos local o un archivo JSON.
3. **Data Transfer Objects (*DTOs*) Inmutables**:
   - Se utilizan clases `readonly` de PHP 8.4 (`QuotationData`, `PricingBreakdownData`, `CountryData`) para transportar información validada entre capas, evitando el paso de arrays asociativos sin tipar.
4. **Excepciones de Dominio**:
   - Errores de negocio como fechas inconsistentes (`InvalidTravelDatesException`) o intentos de doble contratación (`QuotationAlreadyContractedException`) son modelados explícitamente en el dominio y capturados por la capa HTTP.
5. **Resiliencia con Degradación Elegante (*Graceful Degradation*)**:
   - `RestCountriesAdapter` implementa un circuito de tolerancia a fallos:
     1. Petición con timeout a la API primaria de REST Countries.
     2. En caso de timeout o respuesta inesperada (como mensajes de depreciación de versión), conmuta automáticamente al espejo abierto `mledoze/countries`.
     3. Catálogo empaquetado offline como última línea de defensa.
     4. Caché de 24 horas en Laravel para máximo rendimiento.

---

## 2. Requisitos Previos

- **Docker Desktop** (con Docker Compose v2+)
- **Node.js** v18+ y NPM (para el frontend)
- **Git**

---

## 3. Instrucciones de Instalación y Ejecución Rápida

### Paso 1: Clonar el repositorio
```bash
git clone <URL_DEL_REPOSITORIO>
cd ViajaTranqui
```

### Paso 2: Levantar el Backend y Base de Datos con Docker
```bash
# Construir y levantar contenedores en segundo plano (Backend en puerto 8000, MySQL en 3306)
docker compose up -d --build
```

### Paso 3: Ejecutar Migraciones y Datos de Prueba
```bash
# Ejecutar migraciones en MySQL
docker compose exec backend php artisan migrate

# Poblar con datos de prueba (incluye el caso del enunciado: España 10 días = $36)
docker compose exec backend php artisan db:seed
```

### Paso 4: Levantar el Frontend (Vue 3 + Vite + Pinia)
En una terminal:
```bash
cd frontend
npm install
npm run dev
```

La aplicación web estará disponible de inmediato en:  
👉 **Frontend:** [http://localhost:5173](http://localhost:5173)  
👉 **Backend API:** [http://localhost:8000](http://localhost:8000)

---

## 4. Pruebas Automatizadas con PEST

Para ejecutar la suite de pruebas unitarias y de integración dentro del contenedor:
```bash
docker compose exec backend ./vendor/bin/pest
```

Resultado verificado:
```text
PASS  Tests\Unit\CalculateQuotationActionTest
✓ it calculates basic pricing with USD 3 per day correctly as PricingBreakdownData DTO
✓ it calculates Spain pricing with 20% Europe surcharge as defined in business requirements
✓ it correctly applies surcharges across all defined geographical regions in domain action
✓ it throws domain InvalidTravelDatesException when return date is before departure date

PASS  Tests\Unit\ContractQuotationActionTest
✓ it transitions a quotation from Cotizado to Contratado with timestamp
✓ it throws QuotationAlreadyContractedException when quotation is already contracted

PASS  Tests\Unit\QuotationCalculationServiceTest
✓ it calculates basic pricing with USD 3 per day correctly
✓ it calculates pricing for Spain with 20% Europe surcharge as defined in specifications
✓ it correctly applies surcharges across all defined geographical regions
✓ it throws InvalidArgumentException when end date is earlier than start date

PASS  Tests\Feature\QuotationApiTest
✓ it can preview a quotation calculation via API
✓ it validates required fields when storing a quotation
✓ it creates a quotation with Cotizado status
✓ it can transition quotation to Contratado status
✓ it allows downloading quotation PDF
✓ it returns countries list from API

Tests:    28 passed (1096 assertions)
```

---

## 5. Endpoints de la API REST

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/api/countries` | Obtiene el catálogo de países con banderas y regiones |
| `POST` | `/api/quotes/calculate` | Pre-calcula tarifa y recargos mediante `CalculateQuotationAction` |
| `GET` | `/api/quotes` | Lista cotizaciones con filtros (`search`, `status`) y paginación |
| `POST` | `/api/quotes` | Registra una nueva cotización vía `CreateQuotationAction` y DTO |
| `GET` | `/api/quotes/{id}` | Obtiene los detalles de una cotización específica |
| `PATCH` | `/api/quotes/{id}/contract` | Confirma la contratación vía `ContractQuotationAction` |
| `GET` | `/api/quotes/{id}/pdf` | Descarga el documento oficial de cotización en PDF |

---

## 6. Mejoras Futuras para un Entorno Productivo

1. **Integración con Pasarela de Pagos**:
   - Conexión con Stripe o Paymentez para cobrar la póliza antes de ejecutar `ContractQuotationAction`.
2. **Eventos de Dominio y Procesamiento Asíncrono**:
   - Disparar `QuotationContractedEvent` al confirmar la póliza para despachar colas de trabajo (RabbitMQ/Redis) que envíen el correo electrónico con el comprobante PDF adjunto.
3. **Almacenamiento de Archivos en la Nube**:
   - Configurar almacenamiento de PDFs generados en Amazon S3 en lugar de renderizarlos al vuelo.
4. **Autenticación y Roles**:
   - Integración con Laravel Sanctum para restringir la consulta de pólizas a asesores autorizados.

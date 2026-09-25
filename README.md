# Mi Presupuesto — Backend (Laravel API)

API REST en Laravel que le da datos reales a la app de Flutter: planillas
(ingresos), descuentos, presupuesto por categoría, gastos y metas de ahorro
con sus aportes. Este documento explica cómo está armado, cómo funciona por
dentro, y cómo ponerlo en producción.

## 1. Arquitectura general

```
┌─────────────────────┐        HTTPS + Bearer token        ┌──────────────────────┐
│   App Flutter        │ ───────────────────────────────▶  │   API Laravel          │
│  (Android, Web/PWA)  │ ◀───────────────────────────────  │  (este proyecto)       │
└─────────────────────┘             JSON                   └──────────┬───────────┘
                                                                        │
                                                                        ▼
                                                              ┌──────────────────┐
                                                              │  MySQL / Postgres │
                                                              └──────────────────┘
```

- La app de Flutter **no** habla directo con la base de datos: todo pasa por
  esta API. Así puedes usar la app desde el celular y desde la web y ver
  siempre los mismos datos (eso era el requisito de "sincronizado en la
  nube" que definimos al inicio).
- La autenticación es por **token** (Laravel Sanctum), no por cookies/sesión
  — es el modelo correcto para un cliente móvil. Cada dispositivo donde
  inicias sesión obtiene su propio token, y puedes revocar uno solo o todos.
- Es una API multiusuario por diseño (cada fila tiene `user_id`), aunque hoy
  la vayas a usar solo tú. Si más adelante quieres compartirla con tu pareja
  (la mencionaste como posibilidad), la base ya lo soporta sin cambios
  grandes.

## 2. Modelo de datos

```
users
 ├─ income_sources        (plantillas: "Salario base", "Bono 14"...)
 ├─ deduction_rules        (reglas: "Cuota IGSS" = 4.83%)
 ├─ pay_periods             (una planilla real)
 │    ├─ pay_period_incomes     (líneas de ingreso de esa planilla)
 │    └─ pay_period_deductions  (descuentos ya calculados de esa planilla)
 ├─ budget_categories       ("Renta", "Comida"...)
 │    └─ budget_expenses        (gastos registrados en esa categoría)
 └─ savings_goals            ("Enganche de casa", "Fondo de emergencia"...)
      └─ savings_contributions    (aportes hacia esa meta)
```

Puntos importantes de diseño:

- **`income_sources` y `deduction_rules` son plantillas**, configuradas una
  vez en Ajustes. Cuando registras una planilla nueva, se copian sus valores
  como líneas nuevas en `pay_period_incomes` / `pay_period_deductions`. Esto
  es intencional: si en enero cambias el porcentaje del IGSS, las planillas
  de meses anteriores no deben recalcularse solas — quedan con el monto que
  realmente se aplicó ese mes.
- **Nada de saldos guardados aparte.** El monto ahorrado de una meta no es
  una columna: se calcula sumando `savings_contributions` cada vez
  (`SavingsGoal::currentAmount`). Así nunca puede desincronizarse del
  historial. Lo mismo con lo gastado de una categoría (`spentThisMonth`).
- **Multiusuario y multi-meta desde el inicio**, tal como lo pediste:
  `savings_goals` es una tabla, no una fila única, así que puedes tener
  cuantas metas quieras.

## 3. Cómo funcionan los cálculos

- **Bruto de una planilla** = suma de `pay_period_incomes.amount`.
- **Descuento** = se guarda ya resuelto (`base_amount * percentage`) al
  crear la planilla — no se recalcula después.
- **Neto** = bruto − descuentos.
- **Disponible de una categoría** = `monthly_budget − spentThisMonth`.
- **Progreso de una meta** = `currentAmount / target_amount`, nunca mayor a
  1 (100%).

Todo esto vive en los *accessors* de los modelos (`app/Models/*.php`) y se
expone ya calculado en los *API Resources* (`app/Http/Resources/*.php`), para
que el cliente (Flutter) nunca tenga que repetir esta lógica.

## 4. Estructura de archivos de este paquete

Este zip **no es un proyecto Laravel completo** — son los archivos propios
de la app para copiar dentro de un proyecto Laravel recién creado (ver
sección 5). Por eso no incluye `vendor/`, `bootstrap/app.php`,
`app/Http/Kernel.php`, etc. — esos los genera Laravel mismo.

```
app/
  Models/                  (9 modelos: User + 8 propios)
  Http/
    Controllers/Api/       (8 controladores)
    Requests/               (9 Form Requests — validación)
    Resources/              (3 API Resources — formato de respuesta)
database/
  migrations/              (9 migraciones, en orden)
  seeders/                 (DatabaseSeeder + DemoSeeder)
routes/
  api.php                  (todas las rutas de la API)
```

## 5. Instalación local

Requisitos: PHP 8.2+, Composer, y MySQL/PostgreSQL (o nada — puedes empezar
con SQLite, cero configuración, y migrar a MySQL/Postgres cuando despliegues).

```bash
# 1. Crear el proyecto base
composer create-project laravel/laravel presupuesto-api
cd presupuesto-api

# 2. Agregar Sanctum (autenticación por token)
composer require laravel/sanctum
php artisan install:api

# 3. Copiar los archivos de este paquete DENTRO del proyecto,
#    reemplazando lo que ya exista (especialmente app/Models/User.php
#    y database/seeders/DatabaseSeeder.php)

# 4. Base de datos — la opción más rápida para probar en tu PC:
touch database/database.sqlite
```

En tu `.env`, si usas SQLite:

```env
DB_CONNECTION=sqlite
# comenta o borra las líneas DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

O si prefieres MySQL/PostgreSQL directamente (lo que usarás en producción):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presupuesto
DB_USERNAME=root
DB_PASSWORD=
```

Luego:

```bash
php artisan migrate --seed
php artisan serve
```

Esto deja la API corriendo en `http://127.0.0.1:8000`, con un usuario de
prueba ya cargado: `diego@example.com` / `password`, y todas las planillas,
categorías y metas de ejemplo que ya conoces del diseño.

## 6. Referencia de la API

Todas las rutas (excepto `/register` y `/login`) requieren el header:

```
Authorization: Bearer <token>
```

### Autenticación

| Método | Ruta                | Body                                             | Descripción                          |
|--------|---------------------|---------------------------------------------------|---------------------------------------|
| POST   | `/api/register`     | `name, email, password, password_confirmation`     | Crea cuenta y devuelve token           |
| POST   | `/api/login`        | `email, password, device_name`                     | Devuelve un token nuevo para ese dispositivo |
| GET    | `/api/me`           | —                                                   | Datos del usuario autenticado          |
| POST   | `/api/logout`       | —                                                   | Revoca el token actual (este dispositivo) |
| POST   | `/api/logout-all`   | —                                                   | Revoca todos los tokens del usuario    |

### Fuentes de ingreso y descuentos (plantillas, se configuran en Ajustes)

| Método | Ruta                        | Body                                    |
|--------|-----------------------------|-------------------------------------------|
| GET    | `/api/income-sources`       | —                                          |
| POST   | `/api/income-sources`       | `label, default_amount, is_recurring`      |
| PUT    | `/api/income-sources/{id}`  | igual que store                            |
| DELETE | `/api/income-sources/{id}`  | —                                          |
| GET    | `/api/deduction-rules`      | —                                          |
| POST   | `/api/deduction-rules`      | `label, percentage` (ej. `4.83`, no `0.0483`) |
| PUT    | `/api/deduction-rules/{id}` | igual que store                            |
| DELETE | `/api/deduction-rules/{id}` | —                                          |

### Planillas

| Método | Ruta                    | Body / Notas |
|--------|-------------------------|--------------|
| GET    | `/api/pay-periods`      | Lista con bruto/neto ya calculados |
| GET    | `/api/pay-periods/{id}` | Detalle de una planilla |
| POST   | `/api/pay-periods`      | Ver ejemplo abajo |
| DELETE | `/api/pay-periods/{id}` | — |

Ejemplo de body para crear una planilla:

```json
{
  "period_start": "2026-10-01",
  "period_end": "2026-10-31",
  "pay_date": "2026-11-05",
  "payment_method": "Transferencia",
  "work_schedule": "tiempo_completo",
  "incomes": [
    { "label": "Salario base", "amount": 5750 },
    { "label": "Bonificación incentivo", "amount": 250 }
  ],
  "deductions": [
    { "deduction_rule_id": 1, "base_amount": 5750 }
  ]
}
```

### Presupuesto

| Método | Ruta                             | Body |
|--------|----------------------------------|------|
| GET    | `/api/budget-categories`         | Incluye `spent_this_month` y `remaining` ya calculados |
| POST   | `/api/budget-categories`         | `name, monthly_budget` |
| PUT    | `/api/budget-categories/{id}`    | igual que store |
| DELETE | `/api/budget-categories/{id}`    | — |
| GET    | `/api/budget-expenses`           | Filtros opcionales: `?budget_category_id=`, `?month=2026-09` |
| POST   | `/api/budget-expenses`           | `budget_category_id, amount, note, spent_at` — esto es lo que llama la pantalla "Agregar gasto" |
| DELETE | `/api/budget-expenses/{id}`      | — |

### Ahorro

| Método | Ruta                                                  | Body |
|--------|--------------------------------------------------------|------|
| GET    | `/api/savings-goals`                                    | Incluye `current_amount` y `progress` ya calculados |
| GET    | `/api/savings-goals/{id}`                                | Incluye el detalle de aportes |
| POST   | `/api/savings-goals`                                     | `name, target_amount, target_date` |
| PUT    | `/api/savings-goals/{id}`                                | igual que store |
| DELETE | `/api/savings-goals/{id}`                                | — |
| POST   | `/api/savings-goals/{id}/contributions`                  | `amount, note, contributed_at` |
| DELETE | `/api/savings-goals/{id}/contributions/{contribution}`   | — |

## 7. Conectando la app de Flutter (siguiente paso)

Hoy la app usa `lib/data/mock_data.dart`. Para conectarla a esta API:

1. Guardar el `token` que devuelve `/login` con `flutter_secure_storage`
   (nunca en `SharedPreferences` sin cifrar).
2. Mandarlo en cada petición: header `Authorization: Bearer <token>`.
3. Reemplazar cada `MockData.algo` por una llamada `http.get(...)` a su
   endpoint equivalente de la tabla de arriba.

Si quieres, en otra sesión armamos esa capa de red en Flutter (un
`ApiClient` + los modelos ya con `fromJson`) para dejarlo conectado de
verdad.

## 8. Despliegue a producción

Antes que nada, en tu `.env` de producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio-o-subdominio
```

Y genera una `APP_KEY` nueva para producción (`php artisan key:generate`) —
nunca reutilices la de tu entorno local.

### Opción A — PaaS (la más simple para un proyecto personal)

Servicios como **Railway** o **Render** despliegan un repo de Laravel
directamente desde GitHub, con base de datos MySQL/PostgreSQL administrada
incluida:

1. Sube el proyecto a un repositorio de GitHub.
2. Crea el servicio en Railway/Render apuntando a ese repo.
3. Agrega una base de datos MySQL o PostgreSQL desde el mismo panel — te da
   las variables `DB_HOST`, `DB_DATABASE`, etc. listas para pegar en las
   variables de entorno del servicio.
4. En el comando de arranque (build/start), agrega:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   ```
5. Copia la URL pública que te da la plataforma (algo como
   `https://presupuesto-api.up.railway.app`) — esa es la que pondrás como
   base URL en la app de Flutter.

### Opción B — VPS propio (DigitalOcean, Linode, Vultr)

Dado que ya administras infraestructura en tu trabajo, esta ruta también es
viable si prefieres tener control total:

1. Droplet/VM con Ubuntu 24.04, PHP 8.2+, Nginx, MySQL o PostgreSQL,
   Composer y Node (si luego agregas algo que lo requiera).
2. Clona el repo, `composer install --no-dev --optimize-autoloader`.
3. Configura Nginx apuntando a `public/index.php` (el `document root` de
   cualquier proyecto Laravel es la carpeta `public/`, nunca la raíz del
   proyecto).
4. `php artisan migrate --force`, `php artisan config:cache`.
5. Certificado SSL gratis con **Certbot** (Let's Encrypt) — obligatorio,
   ya que los tokens de Sanctum viajan en el header y deben ir siempre por
   HTTPS.
6. Opcional: **Supervisor** si más adelante agregas colas (`queue:work`) —
   hoy la app no las necesita, todo se procesa síncrono.

### CORS (solo si usas la versión PWA)

Si vas a abrir la app desde la versión web (PWA) en Safari, agrega el
dominio donde la publiques a `config/cors.php`:

```php
'allowed_origins' => ['https://tu-pwa.pages.dev'],
```

Esto no aplica a la versión Android nativa — CORS es una restricción del
navegador, no de apps móviles.

## 9. Seguridad — cosas a no pasar por alto

- Los passwords se guardan con `Hash::make` (bcrypt) — nunca en texto plano.
- Considera agregar *rate limiting* al login (`throttle:6,1` en la ruta) si
  la API llega a estar expuesta públicamente por mucho tiempo — Laravel lo
  trae listo, solo hay que agregarlo a la ruta.
- Cada request valida que el recurso pertenezca al usuario autenticado
  (`abort_unless($modelo->user_id === $request->user()->id, 403)`), para
  que un token nunca pueda leer o borrar datos de otra cuenta.
- `logout-all` es útil si alguna vez pierdes el teléfono: revoca todos los
  tokens activos desde cualquier otro dispositivo donde tengas sesión.

## 10. Qué falta todavía

- Conectar la app de Flutter a esta API (hoy usa datos de ejemplo).
- Endpoints de edición para planillas ya creadas (hoy solo hay crear/borrar).
- La idea que mencionamos de leer el PDF de Nomiplex para pre-llenar una
  planilla automáticamente — quedó como posible fase 2, no bloquea nada de
  lo anterior.

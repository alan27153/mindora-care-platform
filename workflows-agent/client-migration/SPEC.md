# 1. Objetivo

Migrar completamente el flujo **Client** actualmente implementado en n8n hacia el backend Java + Quarkus existente, manteniendo su comportamiento funcional y eliminando la dependencia funcional de n8n.

El resultado debe ser un módulo independiente dentro del backend:

```text
io.mindoracare.client
```

El flujo migrado debe exponerse directamente mediante un endpoint HTTP propio del módulo Client.

El cliente externo debe comunicarse directamente con este endpoint.

El flujo final debe ejecutarse dentro del backend Java + Quarkus, sin utilizar n8n como dependencia funcional.

La arquitectura concreta del módulo debe adaptarse a las convenciones existentes en el backend, reutilizando componentes compatibles cuando corresponda.

No crear una arquitectura paralela si el proyecto existente ya proporciona una solución equivalente.

---

# 2. Arquitectura de carpetas

El módulo runtime de Client debe ubicarse dentro de:

```text
src/main/java/io/mindoracare/client/
```

La estructura esperada es:

```text
client/

├── application/

│   ├── in/
│   ├── port/
│   └── usecase/

├── domain/

│   ├── model/
│   └── service/

└── infrastructure/

    ├── controller/

    ├── repository/

    │   └── adapter/

    └── client/
```

La estructura exacta puede adaptarse a las convenciones ya existentes en el backend, siempre que se mantenga la separación de responsabilidades.

No crear una segunda arquitectura paralela si el proyecto existente ya proporciona una forma equivalente de resolver una necesidad.

---

# 3. Reglas generales

## 3.1 Independencia del módulo Client

El módulo `client` debe ser independiente de los módulos existentes que no correspondan funcionalmente.

No debe depender de:

```text
io.mindoracare.patient.*
```

ni utilizar clases específicas de Patient únicamente por conveniencia.

Tampoco debe depender funcionalmente de:

```text
io.mindoracare.chatbot.*
```

si la funcionalidad puede implementarse directamente dentro del módulo Client.

Si Client necesita modelos de entrada o salida, debe utilizar sus propios modelos cuando corresponda.

Ejemplos:

```text
ClientRequest
ClientResponse
```

Los nombres definitivos deben adaptarse a las convenciones del proyecto.

---

## 3.2 n8n deja de ser parte del runtime de Client

El comportamiento del workflow de Client debe implementarse directamente en el backend.

Client no debe realizar llamadas HTTP hacia n8n.

El webhook de n8n:

```text
POST http://localhost:5678/webhook-test/chat-web
```

representa el punto de entrada del workflow actual, pero no debe convertirse en una dependencia funcional del nuevo módulo.

Los componentes de n8n existentes pueden permanecer porque pueden ser utilizados por otros módulos.

No modificar ni eliminar otros flujos por esta migración.

---

## 3.3 Conservación del comportamiento

La migración debe reproducir el comportamiento funcional actual del workflow de Client.

No cambiar por iniciativa propia:

* reglas de identificación del cliente;
* `user_id`;
* `ip`;
* `name`;
* `action`;
* `message`;
* `state`;
* `blocked_until`;
* `last_interaction`;
* historial;
* reglas de actualización del cliente;
* reglas de creación del cliente;
* reglas de recuperación de clientes bloqueados;
* prompt;
* contrato de entrada;
* contrato de salida;
* orden funcional de las operaciones.

Si el workflow y este SPEC describen un comportamiento concreto, debe conservarse.

No realizar mejoras funcionales que no estén solicitadas.

---

## 3.4 Nodo 1 — Webhook

El workflow actual comienza con un Webhook de n8n.

Configuración observada:

```text
HTTP Method: POST

Path:
chat-web

URL de prueba:
http://localhost:5678/webhook-test/chat-web

Authentication:
sin autenticación configurada

Respond:
Respond to Webhook
```

Este Webhook representa actualmente el punto de entrada del flujo Client.

En la migración a Java + Quarkus, este Webhook de n8n debe ser reemplazado por un endpoint HTTP del backend.

El nuevo endpoint debe mantener el comportamiento funcional del flujo, pero no debe depender de:

```text
http://localhost:5678/webhook-test/chat-web
```

ni de la infraestructura de n8n.

La ruta HTTP definitiva debe definirse de acuerdo con las convenciones existentes del backend y documentarse posteriormente en este SPEC.

---

## 3.5 Nodo 2 — Normalización y preparación de entrada

El segundo nodo del workflow es un Code Node encargado de transformar la información recibida por el Webhook.

Código funcional actual:

```javascript
const raw = $json;

const headers = raw.headers || {};
const body = raw.body || {};

const message =
    String(body.message || '').trim();

const userId =
    String(body.userId || '').trim();

const action =
    String(body.action || '').trim();

const host =
    String(headers.host || '').trim();

const hostIp =
    host.split(':')[0].trim();

const ip =
    hostIp && userId
        ? `${hostIp}_USER_${userId}`
        : hostIp || userId || '';

const nameMatch =
    action.match(/\[NAME=([^\]]+)\]/);

const name =
    nameMatch
        ? nameMatch[1].trim()
        : '';

const cleanAction =
    nameMatch
        ? ''
        : action;

return [
    {
        json: {
            user_id: userId,
            message,
            action: cleanAction,
            name,
            ip
        }
    }
];
```

La migración debe reproducir este comportamiento.

### 3.5.1 Datos de entrada

El workflow obtiene:

```text
raw.headers
raw.body
```

Desde `body` obtiene:

```text
message
userId
action
```

Desde `headers` obtiene:

```text
host
```

---

### 3.5.2 Message

El valor:

```text
body.message
```

se convierte a string y se eliminan espacios al inicio y al final.

Si no existe, se utiliza:

```text
''
```

Resultado:

```text
message
```

---

### 3.5.3 User ID

El valor:

```text
body.userId
```

se convierte a string y se eliminan espacios al inicio y al final.

Resultado:

```text
user_id
```

El valor se utiliza posteriormente como identificador del cliente.

---

### 3.5.4 Action

El valor:

```text
body.action
```

se convierte a string y se eliminan espacios al inicio y al final.

Resultado inicial:

```text
action
```

Posteriormente puede modificarse si contiene:

```text
[NAME=...]
```

---

### 3.5.5 Host

El workflow obtiene:

```text
headers.host
```

y elimina espacios al inicio y al final.

Posteriormente separa el valor por `:` y utiliza la primera parte:

```text
host.split(':')[0]
```

El resultado es:

```text
hostIp
```

Esto permite eliminar el puerto cuando `host` contiene una estructura como:

```text
host:port
```

---

### 3.5.6 IP

La variable `ip` se construye mediante:

```text
hostIp + "_USER_" + userId
```

cuando existen ambos valores.

Si no existen ambos valores, se utiliza:

```text
hostIp
```

o:

```text
userId
```

o:

```text
''
```

La lógica funcional debe conservarse.

---

### 3.5.7 Name

El workflow busca dentro de `action` un patrón:

```text
[NAME=...]
```

mediante:

```text
/\[NAME=([^\]]+)\]/
```

Si encuentra coincidencia:

```text
name = contenido interno de [NAME=...]
```

eliminando espacios al inicio y al final.

Si no encuentra coincidencia:

```text
name = ''
```

---

### 3.5.8 Clean Action

Si `action` contiene:

```text
[NAME=...]
```

entonces:

```text
action = ''
```

En caso contrario conserva el valor original normalizado:

```text
action = body.action.trim()
```

---

### 3.5.9 Salida del Nodo 2

El nodo genera:

```json
{
  "user_id": "string",
  "message": "string",
  "action": "string",
  "name": "string",
  "ip": "string"
}
```

El backend debe producir internamente un modelo equivalente.

---

# 3.6 Nodo 3 — Consulta y actualización del cliente

El tercer nodo del workflow es un nodo PostgreSQL que realiza en una única operación lógica:

1. preparar los datos de entrada;
2. buscar el cliente existente;
3. desbloquear clientes cuyo bloqueo ya expiró;
4. actualizar el nombre cuando corresponde;
5. crear el cliente si no existe;
6. obtener el estado actual;
7. obtener `blocked_until`;
8. obtener el nombre final;
9. obtener el historial de conversación;
10. devolver los datos necesarios para continuar el flujo.

El SQL actual del workflow es:

```sql
WITH input_data AS (
    SELECT
        '{{ $json.user_id }}'::uuid AS user_id,
        '{{ ($json.ip || "").replace(/'/g, "''") }}' AS ip,
        '{{ ($json.name || "").replace(/'/g, "''") }}' AS input_name,
        '{{ ($json.message || "").replace(/'/g, "''") }}' AS message,
        '{{ ($json.action || "").replace(/'/g, "''") }}' AS action
),
existing_client AS (
    SELECT
        c.id,
        c.state,
        c.blocked_until,
        c.ip,
        c.name
    FROM client c
    JOIN input_data i
        ON c.id = i.user_id
),
updated_client AS (
    UPDATE client c
    SET
        name = CASE
            WHEN TRIM(i.input_name) <> ''
            THEN i.input_name
            ELSE c.name
        END,
        state = 'NORMAL',
        blocked_until = NULL,
        last_interaction = NOW()
    FROM input_data i
    JOIN existing_client ec
        ON ec.id = i.user_id
    WHERE c.id = ec.id
      AND ec.state = 'BLOCKED'
      AND ec.blocked_until IS NOT NULL
      AND ec.blocked_until <= NOW()
    RETURNING
        c.id,
        c.state,
        c.blocked_until,
        c.name
),
updated_name_client AS (
    UPDATE client c
    SET
        name = i.input_name
    FROM input_data i
    JOIN existing_client ec
        ON ec.id = i.user_id
    WHERE c.id = ec.id
      AND TRIM(i.input_name) <> ''
      AND c.name IS DISTINCT FROM i.input_name
    RETURNING
        c.id,
        c.name
),
new_client AS (
    INSERT INTO client (
        id,
        ip,
        name,
        state
    )
    SELECT
        i.user_id,
        i.ip,
        i.input_name,
        'NORMAL'
    FROM input_data i
    WHERE NOT EXISTS (
        SELECT 1
        FROM existing_client ec
        WHERE ec.id = i.user_id
    )
    RETURNING
        id,
        state,
        blocked_until,
        name
),
client_data AS (
    SELECT
        ec.id,
        COALESCE(
            uc.state,
            ec.state
        ) AS state,
        COALESCE(
            uc.blocked_until,
            ec.blocked_until
        ) AS blocked_until,
        COALESCE(
            unc.name,
            uc.name,
            ec.name
        ) AS name
    FROM existing_client ec
    LEFT JOIN updated_client uc
        ON uc.id = ec.id
    LEFT JOIN updated_name_client unc
        ON unc.id = ec.id

    UNION ALL

    SELECT
        nc.id,
        nc.state,
        nc.blocked_until,
        nc.name
    FROM new_client nc
),
chat_context AS (
    SELECT
        cd.id,
        COALESCE(
            (
                SELECT jsonb_agg(
                    jsonb_build_object(
                        'role', x.role,
                        'message', x.message,
                        'created_at', x.created_at
                    )
                    ORDER BY x.created_at ASC, x.id ASC
                )
                FROM (
                    SELECT
                        cct.id,
                        cct.role,
                        cct.message,
                        cct.created_at
                    FROM client_chat_context cct
                    WHERE cct.client_id = cd.id
                    ORDER BY cct.created_at DESC, cct.id DESC
                    LIMIT 10
                ) x
            ),
            '[]'::jsonb
        ) AS client_chat_context
    FROM client_data cd
)
SELECT
    i.message,
    i.action,
    i.user_id,
    cc.client_chat_context,
    cd.state,
    cd.blocked_until,
    cd.name
FROM input_data i
JOIN client_data cd
    ON cd.id = i.user_id
JOIN chat_context cc
    ON cc.id = cd.id;
```

### 3.6.1 Fuente de verdad de la base de datos

El schema SQL proporcionado para el proyecto Client es la fuente de verdad.

No modificar la base de datos para adaptar el código Java.

No crear tablas nuevas ni modificar:

* columnas;
* tipos;
* relaciones;
* constraints;
* índices;

salvo que sea solicitado explícitamente.

El código Java debe adaptarse al schema existente.

---

### 3.6.2 Cliente existente

El workflow busca el cliente mediante:

```text
client.id = user_id
```

El identificador recibido como `user_id` corresponde al `client.id`.

---

### 3.6.3 Cliente bloqueado con bloqueo expirado

Si el cliente existe y cumple:

```text
state = 'BLOCKED'
```

y:

```text
blocked_until IS NOT NULL
```

y:

```text
blocked_until <= NOW()
```

el workflow lo actualiza a:

```text
state = 'NORMAL'
blocked_until = NULL
last_interaction = NOW()
```

Además, si se recibió un nombre no vacío, actualiza el nombre.

Esta regla debe conservarse.

---

### 3.6.4 Actualización del nombre

Si el cliente existe y:

```text
input_name <> ''
```

y el nombre actual es diferente:

```text
c.name IS DISTINCT FROM i.input_name
```

se actualiza:

```text
client.name = input_name
```

La lógica debe conservarse.

---

### 3.6.5 Creación de un nuevo cliente

Si no existe un cliente con:

```text
id = user_id
```

se crea:

```text
client.id = user_id
client.ip = ip
client.name = input_name
client.state = 'NORMAL'
```

Los demás campos deben conservar sus valores/defaults definidos por el schema de la base de datos.

No inventar valores adicionales.

---

### 3.6.6 Historial de Client

El workflow obtiene el historial desde:

```text
client_chat_context
```

relacionado mediante:

```text
client_chat_context.client_id = client.id
```

Se recuperan como máximo:

```text
10
```

registros.

La selección se realiza inicialmente desde los registros más recientes:

```text
ORDER BY created_at DESC, id DESC
LIMIT 10
```

Posteriormente esos registros se devuelven ordenados cronológicamente:

```text
ORDER BY created_at ASC, id ASC
```

El resultado se transforma a objetos con:

```json
{
  "role": "string",
  "message": "string",
  "created_at": "timestamp"
}
```

Si no existen registros, el resultado es:

```json
[]
```

---

### 3.6.7 Salida del Nodo 3

El nodo PostgreSQL devuelve:

```text
message
action
user_id
client_chat_context
state
blocked_until
name
```

Conceptualmente:

```json
{
  "message": "string",
  "action": "string",
  "user_id": "uuid",
  "client_chat_context": [],
  "state": "string",
  "blocked_until": null,
  "name": "string"
}
```

Los valores concretos dependen del cliente y de su historial.

---

# 3.7 Persistencia

La implementación Java debe utilizar puertos y adaptadores.

Conceptualmente:

```text
UseCase
   ↓
RepositoryPort
   ↓
RepositoryAdapter
   ↓
PostgreSQL / Supabase
```

La consulta del Nodo 3 no debe copiarse necesariamente de forma literal a Java si una implementación equivalente permite reproducir exactamente el mismo comportamiento.

Sin embargo, cualquier reimplementación debe conservar las reglas funcionales descritas en este SPEC.

---

# 3.8 Historial de Client

El historial utilizado por el flujo corresponde a:

```text
client_chat_context
```

Debe conservarse:

* la relación mediante `client_id`;
* el límite de 10 registros utilizado por el Nodo 3;
* el orden cronológico final;
* `role`;
* `message`;
* `created_at`.

Los nodos posteriores definirán cómo se utiliza este historial y cómo se modifica durante el procesamiento del flujo.

---

# 3.9 Configuración y credenciales

No colocar credenciales directamente en:

* Java;
* código fuente;
* archivos versionados;
* JSON;
* configuración con secretos reales.

Utilizar las variables de entorno existentes del proyecto.

El usuario proporcionará los valores reales mediante `.env`.

Claude debe generar únicamente las referencias de configuración necesarias.

---

# 3.10 Reutilización

Antes de crear una clase nueva, revisar el código existente del backend.

Reutilizar abstracciones existentes cuando sean compatibles con la arquitectura.

No reutilizar clases de otros módulos únicamente para evitar crear un modelo propio.

La independencia del módulo Client tiene prioridad sobre una reutilización artificial.

---

# 3.11 Compilación y verificación

Después de implementar la migración:

1. compilar el proyecto;
2. corregir errores introducidos;
3. verificar que el endpoint exista;
4. verificar que Client no dependa funcionalmente de n8n;
5. verificar que Client no dependa innecesariamente de Patient;
6. verificar el contrato de entrada;
7. verificar el contrato de salida;
8. verificar la interacción con PostgreSQL;
9. verificar las reglas de creación y actualización de `client`;
10. verificar la recuperación de `client_chat_context`.

La migración no se considera terminada únicamente porque el código haya sido generado.

---

# 4. Nodo 1

El Nodo 1 es el Webhook descrito en la sección 3.4.

La responsabilidad funcional del Nodo 1 es recibir la petición HTTP inicial del flujo Client.

No contiene lógica de negocio adicional.

En la migración, esta responsabilidad será asumida por el Resource HTTP del módulo Client.

---

# 5. Nodo 2

El Nodo 2 es el Code Node descrito en la sección 3.5.

Su responsabilidad funcional es:

```text
Webhook input
    ↓
normalización
    ↓
user_id
message
action
name
ip
```

La implementación Java debe reproducir exactamente las reglas de normalización descritas.

---

# 6. Nodo 3

El Nodo 3 es el nodo PostgreSQL descrito en la sección 3.6.

Su responsabilidad funcional es:

```text
input
  ↓
buscar client
  ↓
crear o actualizar client
  ↓
resolver state
  ↓
resolver blocked_until
  ↓
resolver name
  ↓
obtener client_chat_context
  ↓
entregar datos al siguiente nodo
```

Los siguientes nodos del workflow definirán el procesamiento posterior.

---

# 7. Nodo 4 — Switch

Debe documentarse:

Tipo: Switch
Entrada: $json.state

Primera condición:

{{$json.state}}
is equal to
NORMAL

Segunda condición:

{{$json.state}}
is equal to
BLOCKED

Responsabilidad funcional:

state
  │
  ├── NORMAL  → continuar con el cuestionario
  │
  └── BLOCKED → responder acceso bloqueado

En backend será una decisión simple sobre state, pero debe quedar documentada como nodo independiente porque durante la migración podremos identificar claramente que este comportamiento del Switch quedó absorbido en el caso de uso/servicio correspondiente.

# 8.1. Nodo 5 — Code — Estado NORMAL

Este es el Code que acabas de proporcionar.

Su responsabilidad es preparar toda la información necesaria para el AI Agent:

input del Nodo 3
      ↓
leer message/action/user_id
      ↓
obtener client_chat_context
      ↓
definir especialidades
      ↓
definir preguntas P1-P4
      ↓
reconstruir historial
      ↓
detectar última pregunta
      ↓
determinar etapa actual
      ↓
obtener respuestas anteriores
      ↓
construir historyText
      ↓
construir prompt
      ↓
entregar prompt al AI Agent

Su salida principal contiene:

{
  "message": "...",
  "action": "...",
  "user_id": "...",
  "client_chat_context": [],
  "questions_answered": 0,
  "questionnaire_status": "START",
  "next_question": "P1",
  "questionnaire_answers": [],
  "prompt": "..."
}

La lógica completa del Code debe conservarse en la migración, especialmente la determinación de:

next_question
questions_answered
questionnaire_status
questionnaire_answers
historyText
prompt
# 8.2. Nodo 6 — Respond to Webhook — BLOCKED

Este camino solamente se ejecuta cuando:

state == BLOCKED

Response Body:

{
  "answer": "Tu acceso se encuentra temporalmente bloqueado. Podrás volver a utilizar el servicio cuando finalice el periodo de bloqueo."
}

En backend, este comportamiento no requiere mantener un nodo equivalente; será una respuesta directa del endpoint/caso de uso cuando el cliente se encuentre bloqueado.

# 9. Nodo 7 — AI Agent

Este nodo solamente recibe los datos provenientes del camino NORMAL, específicamente el prompt generado por el Nodo 8.1.

Input:

{{ $json.prompt }}

Configuración:

mismo modelo utilizado actualmente
misma baseUrl
misma API key
misma configuración del proveedor LLM
mismo prompt generado por el nodo Code

Su responsabilidad funcional es:

prompt
  ↓
AI Agent
  ↓
respuesta generada por el modelo

---

# 10. Nodo 8 — Code — Procesamiento de respuesta del AI Agent

El Nodo 8 es un nodo Code que procesa la respuesta generada por el AI Agent y extrae las etiquetas internas utilizadas por el workflow.

## 10.1. Entradas

El nodo obtiene información de dos nodos anteriores:

* `Code in JavaScript6`
* `AI Agent`

Del nodo `Code in JavaScript6` obtiene:

* `message`
* `user_id`

Del nodo `AI Agent` obtiene:

* `output`

## 10.2. Procesamiento

Primero obtiene la respuesta completa del AI Agent:

```javascript
const aiOutput = String(aiData.output ?? "").trim();
```

Luego extrae:

### ACTION

Busca una etiqueta con el formato:

```text
[ACTION=...]
```

Por ejemplo:

```text
[ACTION=QUESTIONNAIRE]
```

El valor extraído se almacena en:

```text
action
```

### CATEGORY

Busca una etiqueta con el formato:

```text
[CATEGORY=...]
```

Por ejemplo:

```text
[CATEGORY=Ansiedad]
```

El valor extraído se almacena en:

```text
category
```

### ai_message

Después elimina del texto generado por el AI Agent:

* `[ACTION=...]`
* `[CATEGORY=...]`
* `[P1]`
* `[P2]`
* `[P3]`
* `[P4]`

El resultado limpio se almacena como:

```text
ai_message
```

## 10.3. Salida

El nodo entrega:

```json
{
  "message": "...",
  "user_id": "...",
  "ai_output": "...",
  "ai_message": "...",
  "action": "...",
  "category": "..."
}
```

## 10.4. Responsabilidad funcional

Este nodo separa la respuesta conversacional dirigida al usuario de las etiquetas internas utilizadas para controlar el flujo.

En la migración a backend, esta responsabilidad debe mantenerse, aunque no necesariamente como un nodo independiente. Puede implementarse como una operación del dominio o servicio que procese la respuesta del LLM.

---

# 11. Nodo 9 — PostgreSQL — Persistencia de conversación e inquiry

El Nodo 9 es un nodo PostgreSQL que procesa la respuesta del AI Agent y persiste la interacción cuando corresponde.

Su responsabilidad funcional es:

```text
respuesta del AI Agent
        ↓
obtener estado y nombre del client
        ↓
reemplazar [NAME]
        ↓
resolver specialty_id
        ↓
validar estado y action
        ↓
mantener máximo 10 registros de contexto
        ↓
guardar mensaje del usuario
        ↓
guardar respuesta del assistant
        ↓
crear inquiry
        ↓
entregar datos al siguiente nodo
```

## 11.1. Datos de entrada

El nodo recibe:

* `message`
* `user_id`
* `ai_message`
* `action`
* `category`

## 11.2. Obtención del client

Busca el registro correspondiente en `client` mediante:

```text
client.id = user_id
```

Obtiene:

* `state`
* `name`

## 11.3. Reemplazo de [NAME]

Si `ai_message` contiene:

```text
[NAME]
```

se reemplaza por el nombre del cliente.

Si el nombre está vacío o solamente contiene espacios, utiliza:

```text
Usuario
```

Si `[NAME]` no existe, el mensaje permanece sin cambios.

## 11.4. Resolución de specialty_id

La especialidad solamente se busca cuando:

```text
action = MATCH_SUCCESS
```

La búsqueda se realiza en `specialty` comparando:

```text
LOWER(TRIM(specialty))
=
LOWER(TRIM(category))
```

y solamente se consideran registros donde:

```text
deleted = FALSE
```

Si `action` no es `MATCH_SUCCESS`, entonces:

```text
specialty_id = NULL
```

## 11.5. Persistencia del contexto

La persistencia solamente ocurre cuando:

```text
state = NORMAL
```

y:

```text
action = QUESTIONNAIRE
```

o:

```text
action = MATCH_SUCCESS
```

Es decir:

```text
NORMAL + QUESTIONNAIRE
NORMAL + MATCH_SUCCESS
```

son los casos que generan persistencia.

## 11.6. Límite del historial

Antes de insertar los nuevos mensajes, el nodo cuenta los registros existentes en:

```text
client_chat_context
```

para el cliente actual.

El cálculo considera que se agregarán dos nuevos registros:

```text
user
assistant
```

y elimina los registros más antiguos cuando es necesario para mantener un máximo de 10 registros.

El orden utilizado para eliminar registros es:

```text
created_at ASC
id ASC
```

Por tanto, se eliminan primero los registros más antiguos.

## 11.7. Inserción del mensaje del usuario

Se inserta en `client_chat_context`:

```text
client_id = user_id
role = user
message = message
```

## 11.8. Inserción de la respuesta del assistant

Se inserta en `client_chat_context`:

```text
client_id = user_id
role = assistant
message = ai_message
```

## 11.9. Inserción de inquiry

Se crea un registro en `inquiry`:

```text
client_id = user_id
question = message
answer = ai_message_replaced
specialty_id = specialty_id
```

El `answer` utiliza el mensaje con `[NAME]` ya reemplazado.

Para `QUESTIONNAIRE`:

```text
specialty_id = NULL
```

Para `MATCH_SUCCESS`:

```text
specialty_id = ID de specialty correspondiente a category
```

## 11.10. Datos entregados al siguiente nodo

Después de ejecutar la lógica de persistencia, el nodo devuelve información utilizada por los siguientes nodos:

```json
{
  "message": "...",
  "action": "...",
  "user_id": "...",
  "ai_message": "...",
  "answer": "...",
  "category": "...",
  "specialty_id": null,
  "client_name": "..."
}
```

`answer` contiene el mensaje final dirigido al usuario, incluyendo el reemplazo de `[NAME]` cuando corresponde.

## 11.11. Migración a backend

La lógica de este nodo debe trasladarse al backend respetando el comportamiento actual.

No es necesario reproducir literalmente el bloque `DO $$` de PostgreSQL.

En Java, la lógica debe distribuirse según las responsabilidades de la arquitectura existente, utilizando repositorios/puertos para:

* obtener el client;
* obtener o resolver la especialidad;
* administrar `client_chat_context`;
* crear `inquiry`.

Las operaciones de persistencia que forman una misma interacción deben mantener la consistencia transaccional correspondiente.

No se debe modificar el esquema existente de la base de datos para adaptar la implementación Java.

---

# 12. Nodo 10 — Switch — Clasificación de ACTION

El Nodo 10 es un `Switch` que determina la siguiente rama del workflow utilizando el valor de:

```text
$json.action
```

El nodo tiene cinco condiciones.

## 12.1. Condición CRISIS

Expresión:

```javascript
{{ ($json.action || "").includes("CRISIS") }}
```

Condición:

```text
is true
```

Si se cumple, la interacción continúa por la rama correspondiente a `CRISIS`.

## 12.2. Condición QUESTIONNAIRE

Expresión:

```javascript
{{ ($json.action || "").includes("QUESTIONNAIRE") }}
```

Condición:

```text
is true
```

Si se cumple, la interacción continúa por la rama correspondiente a `QUESTIONNAIRE`.

## 12.3. Condición SECURITY

Expresión:

```javascript
{{ ($json.action || "").includes("SECURITY") }}
```

Condición:

```text
is true
```

Si se cumple, la interacción continúa por la rama correspondiente a `SECURITY`.

## 12.4. Condición NOT_UNDERSTOOD

Expresión:

```javascript
{{ ($json.action || "").includes("NOT_UNDERSTOOD") }}
```

Condición:

```text
is true
```

Si se cumple, la interacción continúa por la rama correspondiente a `NOT_UNDERSTOOD`.

## 12.5. Condición MATCH_SUCCESS

Expresión:

```javascript
{{ ($json.action || "").includes("MATCH_SUCCESS") }}
```

Condición:

```text
is true
```

Si se cumple, la interacción continúa por la rama correspondiente a `MATCH_SUCCESS`.

## 12.6. Responsabilidad funcional

El Switch clasifica el resultado generado por el AI Agent y dirige la ejecución hacia la respuesta correspondiente.

El backend deberá conservar esta decisión funcional:

```text
action
  │
  ├── CRISIS
  ├── QUESTIONNAIRE
  ├── SECURITY
  ├── NOT_UNDERSTOOD
  └── MATCH_SUCCESS
```

No se debe asumir todavía qué hace cada rama posterior. Cada una será documentada en el SPEC cuando se proporcionen los nodos correspondientes.

---

# 13. Rama CRISIS

Cuando el Nodo 10 — Switch detecta que:

```text
{{ ($json.action || "").includes("CRISIS") }}
```

es `true`, la ejecución continúa por el nodo PostgreSQL correspondiente a la gestión de crisis.

## 13.1. Nodo PostgreSQL — Gestión de crisis

La responsabilidad de este nodo es procesar una situación de crisis, registrar la interacción, bloquear temporalmente al cliente y limpiar su contexto conversacional.

El flujo funcional es:

```text
CRISIS
  ↓
obtener cliente
  ↓
reemplazar [NAME]
  ↓
obtener first_name y last_name
  ↓
bloquear cliente durante 1 día
  ↓
eliminar client_chat_context
  ↓
registrar inquiry
  ↓
entregar datos para los siguientes nodos
```

### 13.1.1. Obtener cliente

Busca el cliente utilizando:

```text
client.id = user_id
```

y obtiene su nombre.

### 13.1.2. Reemplazar [NAME]

Si la respuesta del AI Agent contiene:

```text
[NAME]
```

y el cliente tiene un nombre válido, se reemplaza por `client.name`.

Si el cliente no tiene nombre válido, la respuesta permanece sin reemplazo.

El resultado se utiliza como `answer`.

### 13.1.3. Obtener nombre y apellido

Si el cliente no tiene nombre:

```text
first_name = Usuario
last_name = ""
```

Si tiene nombre, se divide utilizando el primer espacio:

```text
first_name = primera palabra
last_name = resto del nombre
```

Estos datos serán utilizados posteriormente por el nodo HTTP Request.

### 13.1.4. Bloquear cliente

El cliente se actualiza:

```text
state = BLOCKED
blocked_until = NOW() + INTERVAL '1 day'
```

El bloqueo tiene una duración de un día.

### 13.1.5. Limpiar contexto

Se eliminan todos los registros asociados al cliente en:

```text
client_chat_context
```

### 13.1.6. Registrar inquiry

Se registra la interacción en `inquiry`:

```text
client_id = user_id
question = message
answer = answer
specialty_id = NULL
```

No se asigna una especialidad durante este flujo.

### 13.1.7. Salida

El nodo devuelve:

```text
user_id
action
message
answer
name
first_name
last_name
specialty_id
```

---

# 14. Nodo HTTP Request — Crear solicitud de crisis

Después del PostgreSQL de crisis se ejecuta un nodo HTTP Request.

## 14.1. Endpoint

La solicitud se realiza contra:

```text
https://api-stglcl.mindoracare.io/gql/pu/p/v1
```

## 14.2. Operación

Utiliza la operación GraphQL:

```text
CreateRequest
```

con:

```text
typeId = COMPANY_HIRING
subtypeId = COMPANY_HIRING_SERVICE
employeeRangeId = 3
countryId = PE
countryCallingCode = +51
```

Los datos del cliente se obtienen del nodo PostgreSQL:

```text
firstName = $json.first_name || 'Usuario'
lastName = $json.last_name || ''
email = ''
phone = ''
description = $json.message
```

## 14.3. Responsabilidad funcional

Este nodo registra externamente la solicitud generada a partir de la situación de crisis.

En la migración a backend deberá conservarse la integración con el servicio GraphQL correspondiente.

La URL, credenciales y demás parámetros de infraestructura no deben quedar hardcodeados como lógica de negocio.

---

# 15. Nodo Respond to Webhook — Respuesta final

Este nodo devuelve la respuesta al usuario.

Su Response Body es:

```javascript
{{ {
    answer: $json.answer
} }}
```

La respuesta final tiene la siguiente estructura:

```json
{
  "answer": "..."
}
```

Este nodo es compartido por diferentes ramas del Switch.

En el backend no será necesario implementar un nodo equivalente. La respuesta será retornada directamente por el endpoint correspondiente.

---

# 16. Rama QUESTIONNAIRE

Cuando el Nodo 10 — Switch detecta:

```text
{{ ($json.action || "").includes("QUESTIONNAIRE") }}
```

la ejecución pasa directamente al nodo de respuesta final.

No se ejecuta ningún procesamiento adicional antes de responder.

La rama es:

```text
QUESTIONNAIRE
    ↓
Respond to Webhook
```

Utiliza el mismo nodo descrito en:

```text
# 15. Nodo Respond to Webhook — Respuesta final
```

La respuesta enviada al usuario es:

```json
{
  "answer": "..."
}
```

El valor de `answer` proviene del nodo PostgreSQL anterior.

---

# 17. Rama SECURITY

Cuando el Nodo 10 — Switch detecta:

```text
{{ ($json.action || "").includes("SECURITY") }}
```

la ejecución continúa con el nodo PostgreSQL de gestión de seguridad.

## 17.1. Nodo PostgreSQL — Gestión de seguridad

La responsabilidad de este nodo es registrar la solicitud de seguridad, asignar un asesor, crear un ticket, bloquear temporalmente al cliente y limpiar su contexto.

El flujo funcional es:

```text
SECURITY
    ↓
obtener cliente
    ↓
reemplazar [NAME]
    ↓
crear inquiry
    ↓
buscar asesor con menor carga
    ↓
crear ticket
    ↓
bloquear cliente
    ↓
limpiar client_chat_context
    ↓
entregar datos
```

### 17.1.1. Obtener cliente

Busca el cliente mediante:

```text
client.id = user_id
```

y obtiene:

```text
name
```

### 17.1.2. Reemplazar [NAME]

Si `ai_message` contiene `[NAME]` y el cliente tiene un nombre válido, se reemplaza por `client.name`.

El resultado se utiliza como `answer`.

### 17.1.3. Registrar inquiry

Se crea un registro en `inquiry`:

```text
client_id = user_id
question = message
answer = answer
specialty_id = NULL
```

El identificador generado del `inquiry` se utiliza posteriormente para crear el ticket.

### 17.1.4. Buscar asesor

Se buscan asesores que cumplan:

```text
is_active = TRUE
```

Para calcular la carga de cada asesor se consideran únicamente sus tickets con estado:

```text
OPEN
IN_PROGRESS
```

Se selecciona el asesor con menor cantidad de tickets activos.

En caso de empate, se utiliza el nombre del asesor en orden ascendente.

Se obtienen:

```text
advisor_id
advisor_name
advisor_phone
```

### 17.1.5. Crear ticket

Se crea un ticket con:

```text
client_id = user_id
inquiry_id = inquiry generado
advisor_id = asesor seleccionado
description = message
status = OPEN
```

### 17.1.6. Bloquear cliente

El cliente se actualiza:

```text
state = BLOCKED
blocked_until = NOW() + INTERVAL '1 day'
last_interaction = NOW()
```

### 17.1.7. Limpiar contexto

Se eliminan todos los registros de:

```text
client_chat_context
```

correspondientes al cliente.

### 17.1.8. Salida

El nodo entrega información como:

```text
user_id
action
message
answer
ticket_id
advisor_id
advisor_name
advisor_phone
```

---

# 18. Nodo Respond to Webhook — Respuesta de SECURITY

La rama `SECURITY` utiliza el mismo mecanismo de respuesta final descrito en el Nodo 15.

La respuesta enviada al usuario es:

```json
{
  "answer": "..."
}
```

---

# 19. Nodo WAHA — Notificación al asesor

Además de responder al usuario, la rama `SECURITY` envía una notificación al asesor asignado mediante WAHA.

## 19.1. Session

La sesión utilizada es:

```text
{{ $json.session || 'default' }}
```

## 19.2. Chat ID

El identificador de WhatsApp se construye utilizando el teléfono del asesor:

```text
{{ '51' + ($json.advisor_phone || '').replace(/\D/g, '') + '@c.us' }}
```

## 19.3. Mensaje

El mensaje enviado al asesor contiene:

```text
🚨 ALERTA DE SEGURIDAD

Cliente: {{ $json.client_name || 'No registrado' }}

Ticket: {{ $json.ticket_id }}

Solicitud del usuario:
"{{ $json.message }}"

Acción: Se ha detectado una solicitud relacionada con información interna o seguridad del sistema.

Asesor asignado: {{ $json.advisor_name }}

Por favor, revisar el ticket asignado.
```

## 19.4. Configuración externa

El nodo utiliza una credencial de WAHA que actualmente contiene:

```text
Host URL:
http://host.docker.internal:3000
```

y una API Key.

Estos valores corresponden a configuración de infraestructura y no forman parte de la lógica funcional del flujo Client.

Durante la migración al backend deberán manejarse mediante configuración externa, por ejemplo mediante variables de entorno y `application.properties`.

El SPEC no define ni almacena la API Key.

---

# 20. Rama NOT_UNDERSTOOD

Cuando el Nodo 10 — Switch detecta:

```text
{{ ($json.action || "").includes("NOT_UNDERSTOOD") }}
```

la ejecución continúa directamente hacia el nodo `Respond to Webhook` utilizado para entregar la respuesta final.

La rama `NOT_UNDERSTOOD` no ejecuta ningún procesamiento adicional.

No se realiza:

* consulta de psicólogos;
* llamada HTTP al servicio de afinidad;
* ejecución de un nodo Code adicional;
* eliminación de registros de `client_chat_context`;
* modificación del estado del cliente;
* actualización de `last_interaction`.

La respuesta corresponde directamente al contenido generado previamente por el AI Agent y procesado por el nodo posterior al AI Agent.

La rama queda:

```text
NOT_UNDERSTOOD
      ↓
Respond to Webhook
```

El `Respond to Webhook` utilizado es el mismo nodo de respuesta final documentado anteriormente en el **#15**.

El Response Body es:

```javascript
{{ {
    answer: $json.answer
} }}
```

Por lo tanto, cuando el AI Agent determina que la respuesta del usuario no permite avanzar en el cuestionario, el sistema devuelve nuevamente la respuesta generada sin avanzar de etapa ni ejecutar acciones adicionales.

---

# 21. Rama MATCH_SUCCESS

Cuando el Nodo 10 — Switch detecta:

```text
{{ ($json.action || "").includes("MATCH_SUCCESS") }}
```

es `true`, la ejecución continúa por el nodo HTTP Request encargado de obtener los psicólogos relacionados con la especialidad identificada.

La rama completa queda:

```text
MATCH_SUCCESS
      ↓
HTTP Request
      ↓
Code
      ↓
PostgreSQL
      ↓
Respond to Webhook
```

---

## 21.1. Nodo HTTP Request — Obtener psicólogos

Este nodo consulta el servicio externo de psicólogos mediante una operación GraphQL.

### 21.1.1. Endpoint

```text
https://api-stglcl.mindoracare.io/gql/pt/p/v1
```

### 21.1.2. Headers

El nodo tiene habilitado el envío de headers.

Se envía:

```text
Content-Type: application/json
```

### 21.1.3. Body

El nodo tiene habilitado el envío del body en formato JSON.

La operación utilizada es:

```text
GetPsychologistByAffinityTest
```

La consulta recibe los siguientes parámetros:

```text
patientAge
specialityId
typeOfTherapyId
customFilterId
psychologistGender
```

Los valores utilizados actualmente son:

```text
patientAge = 28
specialityId = specialty_id obtenido del nodo PostgreSQL anterior
typeOfTherapyId = 1
customFilterId = 93412cac-88dd-4c8c-941d-bd919ad91b1f
psychologistGender = 1
```

El `specialityId` se obtiene del resultado del nodo:

```text
Execute a SQL query5
```

específicamente:

```text
specialty_id
```

### 21.1.4. Información solicitada

La consulta solicita información de los psicólogos, incluyendo:

```text
id
userName
professionalSummary
photoImageUrl
englishLanguage
averageRating
yearsExperience
licenseNumber
totalAppointment
videoUrl
coverUrl
approach
language
specialities
```

El resultado será utilizado por el siguiente nodo Code para construir la respuesta que se mostrará al cliente.

### 21.1.5. Responsabilidad funcional

La responsabilidad de este nodo es consultar el servicio externo para obtener los psicólogos relacionados con la especialidad identificada durante el cuestionario.

En la migración al backend, esta integración deberá mantenerse mediante la capa correspondiente de infraestructura, manteniendo la configuración externa separada de la lógica de negocio.

---

# 21.2. Nodo Code — Construcción de respuesta de MATCH_SUCCESS

Este nodo recibe la información del cliente desde el nodo PostgreSQL anterior y la información de los psicólogos desde el HTTP Request.

## 21.2.1. Obtener datos de PostgreSQL

Obtiene:

```text
client_name
user_id
category
```

Si `client_name` no está disponible, utiliza:

```text
Usuario
```

Si `category` no está disponible, utiliza:

```text
bienestar emocional
```

## 21.2.2. Obtener psicólogos

La respuesta del HTTP Request se obtiene desde:

```text
data.GetPsychologistByAffinityTest
```

Si el resultado no es un arreglo, se considera que no existen psicólogos disponibles.

## 21.2.3. Seleccionar psicólogos

Se seleccionan como máximo los primeros dos psicólogos obtenidos:

```text
psychologists.slice(0, 2)
```

Por lo tanto, el flujo presenta un máximo de:

```text
2 psicólogos
```

## 21.2.4. Información mostrada de cada psicólogo

Para cada psicólogo seleccionado se procesa:

```text
userName
yearsExperience
averageRating
photoImageUrl
residenceCountry.code
approach.approach
specialities
```

Las especialidades se obtienen desde:

```text
specialities
```

y se extrae:

```text
specialty
```

Si existen varias especialidades, se concatenan utilizando:

```text
,
```

Si no existen especialidades, se utiliza:

```text
No especificadas
```

## 21.2.5. Razón de la recomendación

Para cada psicólogo se genera una razón basada en la especialidad identificada:

```text
Su experiencia y especialidades están relacionadas con [specialty].
```

## 21.2.6. Cuando existen psicólogos

Si existe al menos un psicólogo disponible, se construye una respuesta que contiene:

```text
nombre del cliente
especialidad identificada
información de los psicólogos
```

También se agregan las marcas:

```text
init_action: view_psychologists
```

y:

```text
end_action
```

Estas marcas forman parte de la respuesta generada por este nodo.

## 21.2.7. Cuando no existen psicólogos

Si no se obtiene ningún psicólogo, se construye una respuesta indicando:

```text
Actualmente no hay psicólogos disponibles para asignación en este momento.
```

También se informa que el equipo de Mindora Care se pondrá en contacto posteriormente.

## 21.2.8. Salida

El nodo devuelve:

```json
{
  "answer": "...",
  "user_id": "..."
}
```

El campo `answer` contiene la respuesta final construida para el cliente.

---

# 21.3. Nodo PostgreSQL — Finalización de MATCH_SUCCESS

Después del nodo Code se ejecuta un nodo PostgreSQL encargado de finalizar el procesamiento del cliente.

## 21.3.1. Limpiar contexto del cuestionario

Se eliminan todos los registros de:

```text
client_chat_context
```

correspondientes al cliente:

```text
client_id = user_id
```

Esto elimina el contexto utilizado durante el cuestionario.

## 21.3.2. Restablecer estado del cliente

El cliente se actualiza con:

```text
state = NORMAL
last_interaction = NOW()
```

De esta manera, el cliente queda nuevamente en estado normal después de completar el proceso.

## 21.3.3. Obtener respuesta final

Finalmente, el nodo devuelve:

```json
{
  "answer": "..."
}
```

El valor corresponde al `answer` generado por el nodo Code anterior.

---

# 21.4. Nodo Respond to Webhook — Respuesta final de MATCH_SUCCESS

Después del PostgreSQL se utiliza el mismo nodo `Respond to Webhook` compartido por las diferentes ramas del Switch.

El Response Body es:

```javascript
{{ {
    answer: $json.answer
} }}
```

La respuesta final enviada al cliente tiene la estructura:

```json
{
  "answer": "..."
}
```

La rama `MATCH_SUCCESS` queda finalmente:

```text
MATCH_SUCCESS
      ↓
HTTP Request
      ↓
Code
      ↓
PostgreSQL
      ↓
Respond to Webhook
```

El `Respond to

---

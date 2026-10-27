# 1. Objetivo

Migrar completamente el flujo **Patient Follow-Up** actualmente implementado en n8n hacia el backend Java + Quarkus existente, manteniendo su comportamiento funcional y eliminando la dependencia funcional de n8n.

El resultado debe ser un módulo independiente dentro del backend:

```text
io.mindoracare.patient
```

El flujo migrado debe exponerse directamente mediante:

```http
POST /public/patient/followup
```

El cliente externo debe comunicarse directamente con este endpoint.

El flujo final debe ser:

```text
Cliente externo
    ↓ HTTP
PatientFollowUpResource
    ↓
PatientFollowUpUseCase
    ↓
PatientFollowUpUseCaseImpl
    ├── PatientRepositoryPort
    │       ↓
    │   PostgreSQL
    │
    ├── ChatContextRepositoryPort
    │       ↓
    │   PostgreSQL
    │
    └── LlmPort
            ↓
        DeepInfra
```

No debe existir:

```text
Cliente → chatbot → patient
```

ni:

```text
Patient → HTTP → n8n
```

ni:

```text
Patient → HTTP → chatbot
```

La funcionalidad debe ejecutarse directamente dentro del backend.

---

# 2. Arquitectura de carpetas

El módulo runtime de Patient debe ubicarse dentro de:

```text
src/main/java/io/mindoracare/patient/
```

La estructura esperada es:

```text
patient/
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

Los tests específicos de Patient deben ubicarse en:

```text
src/test/java/io/mindoracare/patient/
```

Por ejemplo:

```text
src/test/java/io/mindoracare/patient/
├── application/
│   └── usecase/
├── domain/
│   ├── model/
│   └── service/
└── infrastructure/
```

La estructura exacta puede adaptarse a las convenciones ya existentes en el backend, siempre que se respete la separación de responsabilidades.

No crear una segunda arquitectura paralela si el proyecto existente ya proporciona una forma equivalente de resolver una necesidad.

---

# 3. Reglas generales

## 3.1 Independencia del módulo Patient

El módulo `patient` debe ser independiente del módulo `chatbot`.

No debe depender de:

```text
io.mindoracare.chatbot.*
```

Ni utilizar clases específicas de `chatbot` como:

```text
ChatResponse
ChatGateway
ChatbotService
FollowUpChatbotService
N8nGateway
N8nRestClient
```

Si Patient necesita modelos de entrada o salida, debe utilizar sus propios modelos.

Ejemplo:

```text
PatientFollowUpRequest
PatientFollowUpResponse
```

No reutilizar clases de `chatbot` únicamente por conveniencia.

---

## 3.2 n8n deja de ser parte del runtime de Patient

El comportamiento del workflow de Patient debe implementarse directamente en el backend.

Patient no debe realizar llamadas HTTP hacia n8n.

No utilizar:

```text
n8n.followup.url
```

como dependencia funcional del nuevo flujo.

Los componentes de n8n existentes pueden permanecer porque pueden ser utilizados por otros módulos.

No modificar ni eliminar otros flujos por esta migración.

---

## 3.3 Conservación del comportamiento

La migración debe reproducir el comportamiento funcional actual del workflow.

No cambiar por iniciativa propia:

* acciones;
* opciones;
* reglas de conversación;
* `follow_up_case`;
* historial;
* prompt;
* reglas de cierre;
* reglas de generación de opciones;
* contrato de entrada;
* contrato de salida;
* orden funcional de las operaciones.

Si el workflow y este SPEC describen un comportamiento concreto, debe conservarse.

No realizar mejoras funcionales que no estén solicitadas.

---

## 3.4 Contrato de entrada

El endpoint recibe:

```json
{
  "message": "string",
  "userId": "string",
  "action": "string",
  "options": ["string"]
}
```

Debe conservarse la compatibilidad observada en el workflow para `userId` y `user_id` cuando corresponda.

`message` representa el mensaje del paciente.

`action` representa la acción recibida.

`options` representa las opciones seleccionadas.

La normalización de estos valores debe reproducir el comportamiento del workflow original.

---

## 3.5 Contrato de salida

La respuesta debe ser:

```json
{
  "answer": "string",
  "options": []
}
```

No agregar campos adicionales sin necesidad funcional.

---

## 3.6 Persistencia

La persistencia debe utilizar puertos y adaptadores.

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

El backend debe utilizar las tablas existentes.

**El schema SQL proporcionado junto con este SPEC es la fuente de verdad de la base de datos.**

No crear tablas nuevas ni modificar columnas, tipos, relaciones, constraints o índices para adaptar la base de datos al código generado.

Las entidades y repositorios deben adaptarse al schema existente.

La conexión a Supabase debe utilizar la configuración existente del proyecto mediante las variables de entorno ya definidas.

No duplicar ni reemplazar la configuración de datasource existente sin necesidad.

---

## 3.7 Historial

El flujo utiliza `chat_context` como historial de conversación.

Debe conservarse el comportamiento observado en el workflow para:

* obtener el historial utilizado por el LLM;
* limitar el historial;
* guardar el mensaje del usuario;
* guardar la respuesta del asistente;
* mantener el orden de la conversación.

Los detalles exactos de lectura, eliminación e inserción están definidos en los nodos correspondientes de este SPEC.

---

## 3.8 Follow-Up Case

El flujo utiliza `follow_up_case` para conservar el estado de seguimiento.

Los valores válidos son:

```text
FEEL_BETTER
FEEL_SAME
FEEL_WORSE
WANT_TO_TALK
NOT_NOW
FEEL_ANXIOUS
FEEL_CONFUSED
FEEL_SAD
PREFER_TO_WRITE
```

Debe conservarse exactamente la lógica mediante la cual el workflow determina y actualiza este valor.

---

## 3.9 LLM

El LLM utilizado es DeepInfra.

La integración debe estar abstraída mediante:

```text
LlmPort
```

y un adaptador de infraestructura.

Configuración:

```text
DEEPINFRA_BASE_URL
DEEPINFRA_API_KEY
```

Modelo:

```text
deepseek-ai/DeepSeek-V4-Flash-0731
```

Base URL utilizada actualmente:

```text
https://api.deepinfra.com/v1
```

Las credenciales y valores configurables deben provenir del entorno.

No colocar secretos en código fuente.

Si el proyecto ya tiene una convención para clientes externos, reutilizarla.

---

## 3.10 Prompt y respuesta del LLM

La construcción del prompt debe reproducir el comportamiento del nodo que actualmente prepara las instrucciones para el AI Agent.

Debe considerar los datos definidos en el workflow, incluyendo:

* acción;
* opciones;
* `follow_up_case`;
* mensaje;
* historial;
* instrucciones específicas del caso.

La respuesta del LLM debe procesarse reproduciendo la lógica del workflow para:

```text
[CLOSE]
[OPTIONS:...]
```

Las marcas internas no deben aparecer en `answer` cuando el workflow las elimina.

---

## 3.11 Persistencia después del LLM

Después de procesar la respuesta del LLM se debe:

1. determinar `answer`;
2. determinar `options`;
3. guardar el mensaje del usuario;
4. guardar la respuesta del asistente;
5. actualizar `follow_up_case`;
6. devolver la respuesta final.

La llamada al LLM no debe mantenerse dentro de una transacción larga de base de datos.

---

## 3.12 Configuración y credenciales

No colocar credenciales directamente en:

* Java;
* código fuente;
* archivos versionados;
* JSON;
* configuración con secretos reales.

Utilizar las variables de entorno existentes del proyecto.

El usuario proporcionará los valores en `.env`.

Claude debe generar únicamente las referencias de configuración necesarias.

---

## 3.13 Reutilización

Antes de crear una clase nueva, revisar el código existente del backend.

Reutilizar abstracciones existentes cuando sean compatibles con la arquitectura.

No reutilizar clases de otros módulos únicamente para evitar crear un modelo propio.

La independencia de Patient tiene prioridad sobre una reutilización artificial.

---

## 3.14 Sin tests adicionales

Para esta migración **no crear carpeta ni archivos de tests específicos para Patient**.

No crear:

```text
src/test/java/io/mindoracare/patient/
```

No crear un nuevo módulo de tests.

No crear:

```text
src/main/java/io/mindoracare/test/
```

El código debe verificarse mediante compilación y las verificaciones funcionales disponibles en el proyecto.

---

## 3.15 Compilación y verificación

Después de implementar la migración:

1. compilar el proyecto;
2. corregir errores introducidos;
3. verificar que el endpoint exista;
4. verificar que Patient no dependa de `chatbot`;
5. verificar que Patient no dependa de n8n;
6. verificar el contrato de entrada;
7. verificar el contrato de salida.

La migración no se considera terminada únicamente porque el código haya sido generado.

---

# 4. Nodo 1 — Webhook `therapy-followup`

## 4.1 Función actual

El primer nodo expone el endpoint HTTP que recibe las solicitudes del flujo Patient Follow-Up.

Configuración funcional:

```text
Método:
POST

Ruta:
therapy-followup
```

El nodo recibe:

```json
{
  "message": "string",
  "userId": "string",
  "action": "string",
  "options": ["string"]
}
```

## 4.2 Migración

Este comportamiento debe convertirse en un endpoint REST de Quarkus.

Endpoint esperado:

```http
POST /public/patient/followup
```

Debe utilizar:

```java
@POST
@Path("/followup")
```

dentro del recurso correspondiente de Patient.

El endpoint debe delegar la lógica al caso de uso.

No colocar lógica de negocio compleja dentro del Resource.

Conceptualmente:

```text
HTTP Request
    ↓
PatientFollowUpResource
    ↓
PatientFollowUpUseCase
```

## 4.3 Salida

El endpoint debe devolver:

```json
{
  "answer": "string",
  "options": []
}
```

---

# 5. Nodo 2 — Code: normalización de entrada

## 5.1 Función actual

Este nodo normaliza los datos recibidos por el webhook antes de continuar con el flujo.

Los datos principales son:

```text
user_id / userId
message
action
options
```

## 5.2 Reglas

Debe conservarse la prioridad y comportamiento de normalización existente.

El identificador debe resolverse desde:

```text
user_id
```

o:

```text
userId
```

según el comportamiento definido por el workflow.

`message` debe normalizarse para evitar valores nulos inesperados.

`action` debe conservarse.

`options` debe convertirse en una lista válida cuando no exista.

Los elementos nulos o inválidos deben manejarse igual que en el workflow original.

## 5.3 Migración

Esta lógica debe implementarse dentro del modelo de entrada y/o caso de uso de Patient, de acuerdo con la arquitectura del backend.

No crear una clase que simplemente copie literalmente el Code Node si no es necesario.

El resultado funcional debe ser equivalente.

---

# 6. Nodo 3 — PostgreSQL: paciente, contexto y follow-up case

## 6.1 Función actual

Este nodo obtiene o crea el paciente y recupera la información necesaria para continuar la conversación.

El comportamiento incluye:

* identificar al paciente;
* crear el paciente si no existe;
* obtener `follow_up_case`;
* obtener el historial de conversación;
* limitar el historial utilizado por el flujo.

## 6.2 Paciente

El paciente debe identificarse mediante el `userId`.

Si no existe, debe crearse utilizando el comportamiento equivalente al workflow.

Si ya existe, debe reutilizarse el registro existente.

No crear duplicados.

## 6.3 Contexto

El flujo recupera los mensajes recientes del paciente.

La cantidad utilizada por el flujo es:

```text
6
```

El orden del historial debe conservar el comportamiento esperado por el prompt.

## 6.4 Migración

Separar las responsabilidades mediante puertos y adaptadores.

Por ejemplo:

```text
PatientRepositoryPort
ChatContextRepositoryPort
```

Los adaptadores deben implementar la interacción real con PostgreSQL.

No realizar SQL directamente desde el controller.

---

# 7. Nodo 4 — Code: procesamiento del Follow-Up

## 7.1 Función

Este nodo procesa los datos obtenidos de los nodos anteriores y prepara el contexto completo que será enviado al modelo LLM.

Debe realizar la misma lógica funcional del nodo original de n8n.

El resultado principal del nodo es un objeto que contiene, entre otros datos:

```json
{
  "user_id": "uuid",
  "action": "string",
  "message": "string",
  "option_input": [],
  "chat_history": [],
  "detected_case": "string",
  "previous_case": "string",
  "prompt": "string"
}
```

La implementación Java debe separar la lógica de negocio de la construcción/orquestación del caso de uso.

La lógica de determinación del caso y construcción del prompt debe ubicarse preferentemente en `domain/service`.

---

## 7.2 Datos de entrada

El procesamiento utiliza los siguientes valores:

### `user_id`

Identificador UUID del paciente.

Debe conservarse para las operaciones posteriores.

### `action`

Acción recibida en la solicitud.

Debe conservarse en el contexto procesado.

### `message`

Mensaje actual del paciente.

El flujo original obtiene el mensaje desde:

1. `message`
2. `mensaje`
3. `body`
4. `payload.body`

utilizando el primer valor disponible.

Si no existe ningún valor, utiliza:

```text
Hola
```

En la migración, el contrato HTTP debe utilizar `message` como campo oficial. Los nombres alternativos solamente deben mantenerse si forman parte de un comportamiento realmente requerido por el contrato existente.

### `follow_up_case`

Caso almacenado previamente para el paciente.

### `chat_history`

Historial reciente recuperado del almacenamiento.

Si no existe o no es una colección válida, se utiliza una colección vacía.

### `options`

Opciones recibidas en la solicitud.

---

## 7.3 Normalización de `options`

El nodo original acepta `options` tanto como colección como valor individual.

Si `options` es una colección:

1. convierte cada elemento a texto;
2. elimina espacios al inicio y al final;
3. elimina valores vacíos.

Si `options` es un único valor:

1. lo convierte a texto;
2. elimina espacios al inicio y al final;
3. lo incorpora como un único elemento.

El resultado debe almacenarse como:

```text
option_input
```

---

## 7.4 Normalización de opciones para detectar el caso

Para determinar el caso, cada opción debe:

1. convertirse a mayúsculas;
2. eliminar caracteres diacríticos/acentos;
3. normalizar espacios consecutivos.

Ejemplo:

```text
"feel worse"
```

se convierte conceptualmente en:

```text
FEEL_WORSE
```

La comparación debe realizarse contra los siguientes casos válidos:

```text
FEEL_BETTER
FEEL_SAME
FEEL_WORSE
WANT_TO_TALK
NOT_NOW
FEEL_ANXIOUS
FEEL_CONFUSED
FEEL_SAD
PREFER_TO_WRITE
```

---

## 7.5 Determinación de `detected_case`

El caso debe determinarse en este orden:

### Paso 1 — Detectar desde `options`

Se recorren las opciones normalizadas.

La primera opción que coincida con uno de los casos válidos determina:

```text
detected_case
```

Si ninguna opción coincide:

```text
detected_case = UNKNOWN
```

### Paso 2 — Utilizar `follow_up_case`

Si no se detectó un nuevo caso desde `options`, pero `follow_up_case` contiene uno de los casos válidos, debe utilizarse ese valor como:

```text
detected_case
```

Si tampoco existe un caso válido:

```text
detected_case = UNKNOWN
```

Este orden debe conservarse exactamente.

---

## 7.6 Instrucciones específicas por caso

El sistema debe conservar las instrucciones funcionales asociadas a cada caso.

### `FEEL_BETTER`

El paciente indicó que se siente mejor.

La instrucción debe orientar al modelo a:

* explorar cómo ha evolucionado;
* identificar qué le está ayudando;
* conocer cómo se encuentra actualmente;
* priorizar el cierre si existe una sensación clara de avance o tranquilidad.

### `FEEL_SAME`

El paciente indicó que se siente igual.

La instrucción debe orientar al modelo a:

* validar su experiencia;
* explorar qué sigue siendo difícil;
* explorar qué le gustaría que cambiara;
* evitar preguntas secundarias si ya expresó suficientemente lo que siente;
* considerar el cierre cuando corresponda.

### `FEEL_WORSE`

El paciente indicó que se siente peor.

La instrucción debe orientar al modelo a:

* validar lo que siente;
* mostrar empatía;
* permitir que explique qué está ocurriendo;
* explorar la situación cuidadosamente;
* no diagnosticar;
* cerrar cuando exista un punto natural de cierre.

### `WANT_TO_TALK`

El paciente quiere conversar.

La instrucción debe orientar al modelo a:

* ofrecer escucha activa;
* proporcionar un espacio seguro para expresarse;
* permitir que explique lo importante;
* cerrar cuando haya expresado suficientemente lo que necesitaba compartir y exista claridad o alivio.

### `NOT_NOW`

El paciente no quiere conversar ahora.

Debe:

* terminar la conversación de forma breve, cálida y respetuosa;
* no realizar preguntas;
* no intentar prolongar la conversación;
* incluir exactamente:

```text
[CLOSE]
```

No debe agregarse texto después de `[CLOSE]`.

### `FEEL_ANXIOUS`

La conversación corresponde a ansiedad.

Debe:

* validar lo que siente;
* permitir que explique libremente lo que experimenta;
* explorar únicamente aquello que aporte valor;
* considerar el cierre cuando exista suficiente comprensión o una forma de afrontamiento.

### `FEEL_CONFUSED`

La conversación corresponde a confusión.

Debe:

* validar lo que siente;
* ayudar a ordenar sus pensamientos mediante preguntas abiertas;
* priorizar el cierre cuando haya logrado identificar, organizar o comprender mejor lo que siente.

### `FEEL_SAD`

La conversación corresponde a tristeza.

Debe:

* brindar contención;
* validar lo que siente;
* permitir que se exprese;
* considerar el cierre si ya expresó lo importante y muestra alivio o tranquilidad.

### `PREFER_TO_WRITE`

El paciente prefiere expresarse por escrito.

Debe:

* permitir que se exprese libremente;
* acompañarlo mediante preguntas abiertas;
* cerrar cuando haya expresado suficientemente lo que necesitaba compartir.

### `UNKNOWN`

Debe utilizarse una instrucción general de:

* empatía;
* escucha activa;
* avance natural de la conversación;
* cierre cuando el paciente haya expresado suficientemente lo que necesitaba.

---

## 7.7 Regla especial `NOT_NOW`

Cuando:

```text
detected_case = NOT_NOW
```

el prompt debe indicar expresamente al LLM:

```text
El paciente no desea conversar ahora.
Debe cerrarse la conversación inmediatamente.
No deben realizarse preguntas.
No debe intentarse prolongar la conversación.
Debe agregarse exactamente [CLOSE].
No debe agregarse texto después de [CLOSE].
```

---

## 7.8 Regla general de cierre

Para todos los casos excepto `NOT_NOW`, el prompt debe indicar que el modelo es responsable de decidir cuándo la conversación llegó naturalmente a un punto adecuado para finalizar.

Debe conservarse la siguiente lógica funcional:

### Continuar cuando:

* todavía existe un aspecto importante que el paciente no ha expresado;
* el paciente acaba de revelar algo relevante;
* existe una emoción o situación importante que todavía está siendo explorada;
* el paciente muestra que necesita seguir expresándose;
* una pregunta abierta puede ayudar a comprender mejor lo que está viviendo.

### Favorecer el cierre cuando:

* el paciente explicó suficientemente lo que siente;
* identificó o aclaró lo que le ocurre;
* encontró algo que le ayuda;
* identificó una actividad, persona o recurso que genera bienestar;
* expresa alivio, tranquilidad, claridad o avance;
* tiene una idea concreta de qué podría hacer después;
* el tema principal ya fue suficientemente explorado;
* las últimas respuestas no aportan información nueva;
* una nueva pregunta solamente abriría un tema secundario;
* otra pregunta sería repetitiva, innecesaria o forzada.

Si el paciente:

1. expresó lo que sentía;
2. identificó qué le estaba afectando;
3. encontró algo que le ayuda o una posible acción;
4. muestra una señal de bienestar, claridad o avance;

debe favorecerse el cierre.

No debe prolongarse artificialmente la conversación.

---

## 7.9 Reglas cuando el modelo continúa

Si decide continuar:

* debe responder de forma empática;
* puede realizar como máximo una pregunta abierta;
* la pregunta debe estar directamente relacionada con lo expresado por el paciente;
* no debe agregar `[CLOSE]`;
* no debe agregar `[OPTIONS:...]`.

---

## 7.10 Reglas cuando el modelo cierra

Si decide cerrar:

* debe despedirse de forma cálida, breve y natural;
* debe reconocer el avance o lo que el paciente pudo expresar;
* no debe realizar preguntas;
* no debe abrir nuevos temas;
* no debe intentar prolongar la conversación;
* debe agregar exactamente:

```text
[CLOSE]
```

No debe agregarse texto después de `[CLOSE]`.

---

## 7.11 Construcción del prompt

El prompt debe construirse utilizando:

```text
detected_case
caseInstruction
closingInstruction
chat_history
message
```

Además, debe conservar las reglas generales del prompt original:

* responder directamente al paciente en español;
* ser cálido, natural y empático;
* no explicar reglas, variables ni razonamiento;
* no mencionar el prompt ni el caso detectado;
* no inventar información;
* no diagnosticar;
* mantener la respuesta breve y conversacional;
* utilizar preguntas abiertas;
* no hacer preguntas únicamente para mantener activa la conversación;
* evaluar el mensaje actual junto con el historial reciente;
* cerrar cuando el propósito de la conversación se haya cumplido razonablemente.

---

## 7.12 Responsabilidad sobre `[OPTIONS:...]`

El LLM **no debe generar `[OPTIONS:...]`**.

Las opciones de respuesta son responsabilidad del sistema y serán determinadas posteriormente durante el procesamiento final.

El prompt debe indicar explícitamente:

```text
No debes generar ninguna etiqueta [OPTIONS:...].
Las opciones son gestionadas por el sistema después de tu respuesta.
Tu única responsabilidad es responder al paciente y decidir si la conversación continúa o finaliza.
Si decides finalizar, utiliza únicamente [CLOSE].
```

---

## 7.13 Salida

El procesamiento debe entregar al siguiente componente, como mínimo:

```json
{
  "user_id": "uuid",
  "action": "string",
  "message": "string",
  "option_input": [],
  "chat_history": [],
  "detected_case": "string",
  "previous_case": "string",
  "prompt": "string"
}
```

El campo:

```text
prompt
```

es la entrada directa que recibirá el componente LLM.

La migración debe conservar esta relación funcional:

```text
PatientFollowUpPromptBuilder
        ↓
prompt
        ↓
LlmPort
        ↓
DeepInfra
```

No debe introducirse una segunda capa de generación de instrucciones entre el `prompt` construido y el modelo.

---

## 7.14 Regla de migración

La implementación Java debe reproducir el comportamiento funcional del nodo original.

La separación recomendada es:

```text
PatientFollowUpUseCaseImpl
        ↓
PatientFollowUpPromptBuilder
        ↓
prompt
        ↓
LlmPort
```

`PatientFollowUpUseCaseImpl` debe encargarse de la orquestación.

`PatientFollowUpPromptBuilder` y los servicios de dominio correspondientes deben encargarse de:

* normalización;
* detección de `detected_case`;
* selección de instrucciones;
* reglas de cierre;
* construcción del prompt.

No debe modificarse el comportamiento funcional por iniciativa propia.

---

# 8. Nodo 5 — AI Agent

## 8.1 Función actual

Este nodo envía el prompt generado al modelo de inteligencia artificial.

Configuración actual:

```text
Provider:
DeepInfra

Base URL:
https://api.deepinfra.com/v1

Model:
deepseek-ai/DeepSeek-V4-Flash-0731
```

## 8.2 Migración

Implementar una abstracción:

```text
LlmPort
```

y un adaptador:

```text
DeepInfraLlmAdapter
```

El adaptador debe encargarse de la comunicación con DeepInfra.

El dominio no debe conocer detalles HTTP específicos de DeepInfra.

## 8.3 Configuración

La API key debe obtenerse mediante configuración externa:

```text
DEEPINFRA_API_KEY
```

La URL debe poder configurarse mediante:

```text
DEEPINFRA_BASE_URL
```

No hardcodear credenciales.

## 8.4 Resultado

El adaptador debe devolver al caso de uso el texto generado por el modelo para que pueda ser procesado por el siguiente paso.

---

# 9. Nodo 6 — PostgreSQL: procesamiento final y persistencia

## 9.1 Función

Este nodo recibe:

* el mensaje original del paciente;
* el `detected_case` calculado por el Nodo 4;
* `option_input`;
* la respuesta generada por el LLM.

Debe:

1. procesar las marcas internas de la respuesta del LLM;
2. determinar si la conversación fue cerrada;
3. limpiar la respuesta antes de entregarla al paciente;
4. determinar las opciones finales;
5. mantener el historial de conversación;
6. guardar el mensaje del paciente;
7. guardar la respuesta del asistente;
8. actualizar `patient.follow_up_case`;
9. devolver:

```json
{
  "answer": "string",
  "options": []
}
```

---

## 9.2 Datos utilizados

El procesamiento final utiliza:

```text
patient_id
user_message
raw_ai_response
detected_case
option_input
```

`patient_id` corresponde al UUID del paciente.

`user_message` corresponde al mensaje actual del paciente.

`raw_ai_response` corresponde a la respuesta recibida del LLM.

`detected_case` corresponde al caso determinado en el Nodo 4.

`option_input` corresponde a las opciones recibidas originalmente en la solicitud.

---

## 9.3 Detección de `[CLOSE]`

Debe determinarse si la respuesta del LLM contiene:

```text
[CLOSE]
```

El resultado debe almacenarse conceptualmente como:

```text
is_closed = true
```

cuando la marca exista.

La marca `[CLOSE]` es interna y nunca debe aparecer en el `answer` final.

---

## 9.4 Limpieza de `[CLOSE]`

Antes de guardar y devolver la respuesta del asistente, debe eliminarse:

```text
[CLOSE]
```

incluyendo los espacios asociados a la marca cuando corresponda.

El resultado limpio debe utilizarse como:

```text
clean_answer
```

---

## 9.5 Detección de `[OPTIONS:...]`

El procesamiento debe reconocer una marca con la siguiente estructura:

```text
[OPTIONS:[...]]
```

Debe:

1. detectar la marca;
2. extraer su contenido;
3. conservar el comportamiento de parsing existente;
4. eliminar la marca de la respuesta final.

La extracción de esta información forma parte del procesamiento técnico de la respuesta del LLM.

Sin embargo, las opciones extraídas de `[OPTIONS:...]` **no determinan por sí mismas las opciones finales del endpoint en el flujo actual**.

El conjunto de opciones finales se determina mediante las reglas descritas posteriormente.

---

## 9.6 Eliminación de `[OPTIONS:...]`

La marca:

```text
[OPTIONS:...]
```

es interna.

No debe aparecer en:

```text
answer
```

La respuesta final debe contener únicamente el texto destinado al paciente.

---

## 9.7 Opciones iniciales para `FEEL_WORSE`

Existe una regla especial para las solicitudes cuyo `option_input` contiene:

```text
FEEL_WORSE
```

En este caso, las opciones finales deben ser exactamente:

```json
[
  "FEEL_ANXIOUS",
  "FEEL_CONFUSED",
  "FEEL_SAD",
  "PREFER_TO_WRITE"
]
```

Esta regla tiene prioridad en la selección final de opciones.

Por tanto, si `option_input` contiene `FEEL_WORSE`, las opciones anteriores deben devolverse independientemente de que el LLM haya generado `[CLOSE]`.

---

## 9.8 Opciones al cerrar según `detected_case`

Si no aplica la regla especial de `FEEL_WORSE` y la respuesta contiene `[CLOSE]`, las opciones se determinan según `detected_case`.

### `NOT_NOW`

Debe devolver:

```json
[]
```

### `FEEL_ANXIOUS`

Debe devolver:

```json
[
  "BOOK_NEW_APPOINTMENT",
  "TALK_TO_MY_PSYCHOLOGIST",
  "NOT_NOW"
]
```

### `FEEL_CONFUSED`

Debe devolver:

```json
[
  "BOOK_NEW_APPOINTMENT",
  "TALK_TO_MY_PSYCHOLOGIST",
  "NOT_NOW"
]
```

### `FEEL_SAD`

Debe devolver:

```json
[
  "BOOK_NEW_APPOINTMENT",
  "TALK_TO_MY_PSYCHOLOGIST",
  "NOT_NOW"
]
```

### `PREFER_TO_WRITE`

Debe devolver:

```json
[
  "BOOK_NEW_APPOINTMENT",
  "TALK_TO_MY_PSYCHOLOGIST",
  "NOT_NOW"
]
```

### `WANT_TO_TALK`

Debe devolver:

```json
[
  "SAVE_IDEAS_FOR_NEXT_SESSION",
  "WRITE_TO_MY_PSYCHOLOGIST",
  "VIEW_NEXT_SESSION_AVAILABILITY",
  "THANKS_FOR_NOW"
]
```

### `FEEL_BETTER`

Debe devolver:

```json
[
  "REGISTER_FEELINGS",
  "VIEW_RECOMMENDED_RESOURCES",
  "WRITE_TO_MY_PSYCHOLOGIST",
  "THANKS_FOR_NOW"
]
```

### `FEEL_SAME`

Debe devolver:

```json
[
  "REGISTER_FEELINGS",
  "VIEW_RECOMMENDED_RESOURCES",
  "WRITE_TO_MY_PSYCHOLOGIST",
  "THANKS_FOR_NOW"
]
```

### Otros casos

Debe devolver:

```json
[]
```

---

## 9.9 Cuando no existe `[CLOSE]`

Si:

```text
is_closed = false
```

y no se activa la regla especial de `FEEL_WORSE`, las opciones finales deben ser:

```json
[]
```

El sistema no debe mostrar opciones de cierre mientras la conversación continúa.

---

## 9.10 Prioridad para determinar `options`

La selección final debe conservar exactamente este orden:

### Prioridad 1

Si:

```text
option_input contiene FEEL_WORSE
```

devolver:

```json
[
  "FEEL_ANXIOUS",
  "FEEL_CONFUSED",
  "FEEL_SAD",
  "PREFER_TO_WRITE"
]
```

### Prioridad 2

Si no se cumple lo anterior y:

```text
is_closed = true
```

determinar las opciones según `detected_case`.

### Prioridad 3

Si ninguna de las anteriores aplica:

```json
[]
```

---

## 9.11 Mantenimiento del historial

El historial se almacena en:

```text
chat_context
```

Para el paciente correspondiente.

Deben almacenarse dos nuevos registros:

### Mensaje del paciente

```text
role = user
message = user_message
```

### Respuesta del asistente

```text
role = assistant
message = clean_answer
```

---

## 9.12 Límite del historial

Antes de insertar los nuevos mensajes, debe conservarse el comportamiento existente del workflow.

Se cuentan los registros actuales de `chat_context` correspondientes al paciente.

Si existen 6 o más registros:

```text
total_count >= 6
```

deben eliminarse los 2 registros más antiguos, ordenados por:

```text
created_at ASC
```

y limitados a:

```text
2
```

Después se insertan el nuevo mensaje del usuario y la nueva respuesta del asistente.

El objetivo es mantener el límite utilizado por el workflow original.

---

## 9.13 Actualización de `follow_up_case`

Después del procesamiento debe actualizarse:

```text
patient.follow_up_case
```

con:

```text
detected_case
```

Si `detected_case` es una cadena vacía, debe almacenarse como `NULL`.

El paciente actualizado corresponde al:

```text
patient_id
```

de la solicitud actual.

---

## 9.14 Respuesta final

El nodo debe producir:

```json
{
  "answer": "string",
  "options": []
}
```

`answer` debe corresponder a:

```text
clean_answer
```

sin:

```text
[CLOSE]
```

ni:

```text
[OPTIONS:...]
```

`options` debe determinarse mediante las reglas de prioridad definidas en este nodo.

---

## 9.15 Regla de transacción

La persistencia relacionada con el procesamiento final debe conservar la consistencia entre:

* eliminación de historial antiguo, cuando corresponda;
* inserción del mensaje del usuario;
* inserción de la respuesta del asistente;
* actualización de `patient.follow_up_case`.

La implementación debe evitar dejar el sistema en un estado parcialmente actualizado.

La llamada al LLM debe realizarse fuera de una transacción de base de datos de larga duración. La transacción de persistencia debe comenzar únicamente cuando la respuesta del LLM ya esté disponible.

---

## 9.16 Migración

En Java, esta lógica debe distribuirse según la arquitectura existente.

El caso de uso debe orquestar el proceso.

La lógica de parsing y determinación de resultado puede ubicarse en servicios de dominio/aplicación.

La persistencia debe realizarse mediante los puertos y adaptadores correspondientes.

No debe mantenerse SQL embebido dentro del caso de uso como sustituto de los repositorios definidos por la arquitectura.

El comportamiento funcional del workflow original debe mantenerse.


# 10. Nodo 7 — Respond to Webhook

## 10.1 Función actual

El último nodo devuelve la respuesta final al cliente que realizó la solicitud HTTP.

## 10.2 Migración

En Quarkus, el `PatientFollowUpResource` debe devolver directamente el resultado del caso de uso.

No debe existir un segundo HTTP request interno para simular el `Respond to Webhook` de n8n.

Conceptualmente:

```text
PatientFollowUpUseCase
        ↓
PatientFollowUpResponse
        ↓
PatientFollowUpResource
        ↓
HTTP Response
```

## 10.3 Respuesta final

Debe ser exactamente equivalente al resultado esperado:

```json
{
  "answer": "string",
  "options": []
}
```

---

# 11. Flujo completo esperado

La migración completa debe producir el siguiente flujo:

```text
POST /public/patient/followup
        │
        ▼
PatientFollowUpResource
        │
        ▼
Normalización de entrada
        │
        ▼
PatientFollowUpUseCase
        │
        ├──────────────► PatientRepositoryPort
        │                       │
        │                       ▼
        │                   PostgreSQL
        │
        ├──────────────► ChatContextRepositoryPort
        │                       │
        │                       ▼
        │                   PostgreSQL
        │
        ▼
Determinar Follow-Up Case
        │
        ▼
Construir Prompt
        │
        ▼
LlmPort
        │
        ▼
DeepInfra
        │
        ▼
Procesar respuesta
        │
        ├── [CLOSE]
        ├── [OPTIONS:...]
        │
        ▼
Guardar usuario + asistente
        │
        ▼
Actualizar follow_up_case
        │
        ▼
PatientFollowUpResponse
        │
        ▼
HTTP Response
```

No debe existir n8n dentro de este flujo.

---

# 12. Criterios de aceptación finales

La migración se considera correcta cuando se cumpla todo lo siguiente:

1. Existe un módulo:

```text
io.mindoracare.patient
```

2. Existe el endpoint:

```http
POST /public/patient/followup
```

3. El endpoint recibe:

```json
{
  "message": "string",
  "userId": "string",
  "action": "string",
  "options": []
}
```

4. Devuelve:

```json
{
  "answer": "string",
  "options": []
}
```

5. Patient no depende de:

```text
io.mindoracare.chatbot
```

6. Patient no utiliza:

```text
FollowUpN8nGateway
```

7. Patient no realiza llamadas HTTP a n8n.

8. Patient persiste y recupera correctamente el paciente.

9. Patient recupera el contexto de conversación.

10. Patient utiliza el historial requerido por el workflow.

11. Patient conserva `follow_up_case`.

12. Patient conserva las acciones:

```text
FEEL_BETTER
FEEL_SAME
FEEL_WORSE
WANT_TO_TALK
NOT_NOW
FEEL_ANXIOUS
FEEL_CONFUSED
FEEL_SAD
PREFER_TO_WRITE
```

13. Patient utiliza DeepInfra mediante un puerto/adaptador.

14. Las credenciales no están hardcodeadas.

15. Se conserva el procesamiento de:

```text
[CLOSE]
[OPTIONS:...]
```

16. Se guardan los mensajes del usuario y asistente.

17. Se actualiza `follow_up_case`.

18. El código compila.

19. Los tests del módulo Patient pasan.

20. No se crea un nuevo módulo productivo bajo:

```text
src/main/java/io/mindoracare/test/
```

21. No se modifica funcionalmente otro módulo del backend sin necesidad para esta migración.

22. El resultado final debe comportarse de forma equivalente al workflow original de n8n.

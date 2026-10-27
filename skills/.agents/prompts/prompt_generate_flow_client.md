Quiero que regeneres desde cero la implementación del módulo `client` para mi backend Java + Quarkus.

La implementación actual del módulo `client` será eliminada antes de comenzar. Debes generar nuevamente toda la implementación a partir de las fuentes de verdad indicadas abajo.

No debes intentar conservar ni reutilizar automáticamente la implementación anterior de `client`.

---

# 1. LECTURA OBLIGATORIA ANTES DE MODIFICAR

Antes de crear, modificar o eliminar cualquier archivo, debes leer completamente:

1. `conversation/SKILL.md`

2. `conversation/spec_client/SPEC.md`

3. El workflow JSON de n8n correspondiente al flujo Client que te proporcionaré.

4. El nuevo archivo SQL con el schema real de PostgreSQL/Supabase que te proporcionaré.

5. El código existente del backend.

Debes revisar también la arquitectura y convenciones utilizadas por los módulos existentes, especialmente `patient`, para mantener consistencia con el proyecto.

No comiences a implementar hasta haber leído todas estas fuentes.

---

# 2. FUENTES DE VERDAD

Cada fuente tiene una responsabilidad específica:

* `SKILL.md`: define cómo debe realizarse la migración y las reglas arquitectónicas.
* `SPEC.md`: define el comportamiento funcional esperado del flujo Client.
* Workflow n8n: define y permite verificar el comportamiento actual del flujo.
* SQL de Supabase: define exactamente la estructura real y actual de la base de datos.
* Backend existente: define la arquitectura, paquetes, convenciones y componentes reutilizables.

## FUENTE DE VERDAD DE BASE DE DATOS

El SQL de PostgreSQL/Supabase es la fuente de verdad absoluta para la base de datos.

Debes utilizar exactamente:

* tablas;
* columnas;
* tipos;
* relaciones;
* foreign keys;
* constraints;
* defaults;
* índices;
* nombres de columnas.

No infieras columnas que no existan en el SQL.

No mantengas mappings correspondientes a columnas que hayan sido eliminadas del nuevo schema.

---

# 3. OBJETIVO

Migrar completamente el flujo Client desde n8n hacia Java + Quarkus.

Implementa el módulo:

```text
io.mindoracare.client
```

El módulo debe ejecutar el flujo Client directamente desde Java.

Client no debe utilizar n8n en runtime.

No debe realizar llamadas HTTP hacia n8n.

La lógica funcional debe quedar dentro de Java + Quarkus, manteniendo el comportamiento definido por:

```text
SPEC.md
+
workflow n8n
```

---

# 4. BASE DE DATOS

Utiliza exclusivamente el nuevo schema SQL proporcionado.

El nuevo schema reemplaza cualquier conocimiento anterior de la estructura de las tablas Client.

No asumas que las columnas existentes en una implementación anterior siguen existiendo.

En particular, debes revisar cuidadosamente la nueva estructura de:

```text
client
specialty
client_chat_context
advisor
inquiry
ticket
faq_category
faq
```

y cualquier otra tabla realmente utilizada por el flujo Client.

## IMPORTANTE

La tabla `client` actual ya no contiene:

```text
history
failed_attempts
is_blocked
```

No generes código que dependa de esas columnas.

El bloqueo/desbloqueo debe implementarse utilizando exclusivamente las columnas existentes en el nuevo schema, especialmente:

```text
blocked_until
state
```

No inventes una columna alternativa para representar el bloqueo.

---

# 5. NO MODIFICAR LA BASE DE DATOS

No generes ni ejecutes migraciones.

No generes:

```text
CREATE TABLE
ALTER TABLE
DROP TABLE
```

como parte de la implementación Java.

No agregues:

* columnas;
* tablas;
* relaciones;
* índices;
* constraints;
* tipos personalizados;

para adaptar la base de datos al código.

La base de datos ya existe.

Si existe una diferencia entre el código y el schema, adapta el código Java al schema real.

No adaptes Supabase al código.

---

# 6. ARQUITECTURA

Respeta la arquitectura definida por `SKILL.md` y las convenciones del backend.

Utiliza una arquitectura limpia/hexagonal cuando corresponda:

```text
client
├── application
├── domain
└── infrastructure
```

Separa correctamente:

* modelos de entrada/salida;
* casos de uso;
* puertos;
* servicios de dominio;
* entidades;
* repositorios;
* adapters;
* clientes HTTP;
* controllers.

No hagas una traducción literal de cada nodo n8n a una clase.

Identifica primero las responsabilidades funcionales y después ubícalas correctamente dentro de la arquitectura.

---

# 7. REUTILIZACIÓN

Puedes revisar los módulos existentes, especialmente:

```text
io.mindoracare.patient
io.mindoracare.chatbot
```

para entender las convenciones del proyecto.

Sin embargo:

No reutilices clases de `chatbot` solamente por conveniencia.

No acoples Client a `chatbot` si no es necesario.

Client debe ser un módulo independiente.

Puedes reutilizar componentes existentes únicamente cuando sean realmente compatibles con la arquitectura y el comportamiento requerido.

---

# 8. FLUJO CLIENT

Implementa exactamente el comportamiento definido en:

```text
conversation/spec_client/SPEC.md
```

y verifica cada comportamiento contra el workflow n8n.

Debes preservar como mínimo las responsabilidades existentes del flujo:

* recepción del request;
* extracción de `message`;
* extracción de `userId`;
* extracción de `action`;
* identificación del cliente;
* generación del IP a partir del Host y userId cuando corresponda;
* creación del cliente cuando no existe;
* desbloqueo cuando corresponda;
* manejo del estado del cliente;
* recuperación del contexto conversacional;
* construcción del prompt;
* llamada al LLM;
* procesamiento de `[ACTION=...]`;
* procesamiento de `[CATEGORY=...]`;
* reemplazo de `[NAME]`;
* flujo `CRISIS`;
* flujo `SECURITY`;
* flujo `QUESTIONNAIRE`;
* flujo `NOT_UNDERSTOOD`;
* flujo `MATCH_SUCCESS`;
* persistencia de `inquiry`;
* persistencia de `client_chat_context`;
* manejo de `specialty`;
* creación de `ticket` cuando corresponda;
* selección del advisor cuando corresponda;
* bloqueo del cliente cuando corresponda;
* limpieza del contexto cuando corresponda;
* consulta de psicólogos cuando corresponda;
* respuesta HTTP final.

No inventes reglas funcionales.

No simplifiques reglas que estén definidas en el SPEC o workflow.

---

# 9. ACTION

Debes respetar exactamente las acciones definidas por el flujo Client.

Incluyendo:

```text
CRISIS
SECURITY
MISSING_DATA
MATCH_SUCCESS
QUESTIONNAIRE
NOT_UNDERSTOOD
```

Revisa el `SPEC.md` y workflow para determinar cuáles acciones realmente llegan a cada rama.

IMPORTANTE:

`NOT_UNDERSTOOD` debe continuar directamente hacia la respuesta final.

No debe:

* consultar psicólogos;
* modificar el estado del cliente;
* eliminar `client_chat_context`;
* crear tickets;
* ejecutar lógica de `MATCH_SUCCESS`;
* realizar consultas innecesarias a PostgreSQL.

`MATCH_SUCCESS` debe contener la lógica correspondiente a la búsqueda de psicólogos.

No mezcles ambas ramas.

---

# 10. CRISIS

Implementa el flujo CRISIS exactamente como está definido en `SPEC.md` y el workflow.

Debe respetar:

* respuesta al usuario;
* persistencia correspondiente;
* bloqueo del cliente;
* `blocked_until`;
* `state`;
* limpieza del contexto;
* creación de la solicitud externa;
* separación entre lógica de negocio y cliente HTTP externo.

No agregues comportamiento que no exista en las fuentes de verdad.

---

# 11. SECURITY

Implementa exactamente el flujo SECURITY.

Debe respetar:

* persistencia de la interacción;
* selección del advisor según las reglas existentes;
* creación del ticket;
* estado del ticket;
* bloqueo del cliente;
* `blocked_until`;
* limpieza del contexto;
* notificación al advisor mediante WAHA;
* respuesta final al cliente.

Revisa el workflow y el SPEC para determinar exactamente qué operaciones ocurren y en qué orden.

---

# 12. QUESTIONNAIRE

Implementa el cuestionario P1 → P2 → P3 → P4 exactamente como está definido.

No cambies:

* preguntas;
* orden;
* reglas de avance;
* reglas de validación;
* acciones;
* categorías;
* comportamiento ante respuestas inválidas.

El historial debe utilizar `client_chat_context` según el schema real.

No utilices una columna `history` en `client`, porque esa columna ya no existe.

---

# 13. MATCH_SUCCESS

Implementa la rama MATCH_SUCCESS exactamente según el workflow y `SPEC.md`.

Debe:

1. Resolver la especialidad.
2. Obtener `specialty_id`.
3. Persistir la interacción correspondiente.
4. Consultar el servicio de afinidad de psicólogos.
5. Procesar los psicólogos obtenidos.
6. Construir la respuesta.
7. Limpiar el contexto.
8. Restablecer el estado correspondiente del cliente.
9. Actualizar la última interacción cuando corresponda según la especificación.
10. Devolver la respuesta final.

Utiliza exactamente los campos definidos por la respuesta GraphQL real.

No agregues campos que no existan en la respuesta.

---

# 14. MEMORIA / CONTEXTO

El historial conversacional debe utilizar:

```text
client_chat_context
```

No utilices:

```text
client.history
```

porque esa columna ya no existe.

Respeta:

* `role`;
* `message`;
* `created_at`;
* `client_id`;
* límite de contexto definido en el SPEC/workflow;
* orden cronológico.

La memoria debe mantenerse como responsabilidad de la aplicación y PostgreSQL.

No introduzcas LangChain solamente para manejar memoria.

---

# 15. LLM / DEEPINFRA

Utiliza la integración existente con DeepInfra.

Configuración:

```text
DEEPINFRA_API_KEY
DEEPINFRA_BASE_URL
```

Modelo:

```text
deepseek-ai/DeepSeek-V4-Flash-0731
```

No hardcodees credenciales.

Reutiliza la configuración existente de:

```text
application.properties
.env
```

No dupliques configuración innecesariamente.

No reemplaces la configuración existente si ya funciona.

---

# 16. SYSTEM MESSAGE

Revisa cuidadosamente el workflow n8n para determinar si existe un system message.

Si existe, debes reproducirlo correctamente en la implementación Java.

Si no existe, no inventes uno nuevo solamente por conveniencia.

El comportamiento del LLM debe corresponder al flujo real definido por n8n y el SPEC.

---

# 17. TOKENS / USAGE

Revisa la respuesta real de DeepInfra.

Si la API devuelve información de uso de tokens, como:

```text
prompt_tokens
completion_tokens
total_tokens
```

no la descartes innecesariamente.

Evalúa la forma correcta de conservar esta información dentro de la integración LLM sin alterar el comportamiento funcional existente.

No agregues tablas de métricas ni cambios de base de datos salvo que estén definidos explícitamente por el schema/spec.

La obtención del usage debe mantenerse compatible con la arquitectura existente.

---

# 18. CONFIGURACIÓN EXTERNA

Revisa y utiliza las configuraciones existentes para:

* DeepInfra;
* psychologist affinity API;
* crisis request API;
* WAHA.

No hardcodees:

* API keys;
* passwords;
* tokens;
* URLs que ya estén parametrizadas;
* credenciales.

Si el proyecto ya tiene configuración reutilizable, úsala.

---

# 19. ENDPOINT

Implementa el endpoint definido por la especificación y las convenciones actuales del proyecto.

El endpoint Client debe quedar bajo:

```text
/rest/public/client/chat
```

Verifica que el request y response tengan exactamente la estructura esperada por el flujo actual.

---

# 20. MODELOS

Revisa cuidadosamente los modelos de entrada y salida.

El request debe soportar los datos utilizados por el flujo actual, incluyendo:

```text
message
userId
action
```

No agregues campos innecesarios.

La respuesta final debe mantener el contrato funcional existente.

Cuando corresponda, la respuesta debe tener:

```json
{
  "answer": "..."
}
```

No agregues campos adicionales al response final si el SPEC/workflow no los requiere.

---

# 21. TESTS

No generes tests.

No crees:

```text
src/test/java/io/mindoracare/client/
```

No crees ni modifiques:

```text
io.mindoracare.test
```

como parte de esta migración.

---

# 22. OTROS MÓDULOS

No modifiques:

```text
patient
chatbot
therapy
faq
```

ni otros módulos existentes, salvo que exista una dependencia técnica real y estrictamente necesaria.

Si necesitas modificar otro módulo, debes justificarlo al finalizar.

No elimines componentes existentes de n8n que puedan ser utilizados por otros flujos.

---

# 23. ARCHIVOS DE LA IMPLEMENTACIÓN ANTERIOR

La implementación anterior de:

```text
io.mindoracare.client
```

será eliminada antes de esta generación.

Debes tratar la nueva implementación como una regeneración desde cero.

No copies automáticamente:

* entidades;
* repositorios;
* adapters;
* servicios;
* modelos;

de la implementación anterior.

Puedes utilizarla únicamente como referencia si existe alguna información que deba verificarse contra el SPEC, workflow o schema, pero las fuentes de verdad tienen prioridad.

---

# 24. PROCESO

Trabaja de principio a fin sin pedirme aprobación entre pasos.

Debes:

1. Leer `SKILL.md`.
2. Leer `SPEC.md`.
3. Leer el workflow n8n.
4. Leer el SQL nuevo.
5. Revisar la arquitectura existente.
6. Revisar el módulo `patient` como referencia arquitectónica.
7. Revisar los componentes existentes relacionados con LLM y clientes HTTP.
8. Diseñar mentalmente la correspondencia de responsabilidades.
9. Crear el módulo `client`.
10. Compilar.
11. Corregir errores de compilación.
12. Validar los mappings contra el schema real.
13. Ejecutar package.
14. Revisar dependencias y acoplamientos.

No me pidas aprobación entre estos pasos.

Solo detente si existe una ambigüedad realmente bloqueante que no pueda resolverse revisando las fuentes proporcionadas.

---

# 25. VERIFICACIÓN FINAL

Después de implementar, debes verificar:

1. `mvn clean compile`

2. `mvn package`

3. Que los mappings Java coincidan con el SQL real.

4. Que no existan referencias a columnas eliminadas.

5. Que no exista ninguna referencia a:

```text
client.history
client.failed_attempts
client.is_blocked
```

6. Que el bloqueo utilice exclusivamente las columnas reales:

```text
blocked_until
state
```

7. Que el contexto utilice:

```text
client_chat_context
```

8. Que Client no dependa de n8n en runtime.

9. Que Client no dependa innecesariamente de `chatbot`.

10. Que el endpoint sea:

```text
POST /rest/public/client/chat
```

11. Que DeepInfra esté correctamente integrado.

12. Que los clientes HTTP externos utilicen la configuración existente.

13. Que no se hayan creado migraciones.

14. Que no se hayan modificado tablas desde Java.

15. Que no se hayan creado tests.

---

# 26. REGLA IMPORTANTE SOBRE CONTRADICCIONES

Si encuentras una diferencia entre:

```text
SPEC
workflow n8n
SQL
código existente
```

no inventes una solución.

Determina primero cuál fuente tiene prioridad:

* comportamiento funcional → `SPEC.md` + workflow n8n;
* estructura de BD → SQL;
* arquitectura → `SKILL.md` + backend existente.

Si la diferencia puede resolverse revisando otra de estas fuentes, hazlo tú mismo.

Solo detente si después de revisar todas las fuentes existe una contradicción que realmente impida implementar correctamente el módulo.

---

# 27. RESULTADO FINAL

Al finalizar, dame un resumen conciso con:

* archivos creados;
* archivos modificados;
* archivos eliminados, si corresponde;
* endpoint;
* tablas utilizadas;
* integración con Supabase/PostgreSQL;
* integración con DeepInfra;
* integración con APIs externas;
* manejo de memoria/contexto;
* manejo de tokens/usage, si está disponible;
* resultado de `mvn clean compile`;
* resultado de `mvn package`;
* validación de mappings contra el schema;
* dependencias con otros módulos;
* cualquier pendiente real.

No quiero solamente una propuesta o explicación.

Quiero que realices directamente la implementación completa en el repositorio.

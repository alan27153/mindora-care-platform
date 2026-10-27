Quiero que regeneres desde cero la implementación del módulo `client` para mi backend Java + Quarkus.

Antes de modificar cualquier archivo, debes leer completamente:

1. `conversation/SKILL.md`

2. `conversation/spec_client/SPEC.md`

3. El workflow JSON de n8n que te proporcionaré.

4. El archivo SQL del schema real de PostgreSQL/Supabase que te proporcionaré.

5. El código existente del backend.

## Fuentes de verdad

Utiliza cada fuente para su propósito:

* `SKILL.md`: define cómo debes realizar la migración.
* `SPEC.md`: define el comportamiento funcional requerido.
* Workflow n8n: define y permite verificar el comportamiento actual.
* SQL de Supabase: define exactamente la estructura real de la base de datos.
* Backend existente: define la arquitectura y convenciones que debes respetar.

**El SQL de Supabase es la fuente de verdad para la base de datos.**

No infieras tipos, columnas, relaciones ni constraints cuando estén definidos en el SQL.

## Objetivo

Migrar completamente el flujo Client desde n8n hacia Java + Quarkus.

Implementa el módulo:

```text
io.mindoracare.client
```

Debe ser independiente de:

```text
io.mindoracare.chatbot
```

Client no debe utilizar n8n en runtime.

No realices llamadas HTTP hacia n8n.

## Base de datos

Utiliza exactamente las tablas, columnas, tipos, relaciones, constraints, defaults e índices definidos en el SQL proporcionado.

Las entidades Java y los repositorios deben adaptarse a la base de datos existente.

**No modifiques la base de datos.**

No generes:

* `CREATE TABLE`;
* `ALTER TABLE`;
* migraciones;
* cambios de tipos;
* nuevas columnas;
* nuevas relaciones.

No intentes adaptar Supabase al código.

Si Hibernate propone modificar el schema para resolver una diferencia, no ejecutes ni generes ese cambio como solución. Corrige el mapping Java para que coincida con el schema real.

Presta especial atención a `UUID`, `TEXT`, timestamps, relaciones y claves foráneas.

## Configuración

Reutiliza la configuración existente del proyecto.

La conexión a Supabase ya está configurada mediante `.env` y `application.properties`.

No dupliques ni reemplaces innecesariamente la configuración existente.

Para DeepInfra utiliza la configuración existente:

```text
DEEPINFRA_API_KEY
DEEPINFRA_BASE_URL
```

Modelo:

```text
deepseek-ai/DeepSeek-V4-Flash-0731
```

No hardcodees credenciales.

## Implementación

No hagas una traducción literal de cada nodo n8n a una clase.

Identifica las responsabilidades y colócalas correctamente dentro de la arquitectura existente.

Reutiliza las abstracciones existentes cuando sean realmente compatibles.

No reutilices clases de `chatbot` solamente por conveniencia.

Mantén Client independiente.

## Comportamiento

Implementa exactamente el comportamiento definido por:

```text
SPEC.md
+
workflow n8n
```

Presta especial atención a:

* entrada `message`, `userId` y `action`;
* identificación y creación del cliente;
* `blocked_until` y `state`;
* `client_chat_context`;
* historial y construcción del prompt;
* instrucciones del LLM;
* `[ACTION=...]`;
* `[CATEGORY=...]`;
* `QUESTIONNAIRE`;
* `CRISIS`;
* `SECURITY`;
* `NOT_UNDERSTOOD`;
* `MATCH_SUCCESS`;
* persistencia de `inquiry`;
* asignación de `specialty_id`;
* creación de `ticket` y asignación de `advisor`;
* bloqueo del cliente;
* eliminación/reset del contexto;
* búsqueda de psicólogos;
* respuesta HTTP.

Implementa exactamente las reglas definidas en `SPEC.md` y verificadas contra el workflow.

### Reglas importantes

`NOT_UNDERSTOOD` debe ir directamente a la respuesta final.

No debe ejecutar:

* búsqueda de psicólogos;
* consultas adicionales;
* limpieza de contexto;
* actualización de estado;
* actualización de `last_interaction`.

`MATCH_SUCCESS` es responsable del flujo de búsqueda de psicólogos y debe mantener el comportamiento definido actualmente en el workflow.

`CRISIS` debe mantener su flujo actual, incluyendo la solicitud externa correspondiente.

`SECURITY` debe mantener su flujo actual, incluyendo:

* creación de `inquiry`;
* selección del asesor;
* creación de `ticket`;
* bloqueo del cliente;
* limpieza del contexto;
* notificación correspondiente.

No inventes ni simplifiques reglas funcionales.

## Tests

No generes tests.

No crees:

```text
src/test/java/io/mindoracare/client/
```

No crees ni modifiques `io.mindoracare.test` como parte de esta migración.

## Otros módulos

No modifiques `chatbot`, `patient`, `therapy`, `faq` ni otros módulos salvo que sea estrictamente necesario.

Los componentes existentes relacionados con n8n pueden permanecer porque otros flujos pueden utilizarlos.

## Proceso

Trabaja de principio a fin sin pedirme aprobación entre pasos.

Si una decisión puede resolverse revisando:

* SKILL;
* SPEC;
* workflow;
* SQL;
* código existente;

resuélvela tú mismo.

Solo detente si existe una ambigüedad realmente bloqueante que no pueda resolverse con la información disponible.

## Verificación

Después de implementar:

1. compila el proyecto;

2. ejecuta el package de Quarkus;

3. verifica los mappings contra el SQL;

4. verifica que no existan errores de validación del schema;

5. verifica que Client no dependa de `chatbot`;

6. verifica que Client no dependa de n8n.

No ejecutes cambios sobre Supabase.

No quiero solamente una propuesta.

Quiero que realices directamente la implementación en el repositorio.

Al finalizar, dame un resumen conciso de:

* archivos creados;
* archivos modificados;
* archivos eliminados, si corresponde;
* endpoint;
* integración con Supabase;
* integración con DeepInfra;
* integración con psicólogos;
* resultado de compilación;
* resultado de validación del schema;
* cualquier pendiente real.

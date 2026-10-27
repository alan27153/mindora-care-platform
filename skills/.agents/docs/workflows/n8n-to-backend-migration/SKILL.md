# SKILL: Migración de workflows n8n a un backend existente

Esta skill describe cómo migrar uno o varios workflows de n8n a un backend Java que ya existe. Es genérica: sirve para cualquier workflow y cualquier backend. Todo lo específico de un proyecto (entidades, endpoints, proveedores, reglas de negocio, nombres de paquetes) procede del **SPEC del proyecto** y del **backend existente**, nunca de esta skill.

---

## 0. Propósito y alcance

**Qué hace esta skill**

- Enseña el proceso completo para llevar un comportamiento definido en n8n a código real dentro de un backend existente.
- Define el orden de trabajo, las reglas de decisión y los criterios de verificación.

**Qué no hace esta skill**

- No define requisitos de negocio. Esos vienen del SPEC.
- No define una arquitectura. La arquitectura se descubre en el backend existente.
- No contiene nombres de paquetes, clases, tablas, endpoints ni proveedores.
- No reemplaza al SPEC ni a la documentación del proyecto.

**Cuándo aplicarla**

- Hay un JSON exportado de n8n (uno o varios workflows) que debe reproducirse en código.
- Existe un SPEC que define o modifica el comportamiento a migrar.
- Existe un backend donde el código debe vivir.

---

## 1. Principios no negociables

1. **El backend existente manda sobre la forma del código.** Antes de crear nada, se inspecciona el proyecto. No se impone una arquitectura nueva.
2. **El SPEC es una fuente primaria de requisitos.** No es solo un árbitro de conflictos. Se lee primero, por sí mismo.
3. **El workflow n8n define el comportamiento que debe preservarse**, salvo que el SPEC indique explícitamente un cambio.
4. **Ningún nodo equivale automáticamente a una clase.** Se mapea la responsabilidad funcional, no el nodo.
5. **Reutilizar antes que crear.** Cada componente nuevo debe justificar por qué no puede reutilizarse ni extenderse algo existente.
6. **No inventar.** Ni tablas, ni columnas, ni endpoints, ni respuestas, ni credenciales, ni reglas, ni versiones. Lo que falte se marca como `[REQUIRES_CONFIRMATION]`.
7. **No modificar reglas de negocio por iniciativa propia.** Si algo parece erróneo, se documenta y se pregunta.
8. **No mantener transacciones abiertas durante llamadas externas lentas.**
9. **No migrar credenciales desde n8n al código.** Se mapean a la configuración del proyecto.
10. **La compilación no basta.** Hay que probar el comportamiento y compararlo con el original.

---

## 2. Fuentes de información y prioridad

Hay cuatro fuentes. Cada una tiene un papel distinto.

| Fuente | Qué representa | Qué aporta |
|---|---|---|
| **Workflow n8n** (JSON) | Comportamiento actual observado | Nodos, conexiones, ramas, expresiones, consultas, llamadas externas, prompts, respuestas, errores. |
| **SPEC** (`SPEC.md` u otro documento de requisitos) | Requisitos de la migración | Qué debe existir, qué se modifica respecto a n8n, criterios de aceptación, restricciones, NFRs. |
| **Backend existente** | Arquitectura y convenciones actuales | Estructura de paquetes, patrones, componentes reutilizables, configuración, persistencia, seguridad, tests. |
| **Documentación de arquitectura o skill del proyecto** (si existe) | Reglas de implementación del proyecto | Cómo se escribe el código en ese proyecto. |

**Prioridad cuando hay conflicto**

1. El **SPEC** prevalece sobre el comportamiento observado en n8n, en lo que el SPEC define explícitamente.
2. El **comportamiento observado en n8n** prevalece sobre suposiciones, en todo lo que el SPEC no modifica.
3. La **arquitectura y convenciones del backend** determinan dónde y cómo se implementa, pero no cambian el resultado funcional.
4. Cualquier conflicto que no se resuelva con estas reglas se documenta como contradicción (sección 12).

---

## 3. Flujo de trabajo

```text
Fase 0  Inspección del backend existente
Fase 1  Lectura y extracción de requisitos del SPEC
Fase 2  Análisis del workflow n8n
Fase 3  Reconciliación: SPEC ↔ n8n ↔ backend
Fase 4  Diseño (reutilizar / modificar / crear)
Fase 5  Implementación
Fase 6  Tests
Fase 7  Verificación (compilación, n8n, SPEC)
```

Cada fase tiene un entregable. No se pasa a la siguiente sin completar el anterior.

---

## 4. Fase 0 — Inspección del backend existente

Antes de leer el workflow o escribir código, se inspecciona el proyecto de destino.

**Checklist de inspección**

- [ ] Estructura de directorios y paquetes de la aplicación.
- [ ] Arquitectura observable: ¿hay separación por módulos? ¿hay puertos y adaptadores? ¿hay capas (controller, service, repository)? ¿hay CQRS o un modelo propio?
- [ ] Convenciones de nombres: sufijos de clases (`UseCase`, `Service`, `Repository`, `Adapter`, `Gateway`, `Client`, `Resource`, `Controller`), uso de records o clases, idioma de los nombres.
- [ ] Inyección de dependencias: anotaciones de CDI u otro framework, inyección por campo o por constructor, uso de cualificadores.
- [ ] Persistencia: ORM, repositorios, uso de sesiones o transacciones, migraciones de esquema, entidades existentes.
- [ ] Clientes externos: clientes HTTP o GraphQL existentes, bases de cliente reutilizables, timeouts configurados.
- [ ] Manejo de errores: excepciones propias, mapeadores, códigos de respuesta.
- [ ] Seguridad: autenticación, autorización, anotaciones de seguridad, claves y su ubicación.
- [ ] Configuración: archivos de propiedades, prefijos de variables de entorno, valores por defecto.
- [ ] Dependencias de build: qué capacidades ya están disponibles.
- [ ] Tests: framework, convenciones, qué ya está cubierto.
- [ ] Documentación de arquitectura o skills del proyecto. Si existen, se siguen.

**Regla:** si el proyecto ya tiene una forma de resolver algo (una integración, un manejo de error, una lectura de configuración), se usa esa forma.

**Entregable:** un resumen breve de la arquitectura observada, con ejemplos de rutas de archivo que lo demuestran.

---

## 5. Fase 1 — Lectura y extracción de requisitos del SPEC

El SPEC se lee antes que el workflow. Se extrae, como mínimo:

- objetivo y alcance;
- funcionalidades;
- endpoints o operaciones requeridos;
- casos de uso;
- entradas y salidas de cada operación;
- reglas de negocio;
- persistencia requerida (entidades, relaciones, restricciones);
- integraciones requeridas;
- dependencias y tecnologías nombradas;
- configuración definida (nombres de variables, valores por defecto);
- seguridad;
- manejo de errores;
- restricciones;
- requisitos no funcionales;
- comportamiento esperado y criterios de aceptación;
- cualquier decisión explícita sobre cómo debe cambiar un comportamiento de n8n.

**Reglas de uso del SPEC**

- Un requisito del SPEC que no aparece en n8n **se implementa**. No es una contradicción.
- Un requisito que modifica algo que hace n8n **se aplica**, y el cambio se documenta.
- Un requisito ambiguo o incompleto se marca como `[REQUIRES_CONFIRMATION]`.

**Entregable:** lista numerada de requisitos, cada uno con identificador, fuente (sección del SPEC) y tipo (funcional, persistencia, integración, configuración, seguridad, error, NFR, criterio).

---

## 6. Fase 2 — Análisis del workflow n8n

### 6.1 Identificación de workflows

Un JSON puede contener varios workflows. Antes de analizar, se identifica cada uno.

- Cada grupo de nodos conectados entre sí que nace de un trigger propio es un workflow independiente.
- Se anota qué recursos comparten (credenciales, tablas, endpoints externos, nodos de subworkflow).
- No se mezclan reglas entre workflows aunque estén en el mismo archivo.

**Entregable por workflow:** nombre, trigger, nodos que contiene, recursos compartidos.

### 6.2 Reconstrucción del grafo

Se reconstruye el flujo a partir de las conexiones, no de la posición visual de los nodos.

Para cada trigger:

```text
Trigger
 ↓
Entrada (qué datos llegan y en qué forma)
 ↓
Transformaciones
 ↓
Condiciones y ramas
 ↓
Integraciones (HTTP, GraphQL, AI, subworkflows)
 ↓
Persistencia
 ↓
Loops
 ↓
Salida (respuesta, evento, efecto)
```

Hay que anotar:

- ramas y la condición que las activa;
- caminos sin salida (un nodo que no conecta con nada, una rama que termina sin respuesta);
- puntos de unión (merge);
- loops y su condición de salida;
- nodos que se ejecutan en paralelo desde la misma salida;
- errores y su propagación.

**Regla:** si el orden de ejecución entre dos nodos hijos de la misma salida no está determinado por el JSON, se marca como `[REQUIRES_CONFIRMATION]` si puede afectar al resultado.

### 6.3 Inventario de nodos

Para cada nodo se registra:

| Campo | Contenido |
|---|---|
| Nombre | Nombre exacto en el JSON. |
| Tipo y versión | Tipo del nodo y `typeVersion`. |
| Entrada | Datos que recibe y de qué nodo anterior. |
| Salida | Forma de los datos que produce. |
| Propósito funcional | Qué hace en términos de negocio o técnicos. |
| Condiciones | Si depende de alguna condición o rama. |
| Dependencias | Nodos, credenciales, servicios o variables de los que depende. |
| Errores | Qué pasa si falla y si el flujo continúa o se detiene. |
| Decisión | Migrar al backend, mantener fuera, o `[REQUIRES_CONFIRMATION]`. |

**Regla:** el propósito funcional se determina leyendo el código o la configuración del nodo, no su nombre. Un nodo llamado "Format" puede contener validación, decisión y mapeo.

### 6.4 Expresiones de n8n

Las expresiones son parte del comportamiento. Deben analizarse, no copiarse.

Patrones a detectar:

- `{{ $json }}` y `{{ $json.campo }}`: dato de la entrada inmediata.
- `{{ $node["Nombre"].json.campo }}`: dato de un nodo concreto, no necesariamente el anterior.
- `{{ $items(...) }}`: acceso a varios elementos.
- `{{ $now }}` y funciones de fecha: valor dependiente del momento de ejecución.
- Variables de entorno y credenciales referenciadas por nombre.
- Operadores de fallback: `A || B || C` significa "el primero no vacío". Hay que determinar qué se considera vacío (nulo, cadena vacía, cero, falso).
- Operadores condicionales y comparaciones.
- Conversiones: `String()`, `trim()`, `JSON.stringify`, casts en consultas.
- Interpolaciones dentro de cadenas, incluidos prompts y consultas.

Para cada expresión importante se documenta:

- valor que obtiene;
- origen del dato;
- qué pasa cuando el valor falta;
- efecto en el comportamiento;
- cómo se reproduce en el backend (acceso a datos, valor por defecto, transformación).

**Regla:** la expresión se traduce a comportamiento equivalente, no a sintaxis. El código no contiene `$json` ni `{{ }}`.

### 6.5 Code nodes

Cada Code node se analiza por su lógica. Se identifica:

- entradas y salidas, incluida la forma de los objetos;
- validaciones;
- normalizaciones (mayúsculas, espacios, acentos, recortes);
- condiciones y reglas de negocio;
- loops internos;
- valores por defecto;
- construcción de payloads, consultas o prompts;
- parsing de respuestas (expresiones regulares, marcadores, JSON);
- dependencias implícitas del contexto de n8n (nombres de otros nodos, forma del objeto del trigger).

**Regla:** la lógica se coloca en el componente que la arquitectura del proyecto asigna a ese tipo de responsabilidad (lógica de aplicación, dominio, mapeo, adaptador). No se crea una clase por Code node.

**Regla:** si el script contiene una lista de valores (categorías, códigos, opciones), se conserva exactamente, y se documenta si esa lista debe vivir en código, en configuración o en base de datos según el SPEC.

### 6.6 Nodos de base de datos

Para cada consulta:

- tablas y columnas utilizadas;
- operación (SELECT, INSERT, UPDATE, DELETE, UPSERT);
- joins, subconsultas, CTEs y agregaciones;
- filtros, ordenamiento y límites;
- funciones o procedimientos invocados;
- parámetros y de dónde provienen;
- transacciones explícitas o implícitas;
- dependencias con nodos anteriores y posteriores.

**Reglas**

- Si una consulta usa interpolación de texto, la migración usa parámetros enlazados. El comportamiento para entradas válidas debe seguir siendo el mismo.
- Si una consulta contiene varias sentencias en un mismo nodo, se trata como una unidad, salvo que la arquitectura del proyecto exija separarla. En ese caso se preserva la atomicidad.
- Antes de escribir código, cada tabla y columna se valida contra el esquema real. Si no existe en el esquema disponible, se marca `[REQUIRES_CONFIRMATION]`. No se crean columnas ni tablas para resolverlo.
- Las ventanas de historial, los recortes y los borrados se preservan con su orden exacto.

### 6.7 Integraciones externas (HTTP, GraphQL y similares)

Para cada llamada:

- servicio y propósito;
- URL o propiedad de configuración de la que sale;
- método;
- cabeceras;
- query params y body (con sus valores fijos y dinámicos);
- autenticación;
- timeout;
- reintentos;
- respuesta esperada y qué campos se usan;
- manejo de error y fallback;
- si el resultado se usa o no;
- si la llamada es requerida (su fallo detiene la operación) u opcional (su fallo se registra y se continúa con un valor documentado).

**Regla:** la clasificación requerida/opcional se basa en lo que el workflow hace hoy ante un fallo, no en cuán importante parece la llamada.

**Regla:** los valores fijos del body se conservan, o se mueven a configuración si el SPEC lo pide. Se documenta cada valor fijo y se pregunta si es intencional cuando parezca provisional.

### 6.8 Nodos de IA y modelos de lenguaje

Los nodos de IA se analizan como integraciones externas (ver sección 6.7), más:

- proveedor, modelo e identificador tal como aparecen;
- credencial utilizada, sin copiar su valor;
- system message: explícito, por defecto del framework, o ausente;
- memoria y herramientas conectadas (si las hay);
- prompt completo, con sus variables y condiciones de inclusión;
- formato de salida esperado y cómo se parsea (etiquetas, JSON, texto libre);
- validaciones posteriores sobre la salida;
- comportamiento ante salida inválida, ausente o con etiquetas desconocidas;
- configuración de temperatura, máximo de tokens, timeout y reintentos, si existe.

**Reglas**

- El prompt se preserva como dato. No se reescribe, no se resume y no se reordena. Solo se traduce la lógica que decide qué partes se incluyen.
- El identificador de modelo y el proveedor no se deducen del nombre. Se toman de la configuración o del SPEC.
- La llamada al modelo se encapsula detrás de un puerto de salida y un adaptador, siguiendo el patrón del proyecto. Si el proyecto ya tiene infraestructura para modelos, se reutiliza.
- La clave de API nunca se hardcodea.

### 6.9 Loops

Los loops (Loop Over Items, Split in Batches, o equivalentes de la versión de n8n) se analizan explícitamente:

- qué colección itera;
- orden de procesamiento;
- si las iteraciones son independientes o dependen de la anterior;
- condición de salida y casos de parada temprana;
- cómo se acumulan los resultados;
- qué pasa si falla una iteración: se detiene, se salta o se continúa;
- qué se devuelve al final.

**Regla:** el loop se implementa con la forma de iteración que el proyecto ya usa (bucle, flujo reactivo, procesamiento por lotes). Se preserva el orden y el manejo de errores por elemento. No se crea una clase por loop.

### 6.10 Subworkflows

Un nodo que ejecuta otro workflow (Execute Workflow o equivalente) se trata así:

- el subworkflow se analiza como un workflow independiente (6.1 a 6.9);
- si varios workflows lo invocan, la lógica se implementa una sola vez, en un componente compartido;
- se documenta qué datos cruzan la frontera (entrada, salida, errores);
- si el subworkflow se ejecuta de forma asíncrona en n8n, la migración debe decidir si mantiene la asincronía (ver 9.5).

### 6.11 Credenciales, variables y configuración

Para cada referencia a una credencial o variable:

- nombre de la credencial o variable;
- tipo (API key, usuario y contraseña, token, URL);
- nodos que la usan;
- si existe un nombre definido en el SPEC o en la configuración del proyecto;
- valor por defecto, si lo tiene.

**Regla:** no se copian valores. Se documenta la existencia y el nombre. Se mapean al mecanismo de configuración del backend.

### 6.12 Manejo de errores en n8n

Se documenta, por nodo y por rama:

- si el nodo tiene configurado algún comportamiento ante error;
- si existe un camino alternativo tras un error;
- qué respuesta recibe el cliente en cada caso de fallo;
- si hay nodos que continúan aunque otro falle;
- si hay ramas que no producen respuesta.

**Regla:** las ramas sin respuesta se conservan como comportamiento observado. Se documenta explícitamente y se pregunta si debe cambiar.

### 6.13 Entregable de la fase 2

- Inventario de workflows.
- Grafo por workflow.
- Inventario de nodos.
- Análisis de expresiones, Code nodes, persistencia, integraciones, IA, loops, subworkflows, credenciales y errores.
- Lista de `[REQUIRES_CONFIRMATION]`.

---

## 7. Fase 3 — Reconciliación: SPEC ↔ n8n ↔ backend

Se comparan los requisitos (fase 1) con el comportamiento observado (fase 2) y con el backend (fase 0).

Cada requisito o comportamiento cae en una de estas categorías:

| Categoría | Acción |
|---|---|
| El SPEC lo define y n8n lo hace igual | Implementar y verificar. |
| El SPEC lo define y n8n no lo hace | Implementar (es requisito nuevo, no contradicción). |
| n8n lo hace y el SPEC no lo menciona | Preservar el comportamiento, salvo que contradiga la arquitectura. Documentar si afecta al diseño. |
| El SPEC modifica algo que n8n hace | Aplicar la decisión del SPEC. Documentar el cambio. |
| El SPEC contradice algo que n8n hace y el SPEC no lo resuelve | Marcar como contradicción (sección 12). |
| El backend ya resuelve algo | Reutilizar, sin duplicar. |
| El backend contradice el diseño necesario | Documentar y decidir según las reglas de prioridad del proyecto. |

**Alcance de la migración.** Se determina qué parte del comportamiento pasa al backend y qué parte permanece fuera.

- Si el SPEC exige que todo sea backend, se respeta.
- Si una parte permanece fuera (otro servicio, un proveedor externo, un sistema de autenticación, almacenamiento externo), se documenta:
  - responsabilidad que conserva;
  - input que recibe del backend y output que devuelve;
  - errores en cada sentido;
  - autenticación y configuración necesarias;
  - timeouts.
- No se asume que todo n8n desaparece. Puede haber coexistencia temporal entre lógica nueva y flujos existentes.

**Entregable:** tabla de reconciliación y declaración explícita del alcance.

---

## 8. Fase 4 — Diseño

Antes de modificar el código se entrega un diseño breve.

### 8.1 Tabla de decisiones

| Componente | Acción | Motivo |
|---|---|---|
| Nombre del componente existente | Reutilizar / Modificar / Extender | Por qué encaja. |
| Nombre del componente nuevo | Crear | Por qué no hay nada reutilizable. |

### 8.2 Contenido mínimo del diseño

- Archivos nuevos (ruta completa y propósito).
- Archivos modificados (ruta y cambio previsto).
- Archivos reutilizados sin cambios.
- Dependencias de build que se añaden, con su versión o `[REQUIRES_CONFIRMATION]`.
- Propiedades de configuración nuevas, con nombre y valor por defecto.
- Integraciones nuevas, con su puerto y su adaptador.
- Persistencia: qué operaciones se añaden y sobre qué esquema.
- Endpoints nuevos o modificados, con su contrato.
- Estrategia de transacciones y de llamadas externas.

### 8.3 Reglas para crear componentes

- Crear un componente nuevo solo si ninguno existente cumple la responsabilidad.
- No crear archivos para representar nodos visualmente.
- Un nodo puede no generar archivos, generar uno o generar varios.
- Varios nodos pueden convivir en una sola clase cuando su responsabilidad es la misma.
- Si una responsabilidad ya tiene un componente en el proyecto (un cliente HTTP, un manejador de errores, un repositorio), se extiende ese componente.
- No crear una capa o un servicio intermedio que solo delega, salvo que la arquitectura del proyecto lo exija.

---

## 9. Fase 5 — Implementación

### 9.1 Mapeo funcional de nodos

Estos son ejemplos. La implementación sigue la arquitectura real del proyecto.

| Nodo n8n | Responsabilidad en el backend |
|---|---|
| Trigger de webhook | Endpoint o recurso de entrada. |
| Respond to Webhook | Forma de la respuesta del endpoint. |
| Code | Lógica de aplicación, dominio, mapeo o construcción de datos, según su responsabilidad real. |
| Base de datos | Puerto de persistencia y su adaptador, usando el repositorio o acceso a datos del proyecto. |
| HTTP Request | Puerto de integración y su adaptador. |
| GraphQL | Puerto de integración y adaptador tipado, o cliente existente. |
| AI Agent / modelo | Puerto de modelo de lenguaje y su adaptador. |
| IF / Switch | Reglas de aplicación. |
| Set / Edit Fields | Transformación en DTO o mapper. |
| Merge | Orquestación en la capa de aplicación. |
| Loop | Iteración en código, con el patrón del proyecto. |
| Execute Workflow | Llamada a un componente compartido. |
| Credenciales | Configuración del backend. |

### 9.2 Reglas de arquitectura

- Si el proyecto usa casos de uso, puertos y adaptadores: la capa de aplicación depende de puertos, no de clientes concretos.
- Los controladores solo reciben, validan la forma de la entrada y devuelven la respuesta.
- La lógica no vive en controladores, adaptadores ni entidades, salvo que el proyecto lo haga así de forma consistente.
- Las dependencias se inyectan con el mecanismo del proyecto.
- Los nombres siguen las convenciones observadas en la fase 0.

### 9.3 Reactividad y transacciones

Si el backend es reactivo o usa sesiones y transacciones:

- se inspecciona cómo el proyecto las gestiona antes de escribir código;
- se respetan los contextos y límites que el proyecto usa;
- las lecturas, el procesamiento externo y las escrituras se separan cuando hay una llamada externa lenta de por medio:

```text
lectura
 ↓
procesamiento o integración externa (sin transacción abierta)
 ↓
escritura
```

- no se mantiene una transacción abierta durante una llamada a modelo de lenguaje, HTTP, GraphQL o almacenamiento remoto;
- las operaciones que son solo de lectura no abren transacciones de escritura.

### 9.4 Integraciones

- Cada integración tiene timeout finito, configurado según el SPEC o según el valor que el workflow usa.
- Los errores de la integración se mapean al mecanismo de errores del proyecto.
- Las cabeceras de autenticación se construyen en el adaptador, no en la capa de aplicación.
- Se mantiene el nombre exacto de operaciones y campos que exige el servicio remoto, incluidos los que parezcan redundantes.
- Los fallos requeridos se propagan. Los opcionales se registran y se continúa con el valor documentado.

### 9.5 Asincronía

Si n8n ejecuta algo de forma asíncrona (respuesta antes de terminar un efecto secundario):

- la migración mantiene el mismo orden observable;
- si la respuesta se envía antes del efecto, el efecto no puede afectar a la respuesta ya enviada;
- se decide explícitamente si la asincronía se mantiene. Si no hay requisito, se documenta como `[REQUIRES_CONFIRMATION]`.

### 9.6 Configuración

- Cada valor configurable se añade al sistema de configuración del proyecto, con el mismo patrón de nombres.
- Los valores secretos se leen de variables de entorno o del mecanismo de secretos del proyecto. No tienen valor por defecto real.
- Si el SPEC define nombres de variables, se usan esos nombres.
- Si una variable necesaria no tiene valor, se crea la propiedad y se documenta. No se detiene la implementación por eso, salvo que la falta impida conocer el comportamiento correcto.

### 9.7 Dependencias

1. Revisar el build del proyecto antes de añadir nada.
2. Identificar qué capacidades ya están cubiertas (HTTP, JSON, persistencia, validación, testing, modelos de lenguaje).
3. Reutilizar las dependencias existentes.
4. Añadir solo las necesarias para la migración.
5. Versiones: usar la versión que ya utiliza el proyecto para esa biblioteca. Si no existe, usar una versión concreta y compatible con el resto del build. No usar `latest` ni rangos abiertos.
6. Si la versión no puede determinarse con seguridad: `[REQUIRES_CONFIRMATION]`.
7. No realizar actualizaciones globales de dependencias como parte de la migración.

### 9.8 Errores

- Se usan las excepciones y el mapeador de errores existentes en el proyecto.
- No se crea un sistema de excepciones paralelo.
- Los errores de validación, de datos inexistentes, de integración, de persistencia y de IA se distinguen cuando el comportamiento de n8n los distingue.
- Los errores que n8n devuelve como respuesta de texto (con código 200, por ejemplo) se documentan. La migración debe mantener ese comportamiento salvo que el SPEC lo cambie.

### 9.9 Prohibiciones de implementación

- No generar pseudocódigo como entregable.
- No generar implementaciones ficticias ni stubs que simulen comportamiento.
- No dejar métodos importantes con `TODO`.
- No copiar expresiones de n8n al código.
- No hardcodear URLs, claves, nombres de modelos ni credenciales.
- No crear tablas ni columnas que no existen en el esquema validado.
- No crear endpoints que no estén en el SPEC o en el workflow.

---

## 10. Fase 6 — Tests

Los tests se crean o actualizan según las convenciones de tests del proyecto.

**Cubrir como mínimo**

- Caso principal de cada endpoint o trigger.
- Cada rama de decisión del workflow, incluidas las que no producen respuesta.
- Validaciones de entrada.
- Cada error que el workflow maneja, y el comportamiento observable ante cada uno.
- Reglas de negocio con ejemplos concretos de entrada y salida.
- Persistencia: el efecto real sobre los datos (filas creadas, actualizadas o borradas, con sus valores), no solo que la llamada no falle.
- Integraciones: comportamiento con respuesta correcta, con error requerido y con error opcional, usando un doble del puerto.
- Procesamiento de la salida del modelo de lenguaje: entrada conocida, salida esperada. No se prueba la calidad del modelo.
- Loops: número de iteraciones, orden y comportamiento ante fallo de un elemento.
- Configuración: que las propiedades se lean y que los valores por defecto sean los esperados.

**Reglas**

- Los tests comprueban comportamiento, no líneas cubiertas.
- Los tests de persistencia consultan el estado final.
- Los tests de integración no dependen de servicios reales. Si el SPEC exige una prueba contra un servicio real, se documenta y se ejecuta por separado.
- Los nombres de los tests describen el comportamiento.

---

## 11. Fase 7 — Verificación

### 11.1 Verificación técnica

Ejecutar en este orden y corregir antes de continuar:

1. revisar imports sin usar y faltantes;
2. revisar paquetes y ubicación de archivos según la arquitectura del proyecto;
3. revisar inyección de dependencias y cualificadores;
4. revisar dependencias de build;
5. revisar configuración y nombres de propiedades;
6. revisar consultas y su correspondencia con el esquema;
7. revisar integraciones y su configuración;
8. compilar;
9. ejecutar tests;
10. repetir desde el paso 1 si se han hecho cambios.

La compilación por sí sola no es verificación.

### 11.2 Verificación contra n8n

Comparar el backend final con el workflow original, nodo por nodo y rama por rama:

- cada nodo relevante tiene su equivalente o una justificación de por qué no lo tiene;
- cada conexión y cada condición se reproduce;
- las expresiones producen el mismo valor para las mismas entradas;
- los loops producen el mismo resultado;
- las transformaciones dan la misma salida;
- las consultas producen el mismo efecto;
- las llamadas externas se hacen con el mismo método, el mismo cuerpo y la misma interpretación de la respuesta;
- las respuestas tienen la misma forma y los mismos códigos;
- los errores se manejan igual, salvo cambio explícito del SPEC.

**Método recomendado:** ejecutar los mismos datos de entrada en n8n y en el backend, y comparar la salida y los efectos sobre los datos. Si no es posible ejecutar n8n, se documenta y se compara por inspección.

### 11.3 Verificación contra SPEC

Comprobar, requisito por requisito:

- objetivos y funcionalidades;
- endpoints y operaciones;
- casos de uso;
- reglas de negocio;
- persistencia;
- integraciones;
- configuración;
- seguridad;
- errores;
- restricciones;
- requisitos no funcionales;
- criterios de aceptación.

No se considera completa la migración si falta un requisito del SPEC.

---

## 12. Contradicciones y decisiones

Cuando hay diferencias entre n8n, el SPEC, el backend existente o la arquitectura:

1. **Detectar** la contradicción y citar los dos lados (fuente y ubicación).
2. **Identificar** qué requisitos están en conflicto.
3. **Aplicar** la prioridad definida en la sección 2 y, si el proyecto define otra prioridad, esa.
4. **No ignorar** el conflicto en silencio.
5. **Documentar** la decisión en el informe final, con el motivo.
6. **Nunca** modificar una regla de negocio por iniciativa propia. Si la decisión no está clara, se pregunta.

**Formato de registro**

| Id | Fuente A | Fuente B | Conflicto | Decisión aplicada | Motivo | Confirmada |
|---|---|---|---|---|---|---|

---

## 13. Entregables finales

Al terminar, la migración entrega:

1. **Resumen de la arquitectura observada** del backend (fase 0).
2. **Lista de requisitos del SPEC** con identificadores (fase 1).
3. **Inventario de workflows y nodos**, grafo y análisis (fase 2).
4. **Tabla de reconciliación y alcance** (fase 3).
5. **Diseño**: tabla de decisiones y lista de archivos nuevos, modificados y reutilizados (fase 4).
6. **Código implementado**, en las rutas del proyecto.
7. **Tests creados o actualizados**, con su resultado de ejecución.
8. **Resultado de compilación**.
9. **Verificación contra n8n y contra SPEC**, con lo que se comprobó y lo que no se pudo comprobar.
10. **Registro de contradicciones y decisiones.**
11. **Lista de `[REQUIRES_CONFIRMATION]`** pendientes.
12. **Configuración nueva**, con nombres de variables y valores por defecto.

Si algún entregable no puede completarse, se indica cuál y por qué.

---

## 14. Lista de comprobación final

- [ ] El backend existente se inspeccionó antes de implementar.
- [ ] El SPEC se leyó como fuente primaria de requisitos.
- [ ] Cada workflow del JSON se identificó y se analizó por separado.
- [ ] Cada nodo tiene propósito, entrada, salida, condiciones, errores y decisión.
- [ ] Las expresiones se analizaron y ninguna se copió literalmente.
- [ ] Los Code nodes se analizaron por su lógica, no por su nombre.
- [ ] Las consultas se validaron contra el esquema real.
- [ ] Las llamadas externas tienen timeout, clasificación requerido/opcional y manejo de error.
- [ ] La llamada al modelo de lenguaje está detrás de un puerto y no tiene credenciales hardcodeadas.
- [ ] Los loops preservan orden, iteraciones y manejo de errores.
- [ ] Los subworkflows se analizaron y no se duplicó su lógica.
- [ ] No hay transacciones abiertas durante llamadas externas lentas.
- [ ] Se reutilizaron componentes existentes siempre que fue posible.
- [ ] No se crearon archivos para nodos individuales.
- [ ] Las dependencias revisadas no incluyen versiones `latest`.
- [ ] Las credenciales se mapearon a configuración y no se copiaron valores.
- [ ] Los errores usan el mecanismo del proyecto.
- [ ] Los tests comprueban comportamiento.
- [ ] La compilación y los tests se ejecutaron.
- [ ] Se verificó la migración contra n8n y contra el SPEC.
- [ ] Las contradicciones están documentadas.
- [ ] Los `[REQUIRES_CONFIRMATION]` están listados.

---

## 15. Anti-patrones

- Crear una clase por cada nodo.
- Copiar expresiones de n8n al código.
- Crear una arquitectura nueva cuando el backend ya tiene una.
- Duplicar un cliente, un manejador de errores o una entidad que ya existe.
- Mantener una transacción abierta mientras se espera a un modelo de lenguaje o a una API externa.
- Inventar columnas, tablas, endpoints, respuestas o credenciales para que el código compile.
- Resolver una duda cambiando una regla de negocio en silencio.
- Hardcodear URLs, claves o nombres de modelos.
- Considerar terminada la migración porque el código compila.
- Mezclar las reglas de dos workflows distintos en el mismo componente sin justificación.
- Dejar ramas sin respuesta sin documentarlas.
- Introducir dependencias o versiones sin revisar el build.

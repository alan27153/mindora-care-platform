# 1. Name

**09-N8N-Backend-Migration-Skill**

---

# 2. Description

Esta Skill analiza de forma **exhaustiva y determinista** cualquier workflow exportado desde n8n en formato JSON (webhooks, triggers, nodos custom, HTTP Request, bases de datos, nodos Code, condicionales, y el ecosistema `@n8n/n8n-nodes-langchain`) y produce **un único documento Markdown técnico, completo y autocontenido** (denominado `spec.md`). 

El objetivo de esta Skill es traducir la lógica visual y de datos del JSON a especificaciones funcionales y de arquitectura de software, de modo que **otra IA o Skill posterior de desarrollo** pueda diseñar o implementar un backend Java Quarkus **sin volver a abrir ni interpretar el JSON original**.

Debes activar esta Skill siempre que el usuario:
- Adjunte o referencie un archivo `.json` exportado de n8n.
- Pida "analizar", "documentar", "migrar", "convertir" o "extraer lógica" de un workflow de n8n.
- Pida preparar la base o especificación técnica (`spec.md`) para migrar un flujo de n8n a un backend microservicio/API.
- Mencione "n8n", "workflow", "nodo", "WAHA", "webhook" en el contexto de una migración o especificación técnica.

**LÍMITE ESTRICTO DE RESPONSABILIDAD:**
Esta Skill **es exclusivamente de análisis, extracción de lógica y diseño arquitectónico abstracto**. 
- **NUNCA escribe código fuente ejecutable.**
- **NUNCA genera clases Java, Kotlin, Python, SQL, DTOs, Interfaces, Annotations ni YAML.**
Esa responsabilidad pertenece a Skills posteriores en la cadena de desarrollo. El único entregable es el documento Markdown de especificación técnica descrito en la Sección 5.

---

# 3. General Rules

1. **Prohibición Absoluta de Generación de Código:** Bajo ninguna circunstancia la Skill debe producir código fuente (Java, Kotlin, SQL DTOs, `interface`, `class`, `@Path`, `@Inject`, Dockerfiles, etc.). Incluso si el usuario lo pide en el mismo turno, la Skill debe emitir el análisis en Markdown y aclarar explícitamente que la generación de código corresponde a la siguiente etapa.
2. **Un Único Entregable de Especificación:** El resultado final de esta Skill es **un solo documento Markdown estructurado** (ver Sección 5). No se dividen las secciones en archivos separados a menos que el flujo sea tan masivo que exceda el límite de contexto del modelo, en cuyo caso la IA debe notificarlo y entregar el Markdown por entregas secuenciales sin perder la estructura.
3. **Cobertura Total Obligatoria (Sin Omitir Nodos):** El 100% de los nodos presentes en el JSON deben ser catalogados. Ningún nodo puede descartarse silenciosamente. Si un nodo está deshabilitado (`"disabled": true`), debe documentarse igualmente y clasificarse.
4. **Clasificación Unívoca por Nodo:** Cada nodo debe recibir **exactamente una** de las 9 categorías fijas (Sección 5.3). Si un nodo cumple roles mixtos, se asigna la categoría según su función principal y la ambigüedad se detalla en su ficha individual.
5. **Decisión de Migración Fundamentada:** Cada nodo debe recibir explícitamente una decisión: `PERMANECE EN N8N` o `MIGRA AL BACKEND`, acompañada siempre de una justificación técnica basada en la matriz de decisión (Sección 5.8).
6. **Cero Tolerancia a las Alucinaciones / Datos Inventados:** Si un parámetro, credencial, URL, secreto o expresión no está presente o es inaccesible dentro del JSON, debe declararse como `No determinable desde el JSON`. Nunca asumas cadenas de conexión, API Keys o estructuras de tablas que no aparezcan escritas o referenciadas implícitamente en el archivo.
7. **Trazabilidad Total con el JSON:** Cada dato técnico documentado debe corresponder a un campo fuente (`id`, `name`, `type`, `parameters`, `credentials`, `connections`).
8. **Neutralidad del Dominio de Negocio:** La metodología de análisis debe funcionar exactamente igual sin importar el ecosistema del workflow (e-commerce, agentes IA, WhatsApp, ERP, FinTech). Los nombres de servicios como WAHA o Telegram deben tratarse como integraciones de origen, no como una regla predeterminada de la Skill.
9. **Compatibilidad Universal con Nodos n8n:** La Skill debe tolerar diferencias de versión (`typeVersion`), nodos deprecados y nodos de la comunidad (`n8n-nodes-*`). Si el tipo de nodo es desconocido, se analiza bajo la estructura genérica de sus `parameters`.
10. **Traducción Pedagógica de Expresiones `{{ }}`:** Cada expresión o fórmula dinámica de n8n debe ser explicada en lenguaje natural (ej. *"Toma el ID del cliente del nodo X, si no existe usa el número de teléfono"*), además de transcribir la fórmula exacta.
11. **Tratamiento Seguro de Credenciales:** Enumera las credenciales por su **nombre de referencia y tipo** (`credentials.<tipo>.name`). Nunca inventes o intentes exponer tokens o claves reales.
12. **Autocontención para Consumo de otras IAs:** Redacta el documento asumiendo que el lector (otra IA generadora de código Java) no tiene acceso al JSON de n8n. Toda la información operativa debe quedar volcada en el Markdown.
13. **Formato Markdown Técnico:** Emplea estrictamente encabezados `#`/`##`/`###`, tablas Markdown para inventarios y comparaciones, bloques `mermaid` para el diagrama de flujo y bloques `json` solo para citar fragmentos puntuales de configuración.
14. **Inviolabilidad de las 25 Secciones:** Las 25 secciones obligatorias de la Sección 5.10 deben existir siempre en el orden exacto. Si una sección no aplica (ej. el flujo no tiene IA), debe redactarse: `No se detectaron componentes de este tipo en el workflow analizado.`
15. **Idioma:** Redacción técnica en español. Los encabezados principales `#` de la Sección 5.10 **deben mantenerse en inglés** exactamente como están definidos, ya que funcionan como anclas de búsqueda (*anchors*) para la lectura automatizada de Skills posteriores.

---

# 4. Project Structure
09-N8N-Backend-Migration-Skill/
├── SKILL.md                                 # Especificación de la Skill (Name, Description, Rules, Sections)
├── references/
│   ├── node-type-catalog.md                 # Catálogo de tipos de nodo n8n y su categoría por defecto
│   ├── langchain-nodes-guide.md             # Guía de nodos @n8n/n8n-nodes-langchain.* (Agent, Memory, etc.)
│   ├── expression-syntax-guide.md           # Reglas de interpretación de expresiones {{ $json }},$node, etc.
│   ├── migration-decision-matrix.md         # Criterios para decidir PERMANECE EN N8N vs MIGRA AL BACKEND
│   └── output-template.md                   # Plantilla base con las 25 secciones obligatorias
├── scripts/
│   └── extract_workflow_structure.py        # Script auxiliar Python para pre-extraer nodos y conexiones
└── assets/
└── example-output.md                    # Ejemplo de spec.md completo generado correctamente


---

# 5. Component Sections

La ejecución se realiza como un pipeline de 10 componentes conceptuales en secuencia, culminando en la generación de las 25 secciones obligatorias del documento.

## 5.1 Componente 1 — Ingesta y Parsing del Workflow
* Cargar y analizar el archivo JSON de n8n.
* Extraer el inventario de nodos con sus identificadores (`id`, `name`, `type`, `typeVersion`, `disabled`).
* Mapear la matriz de conexiones (`connections`) identificando entradas, salidas y canales (`main`, `ai_languageModel`, `ai_memory`, etc.).

## 5.2 Componente 2 — Reconstrucción del Flujo de Ejecución
* Mapear la ruta crítica desde el nodo Trigger hasta cada nodo terminal.
* Identificar ramas condicionales (IF/Switch), bucles (loops) y nodos aislados.
* Generar un diagrama sintáctico `mermaid` (`flowchart TD`) y una narrativa sintética del flujo.

## 5.3 Componente 3 — Clasificación de Nodos
Asignar a cada nodo **exactamente una** categoría:

| Categoría | Criterio de Selección |
|---|---|
| **Trigger** | Puntos de entrada al workflow (Webhooks, Cron, Eventos). |
| **Business Logic** | Reglas de negocio, condicionales sobre lógica de dominio, cálculos o transformaciones críticas. |
| **Integration** | Conectores HTTP genéricos o llamadas a APIs externas heterogéneas. |
| **External Service** | Proveedores de servicios dedicados (ej. WAHA/WhatsApp, Slack, Stripe). |
| **Persistence** | Operaciones CRUD sobre bases de datos o almacenamiento persistente (Postgres, Redis, Sheets). |
| **AI Component** | Nodos del ecosistema LangChain/IA (Agents, LLMs, Vector Stores, Embeddings, Memory, Tools). |
| **Infrastructure** | Control de flujo técnico (Merge, Wait, NoOp, Error Trigger, Split in Batches). |
| **Utility** | Formateo técnico puntual, mapeo simple de variables (`Set` simple, Date/Time). |
| **Configuration** | Definición de variables globales, entorno o notas de parámetros. |

## 5.4 Componente 4 — Ficha Individual por Nodo
Ficha detallada para cada nodo individual dentro de `# Node Details`:
* Nombre, ID, Tipo, Versión, Estado (`disabled`).
* Categoría asignada y Propósito Funcional (en términos de negocio).
* Configuración de parámetros clave, credenciales asociadas, entradas/salidas y expresiones utilizadas.
* Decisión individual de migración (`PERMANECE EN N8N` / `MIGRA AL BACKEND`) y su razón.

## 5.5 Componente 5 — Análisis de Variables y Expresiones
* Identificar todas las expresiones dinámicas `{{ ... }}`.
* Mapear el origen de los datos (`$json`, `$node["X"]`, `$input`, `$now`).
* Documentar el significado de negocio de cada variable y transformar la sintaxis de n8n en reglas legibles.

## 5.6 Componente 6 — Servicios Externos, APIs e Integraciones
* Agrupar y catalogar todos los puntos de contacto con sistemas externos (endpoints, métodos HTTP, payload requerido, webhooks expuestos por el workflow).

## 5.7 Componente 7 — Componentes de IA y Persistencia
* Si hay nodos de IA: Descomponer la arquitectura (Agente, Modelo, Memoria, Herramientas, Prompts).
* Si hay nodos de Base de Datos: Identificar tablas, operaciones (SELECT, INSERT, UPDATE), filtros SQL y modelos de datos afectados.

## 5.8 Componente 8 — Motor de Decisión de Migración
Aplicación de reglas para calificar cada nodo:
* **Permanecer en N8N:** Triggers de canal, conectores complejos no críticos, orquestación puramente visual, baja variabilidad de código.
* **Migrar al Backend:** Lógica de negocio/validaciones, transacciones de BD, orquestación de agentes/prompts críticos, procesamiento de código complejo, operaciones que requieren tests unitarios.

## 5.9 Componente 9 — Derivación de Componentes Backend (Abstracto)
* Identificar conceptualmente Casos de Uso (*Use Cases*), Puertos (*Ports*), Adaptadores (*Adapters*) y Servicios de Dominio/Configuración requeridos para reemplazar la lógica de los nodos marcados para migración.
* **REGLA DE ORO:** Describe responsabilidades y propósitos. **NO redactes código, ni firmas de métodos Java, ni interfaces explicitas.**

## 5.10 Componente 10 — Ensamblado Final y Validación

Generar el Markdown garantizando la inclusión de las **25 secciones exactas** en el orden especificado:

```markdown
# Workflow Summary
# Workflow Overview
# Execution Flow
# Node Inventory
# Node Details
# Variables
# Expressions
# External Services
# AI Services
# Database Usage
# APIs
# Integrations
# Business Logic
# Infrastructure Logic
# Migration Decisions
# Backend Candidate Components
# N8N Remaining Components
# Recommended Use Cases
# Recommended Ports
# Recommended Adapters
# Recommended Domain Services
# Recommended Infrastructure Services
# Recommended Configuration
# Migration Checklist
# Final Recommendations
6. Checklist de Control de Calidad (Previo a Entregar)
Antes de responder al usuario, la Skill debe verificar interiormente:

[ ] ¿Están presentes los 25 encabezados exactos en inglés?

[ ] ¿El inventario incluye el 100% de los nodos expuestos en el JSON?

[ ] ¿Se omitió completamente la generación de código Java, interfaces, DTOs o SQL?

[ ] ¿Todas las decisiones de migración incluyen su justificación técnica?

[ ] ¿Las expresiones {{ }} están explicadas en lenguaje natural?


---

### ¿Por qué esta versión solucionará el problema?

1. **Definición de Límites más Firme:** He reforzado el rol exclusivo de *Análisis y Especificación*. La IA entenderá que redactar interfaces o firmas de métodos Java es una **violación explícita de la regla 1**.
2. **Claridad en los Componentes de Arquitectura:** En el Componente 9 se añade la **"Regla de Oro"**, prohibiendo explícitamente generar código Java/Quarkus en las secciones de `# Recommended Ports` o `# Recommended Adapters` y exigiendo únicamente descripciones conceptuales.
3. **Estructura Optimizada:** La especificación de las 25 secciones se mantiene idéntica para no romper la compatibilidad con tus Skills posteriores, pero elimina redundancias internas en la ejecución del modelo.
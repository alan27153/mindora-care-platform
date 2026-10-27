
# 1. NAME

API Generation Specification

# 2. DESCRIPTION

This Specification defines which API capabilities must be generated for a project.

It acts as the execution contract consumed by the API Generation Skill.

This Specification is responsible for:

Defining generation variables.

Defining project locations.

Defining documentation sources.

Defining generation mode.

Defining endpoint selection.

Defining Skill bindings.

Defining workspace targets.

Defining generation flow.

This Specification does NOT define:

API architecture.

Hexagonal Architecture.

GraphQL implementation rules.

REST implementation rules.

DTO conventions.

Resolver implementation.

Controller implementation.

Repository implementation.

Storage implementation.

Validation rules.

Security rules.

Bruno generation format.

Those responsibilities belong exclusively to the API Generation Skill.

# 3. VARIABLES


## Project Variables

ProjectName = GSCT Contracts

BasePackage = io.joyit

Module = company


## Generation Variables

ModuleGenerationMode = INCREMENTAL


Allowed values:

BOOTSTRAP

INCREMENTAL


BOOTSTRAP:

Used when the module does not exist.

The Skill must create:

- Module structure.
- Required application structure.
- Required infrastructure structure.
- Initial endpoint implementation.
- Required adapters.


INCREMENTAL:

Used when the module already exists.

The Skill must:

- Reuse existing files.
- Extend existing implementations.
- Generate only missing endpoints.
- Avoid recreating module structure.



## Endpoint Variables


EndpointNames =

ListCompanies

GetCompanyById


The values inside EndpointNames MUST match exactly the value stored in:


endpoints.csv

Column:

Método


The Skill MUST NOT transform endpoint names.


Endpoint definitions may represent:

GRAPHQL endpoints.

REST endpoints.


The Skill determines the generation strategy using:


Type

column from endpoints.csv.



## Workspace Variables


BackendDir = gsct-cn-backend-contract

BrunoDir = gsct-cn-bruno

DatabaseDir = gsct-db-database-contract/flyway

AgentsDir = gstc-cn-skills/.agents



## Documentation Variables


DatabaseSchemaFile =

gstc-cn-skills/.agents/database/schema.sql


EndpointDocumentationFile =

gstc-cn-skills/.agents/docs/endpoints.csv


StorageDocumentationFile =

gstc-cn-skills/.agents/docs/buckets-path.csv



## Skill Variables


ApiGenerationSkill =

gstc-cn-skills/.agents/skills/02-Api-Generation-Skill/SKILL.md

# 4. SKILL REGISTRY & BINDING


Skill Registry


Registered Skills


API Generation Skill



Skill Location


API Generation Skill

↓

gstc-cn-skills/.agents/skills/02-Api-Generation-Skill/SKILL.md



Skill Execution Binding


The Specification MUST execute the API Generation Skill using the following process.


Step 1

Load:

API Generation Skill


Step 2

Load documentation sources:


EndpointDocumentationFile

DatabaseSchemaFile

StorageDocumentationFile


Step 3

For every endpoint defined inside:


EndpointNames


Search the endpoint inside:

EndpointDocumentationFile


using:


Column:

Método


The value stored in:

Método


is the unique endpoint identifier.


Step 4

Extract the complete endpoint definition.


Step 5

Determine endpoint technology:


Type = GRAPHQL

or

Type = REST


Step 6

Apply the corresponding generation rules defined by the API Generation Skill.


Step 7

Resolve generation mode.


When:

ModuleGenerationMode = BOOTSTRAP


The Skill generates:

- Module structure.
- First endpoint.
- Required adapters.
- Bruno request.


When:

ModuleGenerationMode = INCREMENTAL


The Skill generates only missing endpoints.

# 5. WORKSPACE STRUCTURE


workspace


├── gsct-cn-backend-contract

│
│   └── src

│       └── main

│           └── java

│               └── io

│                   └── joyit

│                       ├── platform

│                       │
│                       └── <Module>


├── gsct-cn-bruno


├── gsct-db-database-contract

│   └── flyway


└── gstc-cn-skills

    └── .agents

        ├── database

        ├── docs

        ├── prompts

        ├── skills

        └── specifications



API Generation Skill Target


The API Generation Skill owns:


<BackendDir>

↓

src/main/java

↓

<BasePackage>/<Module>


The Skill MUST NOT generate Flyway migrations.


Database changes are managed exclusively by the Database Skill.


The Skill may update:

Bruno requests.

Module source code.

Required platform contracts explicitly required by the Skill.

---

# 6. DOCUMENTATION SOURCES

The Specification defines two documentation sources.

endpoints.csv

This document is the primary source of truth for endpoint generation.

Every endpoint requested by the Specification MUST exist inside this document.

Column Definitions
Column	Purpose
Environment	Target deployment environment.
Namespace	System namespace.
Application	Application owning the endpoint.
Descripción	Functional description of the endpoint.
User	Consumer of the endpoint.
Feature	Business module. This value defines the backend module to which the endpoint belongs.
Acción	Business action performed.
Método	Unique endpoint name. Used to locate the endpoint definition.
Filtro	Filters accepted by the endpoint.
Parámetros de entrada	Request parameters or payload definition.
Type	Endpoint technology. Possible values: GRAPHQL or REST.
SubType	Operation type. GRAPHQL → QUERY/MUTATION. REST → GET/POST/PUT/DELETE/PATCH.
Rol validación	Required role.
UserID validación	User validation strategy.
notification email	Email notification behavior.
notification app	In-app notification behavior.
Querie	Bruno request that must be generated for endpoint testing.
buckets-path.csv

This document defines every storage resource managed by the system.

It is used only when an endpoint interacts with MinIO or storage resources.

Column Definitions
Column	Purpose
Como se Gestiona	Resource management strategy.
Recurso	Logical resource name.
Bucket	MinIO bucket.
Folder 1	First folder level.
Folder 2	Second folder level.
Folder 3	Third folder level.
Ficheros/Folders	Stored files or folders.
Estado	Resource status.
Modulo	Backend module owning the resource.

---

# 7. OUTPUT ARTIFACTS


The API Generation Skill may generate or update:


Backend:


Application Layer.

Domain Layer.

Infrastructure Layer.

GraphQL Queries.

GraphQL Mutations.

REST Controllers.

REST DTOs.

Storage Adapters.

Persistence Adapters.

Repository Contracts.

Bruno Requests.


The Skill MUST NOT generate:


Flyway migration files.


Database schema changes are managed externally by the Database Skill.

# 8. GENERATION FLOW


Phase 1

Load Variables and API Generation Skill


↓


Phase 2

Load Documentation Sources


↓


Phase 3

Resolve Endpoint Definitions and Generation Mode


↓


Phase 4

Generate or Extend Module Structure


↓


Phase 5

Generate Required API Components

(GraphQL / REST / Storage / Persistence)


↓


Phase 6

Generate Bruno Requests and Validate Compilation

---
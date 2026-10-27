# 1. Name

Backend API Skill

---

# 2. Description

This Skill defines the standards and rules required to generate backend API capabilities using GraphQL and REST technologies under Hexagonal Architecture.

This Skill establishes:

- GraphQL endpoint generation rules.
- REST endpoint generation rules.
- API adapter conventions.
- DTO generation standards.
- Resolver and controller rules.
- Application layer interaction rules.
- Persistence adapter integration rules.
- External storage integration rules.
- API client generation rules.
- Endpoint evolution rules.
- Validation and generation safety rules.

This Skill defines how API endpoints are exposed and connected with the application layer.

This Skill supports:

- GraphQL endpoints for business operations.
- REST endpoints for HTTP-based operations.
- File management operations.
- External storage communication through infrastructure adapters.

This Skill does not define:

- Business requirements.
- Specific endpoints.
- Business rules.
- Database schemas.
- User roles.
- Authentication policies.
- Domain models.
- Project-specific workflows.

Those responsibilities belong to Specifications and other Skills.

---

# 3. General Rules


## Architecture Principle

Generated APIs must follow Hexagonal Architecture.

The dependency direction is:

Infrastructure

        ↓

Application

        ↓

Domain


Allowed dependencies:

Infrastructure → Application

Infrastructure → Domain

Application → Domain

Forbidden dependencies:

Domain → Infrastructure

Domain → Application

Application → Infrastructure

Application → API Frameworks

Application → Persistence Frameworks
API Technology Rules

This Skill supports two API technologies:

GRAPHQL

REST

The endpoint technology is defined externally by the Specification.

The Skill MUST NOT decide endpoint technology.

The Skill MUST apply:

GraphQL rules when Type = GRAPHQL.
REST rules when Type = REST.
Framework Standards

Generated backend code must use:

Quarkus 3+

Java 21

Jakarta EE

SmallRye GraphQL

RESTEasy Reactive

Allowed imports:

jakarta.inject.*
jakarta.enterprise.*
jakarta.transaction.*
jakarta.persistence.*
jakarta.validation.*
jakarta.ws.rs.*

Forbidden imports:

javax.inject.*
javax.enterprise.*
javax.transaction.*
javax.persistence.*
javax.validation.*
javax.ws.rs.*
Domain Isolation

Domain and Application layers must remain independent from:

GraphQL annotations.
REST annotations.
Persistence annotations.
Storage SDKs.
HTTP frameworks.

Forbidden examples:

@GraphQLApi

@Path

@Entity

MinioClient

inside:

domain

application
Endpoint Generation Rules

Every endpoint must contain:

API Adapter

Application Entry Point

Required DTOs

Required Mappers

API Client Request

The API layer must remain thin.

API adapters MUST NOT:

Contain business logic.
Access repositories directly.
Access databases directly.
Access external services directly.
Incremental Evolution Rules

When extending an existing module:

The Skill MUST:

Reuse existing components.
Modify existing implementations.
Preserve current architecture.
Avoid duplicate classes.

Forbidden:

CompanyServiceV2

CompanyResolverNew

CompanyControllerUpdated
Validation Rules

Before generating code, the Skill MUST verify:

Required interfaces exist.
Required classes exist.
Required methods exist.
Required constructors exist.
Required CDI beans exist.
Required adapters exist.

If a required dependency cannot be verified:

Generation MUST stop.

The Skill MUST NOT create:

Fake implementations.
Missing providers.
Assumed dependencies.
Naming Rules

Generated components must follow:

<CreateAction><Entity>


Examples:

CreateCompany

UploadDocument

DownloadFile

Generated classes:

<NameEndpoint>UseCase

<NameEndpoint>Service

<NameEndpoint>Command

<NameEndpoint>Input

<NameEndpoint>Response

Examples:

UploadFileUseCase

UploadFileService

UploadFileCommand

---

## GraphQL Schema Initialization Rule

When BOOTSTRAP generates the first GraphQL endpoint:

If the generated endpoint type is Mutation
and no Query operation exists:

The Skill MUST generate a minimal Query API
to create a valid GraphQL schema.

The generated Query MUST:

- Not contain business logic.
- Not access repositories.
- Not access persistence.
- Only guarantee schema validity.

Once real Query endpoints are generated,
the temporary Query implementation may be replaced.

---

# 4. Project Structure


The Backend API Skill generates modules following this structure:


<BackendDir>

└── src

    └── main

        ├── java

        │   └── <BasePackage>

        │       ├── platform

        │       │
        │       └── <Module>

        │           ├── application

        │           │   ├── command
        │           │   ├── service
        │           │   └── usecase
        │           │
        │           ├── domain

        │           │   ├── model
        │           │   ├── valueobject
        │           │   └── port
        │           │
        │           └── infrastructure

        │               ├── graphql

        │               │   ├── dto
        │               │   │   ├── input
        │               │   │   └── response
        │               │   │
        │               │   ├── mapper
        │               │   ├── query
        │               │   └── mutation
        │               │
        │               ├── rest

        │               │   ├── dto
        │               │   ├── controller
        │               │   └── mapper
        │               │
        │               ├── persistence

        │               │   ├── entity
        │               │   ├── mapper
        │               │   └── repository
        │               │
        │               └── storage

        │                   ├── adapter
        │                   └── configuration

        │
        └── resources

            ├── application.properties

            └── security


API Client structure:


<BrunoDir>

└── <Module>

    └── <Endpoint>.bru


Storage structure:


<BackendDir>

└── <Module>

    └── infrastructure

        └── storage

            ├── adapter

            └── configuration

---

# 5. Application


Location


<BackendDir>/src/main/java/<BasePackage>/<Module>/application

Ownership

This section is the only source of truth for:

Application layer responsibilities.
Use case organization.
Application commands.
Application services.
Application dependencies.
Application restrictions.

Purpose

The application layer defines the entry points for executing business operations.

It coordinates domain behavior and external interactions through defined ports.

Responsibilities

The application layer is responsible for:

Executing application workflows.
Coordinating domain operations.
Receiving commands from external adapters.
Returning application results.
Calling domain objects.
Using domain ports when external communication is required.

Internal Structure

usecase

Contains application contracts.

Example:

CreateCompanyUseCase
UploadFileUseCase
service

Contains application service implementations.

Example:

CreateCompanyService
UploadFileService
command

Contains input objects used by application operations.

Example:

CreateCompanyCommand
UploadFileCommand

Dependency Rules

Allowed:

Application → Domain

Forbidden:

Application → Infrastructure

Application → GraphQL

Application → REST

Application → Persistence

Application → Storage SDK

Allowed Content

Use case interfaces.
Application services.
Commands.
Application orchestration.

Forbidden Content

GraphQL annotations.
REST annotations.
JPA entities.
Repository implementations.
HTTP objects.
MinIO clients.

Restrictions

The application layer MUST NOT expose framework-specific objects.

Examples forbidden:

GraphQLInput

ResponseEntity

MultipartFile

MinioClient


---

# 6. Domain


Location


<BackendDir>/src/main/java/<BasePackage>/<Module>/domain

Ownership

This section is the only source of truth for:

Domain organization.
Domain models.
Domain ports.
Domain rules.
Domain restrictions.

Purpose

The infrastructure layer connects the application with external technologies and frameworks.

It isolates external concerns from the application and domain layers.

Responsibilities

The infrastructure layer is responsible for:

Exposing external APIs.

Implementing application adapters.

Implementing domain ports.

Communicating with databases.

Communicating with external storage providers.

Communicating with external systems.

Translating external data formats.


Internal Structure

model

Contains domain entities and business objects.

Examples:

Company

Contract

Document
valueobject

Contains immutable business concepts.

Examples:

TaxId

DocumentType

FileMetadata
port

Contains interfaces used to communicate with external systems.

Examples:

CompanyRepository

StoragePort

Dependency Rules

Allowed:

Domain

The domain must not depend on external layers.

Forbidden:

Domain → Application

Domain → Infrastructure

Domain → Frameworks

Allowed Content

Domain models.
Value objects.
Domain ports.
Business abstractions.

Forbidden Content

GraphQL annotations.
REST annotations.
JPA annotations.
CDI annotations.
Storage SDK classes.

Restrictions

The domain layer MUST remain framework independent.



---

# 7. Infrastructure


Location

<BackendDir>/src/main/java/<BasePackage>/<Module>/infrastructure

Ownership

This section is the only source of truth for:

External adapters.
Framework integrations.
API exposure.
Persistence communication.
Storage communication.

Purpose

The infrastructure layer connects the application with external technologies.

Responsibilities

The infrastructure layer is responsible for:

Exposing APIs.
Implementing domain ports.
Communicating with databases.
Communicating with external storage.
Translating external data formats.

Internal Structure

graphql

Contains GraphQL API adapters.

rest

Contains REST API adapters.

persistence

Contains database adapters.

storage

Contains external storage adapters.

Dependency Rules

Allowed:

Infrastructure → Application

Infrastructure → Domain

Forbidden:

Infrastructure → Business Logic

Allowed Content

API adapters.
Database adapters.
External service adapters.
Framework implementations.

Forbidden Content

Business rules.
Domain decisions.
Application workflows.

Restrictions

Infrastructure components must adapt external technologies.

They must not become a second application layer.

---

# 8. GraphQL


Location


<BackendDir>/src/main/java/<BasePackage>/<Module>/infrastructure/graphql

Ownership

This section is the only source of truth for:

GraphQL API exposure.
GraphQL resolver organization.
GraphQL DTO structure.
GraphQL mapping rules.
GraphQL restrictions.

Purpose

The GraphQL component exposes application operations through the GraphQL API.

Responsibilities

The GraphQL component is responsible for:

Receiving GraphQL requests.
Validating GraphQL input structures.
Calling application use cases.
Mapping application results into GraphQL responses.
Exposing queries and mutations.

Internal Structure

dto

Contains GraphQL-specific data transfer objects.

input

Contains objects received from GraphQL clients.

Examples:

CreateCompanyInput

UpdateCompanyInput
response

Contains objects returned to GraphQL clients.

Examples:

CompanyResponse

DocumentResponse
mapper

Contains transformations between:

GraphQL DTO

↓

Application Objects

↓

GraphQL DTO
query

Contains GraphQL query adapters.

Examples:

@Query

listCompanies()

company()
mutation

Contains GraphQL mutation adapters.

Examples:

@Mutation

createCompany()

updateCompany()

Dependency Rules

Allowed:

GraphQL
        ↓

Application

Forbidden:

GraphQL → Repository

GraphQL → Entity

GraphQL → Database

GraphQL → Storage Client

Allowed Content

@GraphQLApi
@Query
@Mutation
GraphQL DTOs
GraphQL mappers

Forbidden Content

Business logic.
Persistence logic.
Domain rules.
Database queries.

Restrictions

Resolvers must remain thin.

Example:

Allowed:

public CompanyResponse createCompany(Input input){

    return mapper.toResponse(
        service.execute(command)
    );
}


A GraphQL module MUST always contain at least one Query operation.

If the first generated endpoint is a Mutation and no Query exists:

The Skill MUST generate a minimal health/query endpoint.

Example:

@Query
public String graphqlStatus(){

    return "OK";

}


GraphQL resolvers must not depend on REST components.

GraphQL resolvers must not depend on Storage adapters directly.


Forbidden:

public CompanyResponse createCompany(){

    repository.save(entity);

}


---


# 9. REST


Location

<BackendDir>/src/main/java/<BasePackage>/<Module>/infrastructure/rest

Ownership

This section is the only source of truth for:

REST API exposure.
HTTP controller organization.
REST DTO handling.
HTTP request mapping rules.

Purpose

The REST component exposes operations that require HTTP capabilities.

REST is mainly used for:

File uploads.
File downloads.
Binary content.
External integrations.
Operations requiring HTTP semantics.

Responsibilities

The REST component is responsible for:

Receiving HTTP requests.
Validating request structures.
Converting HTTP data into application commands.
Calling application use cases.
Returning HTTP responses.

Internal Structure

controller

Contains REST endpoints.

Examples:

@Path("/files")

POST

GET
dto

Contains REST request and response objects.

Examples:

UploadFileRequest

FileResponse
mapper

Contains transformations between:

REST DTO

↓

Application Objects

Dependency Rules

Allowed:

REST Controller

↓

Application

Forbidden:

REST Controller → Repository

REST Controller → Entity

REST Controller → MinIO Client

Allowed Content

JAX-RS controllers.
HTTP DTOs.
HTTP mappings.
Response handling.

Forbidden Content

Business logic.
Database access.
Storage SDK calls.

Restrictions

REST controllers must not implement workflows.

Example:

Allowed:

uploadController()

    ↓

uploadUseCase.execute()

Forbidden:

uploadController()

    ↓

minioClient.putObject()


---

# 9. REST

Location

<BackendDir>/src/main/java/<BasePackage>/<Module>/infrastructure/rest

Ownership

This section is the only source of truth for:

REST API exposure.
HTTP controller organization.
HTTP request and response handling.
Multipart request handling.
REST DTO structure.
HTTP mapping rules.

Purpose

The REST component exposes application operations that require HTTP capabilities.

REST is mainly used for:

File uploads.
File downloads.
Binary content transfer.
Multipart requests.
External integrations.
Operations requiring HTTP-specific semantics.

Responsibilities

The REST component is responsible for:

Receiving HTTP requests.
Handling HTTP headers, parameters and multipart data.
Validating HTTP request structures.
Converting HTTP data into application commands.
Calling application use cases.
Converting application results into HTTP responses.
Returning binary responses when required.

Internal Structure

controller

Contains REST endpoint adapters.

Examples:

@Path("/files")
POST
GET
PUT
DELETE

Controllers are responsible only for HTTP communication.

dto

Contains REST-specific request and response objects.

Examples:

Request:

UploadFileRequest

Response:

FileResponse

DTOs may contain:

Request fields.
Response fields.
HTTP-specific representations.

DTOs must not contain:

Business rules.
Persistence entities.
Storage provider objects.
multipart

Contains multipart request handling objects.

Used for:

File uploads.
Binary content reception.
Multipart/form-data requests.

Examples:

FileUploadRequest
MultipartFileData
mapper

Contains transformations between:

REST DTO

↓

Application Objects

Examples:

UploadFileRequest

↓

UploadFileCommand

Dependency Rules

Allowed:

REST Controller

        ↓

Application

Allowed:

REST DTO

        ↓

REST Mapper

        ↓

Application Objects

Forbidden:

REST Controller → Repository
REST Controller → Entity
REST Controller → Storage Client
REST Controller → GraphQL Component

Allowed Content

JAX-RS controllers.
HTTP annotations.
REST DTOs.
Multipart handlers.
HTTP response handling.
REST mappers.

Forbidden Content

Business logic.
Database access.
Persistence entities.
Storage SDK calls.
GraphQL DTOs.
GraphQL resolvers.

Restrictions

REST controllers must remain thin.

Controllers must not implement application workflows.

Allowed:

uploadController()

        ↓

uploadUseCase.execute()

        ↓

storagePort

Forbidden:

uploadController()

        ↓

minioClient.putObject()

REST endpoints handling files must delegate storage operations through application use cases.

The REST layer must never communicate directly with storage providers.

---

# 11. Storage

Location

<BackendDir>/src/main/java/<BasePackage>/<Module>/infrastructure/storage

Ownership

This section is the only source of truth for:

External storage integration.
Storage adapters.
Storage provider communication.
Storage configuration.
File storage operations.

Purpose

The storage component provides integration with external storage systems.

It allows modules to store and retrieve files without coupling application logic to specific storage providers.

Examples:

MinIO.
Amazon S3.
Other object storage providers.

Responsibilities

The storage component is responsible for:

Uploading files.
Downloading files.
Removing stored files.
Managing storage object paths.
Communicating with external storage providers.
Translating storage provider responses.
Managing storage metadata required by adapters.
Resolving stored object locations.

Internal Structure

adapter

Contains implementations that communicate with external storage providers.

Examples:

MinioStorageAdapter

S3StorageAdapter

The adapter is responsible for:

Creating storage objects.
Uploading binary content.
Downloading binary content.
Deleting objects.
Mapping provider responses.

The adapter hides storage provider details from the rest of the application.

configuration

Contains storage provider configuration.

Examples:

StorageConfiguration

MinioClientProvider

Responsible for:

Creating storage clients.
Loading storage configuration.
Providing storage infrastructure dependencies.

Dependency Rules

Allowed:

Storage Adapter

        ↓

External Storage Provider

Allowed:

Storage Adapter

        ↓

Storage Port

Allowed:

Application

        ↓

Storage Port

        ↓

Storage Adapter

Forbidden:

Storage → Application Workflow
Storage → GraphQL
Storage → REST Controller
Application → MinioClient

Allowed Content

Storage adapters.
Storage clients.
Storage configuration.
Provider mappings.
Storage communication implementations.

Forbidden Content

Business rules.
File validation rules.
API controllers.
GraphQL DTOs.
REST DTOs.
Application workflows.

Restrictions

Storage providers must be hidden behind abstractions.

Business modules must not directly depend on storage SDK implementations.

Forbidden direct dependencies:

MinioClient
S3Client

Application services must communicate through storage abstractions.

Correct:

UploadFileService

        ↓

StoragePort

        ↓

MinioStorageAdapter

        ↓

MinioClient

Incorrect:

UploadFileService

        ↓

MinioClient

Storage adapters must not contain business decisions.

They only translate application storage operations into provider-specific operations.

---

# 12. Bruno

Location
<BrunoDir>/<Module>
Ownership

This section is the only source of truth for:

API client request generation.
Endpoint testing files.
Bruno organization.
Example execution data.
Purpose

Bruno files provide executable API requests for validating generated endpoints during development and integration testing.

Responsibilities

The Bruno component is responsible for:

Creating one Bruno request file per generated endpoint.
Organizing requests by module.
Providing executable examples.
Providing reusable variables.
Providing direct execution examples.
Internal Structure

Each endpoint must generate one Bruno file.

Example:

Company

├── CreateCompany.bru

├── UpdateCompany.bru

├── ListCompanies.bru

└── GetCompanyById.bru

REST examples:

Contract

├── UploadContractFile.bru

└── DownloadContractFile.bru
GraphQL Request Rules

GraphQL Bruno requests must contain:

GraphQL operation definition.
Headers.
Authentication configuration.
Example variables.
Direct executable request example.
Example response.

Every GraphQL endpoint MUST generate:

Variable execution mode

Example:

mutation createCompany($input: CreateCompanyInput!) {
    createCompany(input:$input){
        id
        name
    }
}

with:

{
 "input":{
    "name":"Example Company",
    "taxId":"123456789",
    "industry":"Technology",
    "address":"123 Main Street"
 }
}
Direct execution mode

The request must also include an example without variables.

Example:

mutation {
 createCompany(
    input:{
       name:"Example Company"
       taxId:"123456789"
       industry:"Technology"
       address:"123 Main Street"
    }
 ){
    id
    name
 }
}

The direct execution example MUST use valid example values.

REST Request Rules

REST Bruno requests must contain:

HTTP method.
URL.
Headers.
Authentication configuration.
Parameters when required.
Example body when required.
Example response.

For file operations REST requests must support examples for:

Multipart upload.
File download.
Content type configuration.

Examples:

UploadContractFile.bru

must support:

multipart/form-data

with:

file
metadata

when required by the endpoint specification.

Allowed Content

Each Bruno request may contain:

Endpoint URL.
HTTP method.
GraphQL query or mutation.
Variables.
Example payload.
Example response.
Authentication placeholders.
Forbidden Content

Bruno files must not contain:

Business logic.
Application code.
Domain implementation.
Persistence logic.
Generated source code.
Restrictions

Every generated endpoint MUST have a corresponding Bruno request.

When a new endpoint is added:

The Skill MUST update:

<BrunoDir>/<Module>

The Skill MUST generate requests ready for immediate execution.

The Skill MUST NOT generate Bruno files that require manual modification before the first execution.

---

# 1. NAME

GraphQL Skill

---

# 2. DESCRIPTION

Defines GraphQL API design standards, resolver architecture, DTO conventions, pagination, filtering, validation, error handling, testing rules and API exposure standards for backend projects using Hexagonal Architecture.

This skill ensures GraphQL is implemented using Quarkus SmallRye GraphQL and a code-first approach.

This skill defines:

* Module structure
* File responsibilities
* GraphQL architecture
* REST architecture
* Endpoint evolution rules
* Bruno collection generation
* Validation rules
* Compilation safety rules

This skill does not define:

* Business operations
* Business rules
* Database schemas
* Permissions
* Roles
* Project-specific requirements
* Endpoint specifications

Those responsibilities belong to project specifications and other skills.

---

# 3. MODULE GENERATION MODE

The SPEC will define:

```text
ModuleGenerationMode
```

Allowed values:

```text
BOOTSTRAP
INCREMENTAL
```

## BOOTSTRAP

Used when a module is generated for the first time.

The AI MUST create:

* Module folder structure
* Domain layer
* Application layer
* Infrastructure layer
* Initial endpoint implementation
* Initial Bruno collection

The AI MUST read:

* Module Structure
* File Definitions
* Bootstrap Rules
* GraphQL Rules
* REST Rules
* Validation Rules

---

## INCREMENTAL

Used when the module already exists.

The AI MUST:

* Reuse existing files
* Extend existing functionality
* Modify only required files

The AI MUST NOT:

* Recreate module structure
* Duplicate classes
* Create alternative versions of existing files

Examples:

Forbidden:

```text
CompanyServiceV2
CompanyRepositoryNew
CompanyResolverUpdated
```

The AI must update existing implementations whenever possible.

---

# 4. TEMPLATE VARIABLE RULES

This skill uses generic placeholders.

The SPEC will provide the real values.

Placeholders:

```text
Module
NameEndpoint
```

Replacement rules:

```text
Module
→ Company
→ Contract
→ Project
→ Notification
```

```text
NameEndpoint
→ CreateCompany
→ UpdateCompany
→ GetCompanyById
→ ListCompanies
```

Examples:

```text
ModuleRepository
→ CompanyRepository
```

```text
NameEndpointService
→ CreateCompanyService
```

```text
NameEndpointUseCase
→ CreateCompanyUseCase
```

The AI MUST replace placeholders before generating code.

The AI MUST NOT generate code containing unresolved placeholders.

---

# 5. PROJECT COMPATIBILITY RULES

Projects using this skill are expected to use:

```text
Quarkus 3+
Java 21
Jakarta EE
SmallRye GraphQL
Panache Repository
```

Mandatory imports:

```java
jakarta.inject.*
jakarta.enterprise.*
jakarta.transaction.*
jakarta.persistence.*
jakarta.validation.*
```

Forbidden imports:

```java
javax.inject.*
javax.enterprise.*
javax.transaction.*
javax.persistence.*
javax.validation.*
```

The AI MUST use Jakarta packages exclusively.

---

# APPLICATION LAYER RULES

One use case = one application entrypoint.

Choose ONE:

application/usecase
OR
application/service

Never generate both for the same operation.

---

# 6. ARCHITECTURE RULES

The architecture is Hexagonal Architecture.

Dependency direction:

```text
Infrastructure
      ↓
Application
      ↓
Domain
```

Allowed:

```text
Infrastructure → Application
Infrastructure → Domain

Application → Domain
```

Forbidden:

```text
Domain → Application
Domain → Infrastructure

Application → Infrastructure
Application → GraphQL
Application → Persistence
```

Domain must remain framework independent.

Business logic belongs only to the Application layer.

Infrastructure contains adapters only.

---

# 7. DEPENDENCY VALIDATION RULES

Before generating code, the AI MUST validate all referenced dependencies.

The AI MUST verify:

* Classes exist
* Interfaces exist
* Methods exist
* Constructors exist
* Beans exist
* Repository contracts exist

The AI MUST NOT assume implementations.

Example:

Interface exists:

```java
AuthenticatedUserProvider
```

The AI must verify that a valid CDI implementation also exists.

If verification is impossible:

```text
STOP GENERATION
```

Report the missing dependency.

The AI MUST NOT invent missing implementations.

---

# 8. SECURITY RULES

Authentication logic belongs to the Security layer.

Resolvers must not implement authentication manually.

Resolvers must use project security abstractions.

Examples:

```text
AuthenticatedUserProvider
CurrentUser
SecurityIdentity
```

Before using security dependencies the AI MUST verify:

* Interface exists
* Implementation exists
* CDI bean exists

If validation fails:

```text
STOP GENERATION
```

The AI MUST NOT generate fake security providers.

The AI MUST NOT parse JWTs manually unless explicitly required by project specifications.

---

# 9. GRAPHQL RULES

Technology:

```text
Quarkus SmallRye GraphQL
```

Default endpoint:

```text
/graphql
```

The AI MUST use:

```java
@GraphQLApi
@Query
@Mutation
```

GraphQL operations must call Application layer use cases.

Resolvers must remain thin.

Resolvers MUST NOT:

* Access databases directly
* Use repositories directly
* Contain business logic
* Call external services directly

GraphQL DTOs belong exclusively to Infrastructure.

Application and Domain layers must remain GraphQL-independent.

---

# 10. REST RULES

REST endpoints are allowed when explicitly required by the specification.

REST controllers belong to Infrastructure.

Examples:

```java
@Path("/companies")
```

Allowed annotations:

```java
@Path
@GET
@POST
@PUT
@DELETE
```

REST controllers must call Application use cases.

REST controllers MUST NOT:

* Access repositories directly
* Access entities directly
* Contain business logic

The same use cases may be reused by GraphQL and REST.

---

# 11. BRUNO RULES

Every generated endpoint must generate a Bruno request.

Location:

```text
.bruno/Module/
```

Examples:

```text
CreateCompany.bru
UpdateCompany.bru
DeleteCompany.bru
GetCompanyById.bru
ListCompanies.bru
```

The Bruno request must contain:

* Request definition
* Variables
* Example payload
* Example response

The AI MUST update the Bruno collection whenever a new endpoint is added.

---

# 12. BOOTSTRAP RULES

Bootstrap mode is used only when creating a module for the first time.

The first endpoint is responsible for creating the module foundation.

During BOOTSTRAP the AI may create:

* Domain structure
* Application structure
* Infrastructure structure
* Initial repository contracts
* Initial GraphQL resolvers
* Initial persistence adapters
* Initial Bruno collection

After bootstrap:

```text
ModuleGenerationMode = INCREMENTAL
```

Future endpoints must reuse existing files whenever possible.

The AI MUST NOT recreate the module during incremental generations.

The AI MUST evolve the existing module incrementally.

# 13. MODULE STRUCTURE

This section defines the initial structure that MUST be created when:

```text
ModuleGenerationMode = BOOTSTRAP
```

The AI MUST create the following structure:

```text

Module
├── application
│   ├── service
│   │   └── NameEndpointService.java
│   │
│   └── usecase
│       └── NameEndpointUseCase.java
│
├── domain
│   ├── model
│   │   └── Module.java
│   │
│   └── port
│       └── ModuleRepository.java
│
└── infrastructure
    ├── graphql
    │   ├── ModuleMutationResolver.java
    │   ├── ModuleQueryResolver.java
    │   │
    │   ├── dto
    │   │   ├── input
    │   │   │   └── NameEndpointInput.java
    │   │   │
    │   │   └── response
    │   │       └── ModuleResponse.java
    │   │
    │   └── mapper
    │       └── ModuleGraphQLMapper.java
    │
    └── persistence
        ├── adapter
        │   └── ModuleRepositoryAdapter.java
        │
        ├── entity
        │   └── ModuleEntity.java
        │
        └── repository
            └── ModulePanacheRepository.java
```

Rules:

* The structure is created only once.
* Future endpoints must reuse the structure.
* The AI MUST NOT duplicate files.
* The AI MUST NOT create V2 classes.
* The AI MUST NOT create alternative structures.

Example:

Forbidden:

```text
CompanyServiceV2
CompanyRepositoryNew
CompanyResolverUpdated
```

---

# 14. DOMAIN MODEL FILE RULES

File:

```text
domain/model/Module.java
```

Purpose:

Represents the business entity.

Responsibilities:

* Business state
* Domain invariants
* Domain behavior

Allowed dependencies:

```java
java.*
```

Forbidden dependencies:

```java
jakarta.persistence.*
jakarta.inject.*
org.eclipse.microprofile.graphql.*
io.quarkus.*
```

The Domain model MUST remain framework-independent.

Example:

```java
public class Company {

    private UUID id;

    private String name;

    private String taxId;

}
```

Rules:

* No annotations from GraphQL.
* No annotations from Persistence.
* No CDI annotations.
* No Panache inheritance.

---

# 15. DOMAIN PORT FILE RULES

File:

```text
domain/port/ModuleRepository.java
```

Purpose:

Defines repository contracts used by Application.

Example:

```java
public interface CompanyRepository {

    Company save(Company company);

    Optional<Company> findById(UUID id);

}
```

Rules:

* Interface only.
* No implementation.
* No persistence logic.
* No Panache code.

The Domain layer owns repository contracts.

---

# 16. APPLICATION USE CASE FILE RULES

File:

```text
application/usecase/NameEndpointUseCase.java
```

Purpose:

Represents a business action.

Examples:

```text
CreateCompanyUseCase
UpdateCompanyUseCase
DeleteCompanyUseCase
GetCompanyByIdUseCase
ListCompaniesUseCase
```

Rules:

* One use case per endpoint.
* Public API for Application layer.
* Invoked by GraphQL or REST.

Example:

```java
public interface CreateCompanyUseCase {

    CompanyResponse execute(CreateCompanyCommand command);

}
```

Use cases define business operations.

---

# 17. APPLICATION SERVICE FILE RULES

File:

```text
application/service/NameEndpointService.java
```

Purpose:

Implements the Use Case.

Example:

```java
@ApplicationScoped
public class CreateCompanyService
        implements CreateCompanyUseCase {
}
```

Responsibilities:

* Business orchestration
* Validation
* Calling Domain ports
* Transaction coordination

Forbidden:

* GraphQL annotations
* REST annotations
* Panache usage
* EntityManager usage

The Service layer MUST depend only on:

```text
Domain
Application
```

---

# 18. GRAPHQL RESOLVER FILE RULES

Files:

```text
ModuleMutationResolver.java
ModuleQueryResolver.java
```

Purpose:

Expose GraphQL operations.

Examples:

```java
@GraphQLApi
public class CompanyMutationResolver {
}
```

Rules:

Mutations:

```java
@Mutation
```

Queries:

```java
@Query
```

Resolvers MUST:

* Receive requests
* Validate technical input
* Call Use Cases
* Return responses

Resolvers MUST NOT:

* Access repositories
* Access persistence
* Contain business logic

Use constructor injection.

Forbidden:

```java
@Inject
private CreateCompanyService service;
```

Preferred:

```java
private final CreateCompanyUseCase useCase;

public CompanyMutationResolver(
    CreateCompanyUseCase useCase
) {
    this.useCase = useCase;
}
```

---

# 19. GRAPHQL INPUT DTO FILE RULES

File:

```text
graphql/dto/input/NameEndpointInput.java
```

Purpose:

Receive GraphQL input.

Example:

```java
public class CreateCompanyInput {

    @NotBlank
    private String name;

}
```

Rules:

* One input DTO per mutation.
* Validation annotations allowed.
* Never expose entities.

Allowed:

```java
@NotBlank
@NotNull
@Email
@Size
```

---

# 20. GRAPHQL RESPONSE DTO FILE RULES

File:

```text
graphql/dto/response/ModuleResponse.java
```

Purpose:

Expose GraphQL output.

Example:

```java
public class CompanyResponse {

    private UUID id;

    private String name;

}
```

Rules:

* Never expose entities.
* Never expose persistence models.
* GraphQL returns response DTOs only.

---

# 21. GRAPHQL MAPPER FILE RULES

File:

```text
graphql/mapper/ModuleGraphQLMapper.java
```

Purpose:

Convert:

```text
Input DTO
↓
Application DTO

Application DTO
↓
Response DTO
```

Example:

```java
public class CompanyGraphQLMapper {
}
```

Rules:

* Mapping only.
* No business logic.
* No repository access.

---

# 22. PERSISTENCE ENTITY FILE RULES

File:

```text
persistence/entity/ModuleEntity.java
```

Purpose:

Database representation.

Example:

```java
@Entity
@Table(name = "company")
public class CompanyEntity {
}
```

Rules:

* Persistence annotations allowed.
* Database mapping only.
* No business logic.

The AI MUST validate table existence before generation.

---

# 23. PANACHE REPOSITORY ID RULES

The AI MUST infer the repository ID type from the JPA entity @Id field.

Examples:

@Id UUID id
-> PanacheRepositoryBase<Entity, UUID>

@Id Long id
-> PanacheRepository<Entity>

@Id Integer id
-> PanacheRepositoryBase<Entity, Integer>

Rules:

Never assume Long IDs.

The repository generic ID type MUST match exactly the @Id type declared in the entity.

Validation:

Repository ID type == Entity @Id type

If they differ:
STOP generation and report the mismatch.

---

# 24. PERSISTENCE ADAPTER FILE RULES

File:

```text
persistence/adapter/ModuleRepositoryAdapter.java
```

Purpose:

Implements Domain repository contracts.

Example:

```java
@ApplicationScoped
public class CompanyRepositoryAdapter
        implements CompanyRepository {
}
```

Responsibilities:

```text
Persistence Entity
↓
Persistence Mapper
↓
Domain Model
```

Rules:

* Infrastructure implementation only.
* Must implement Domain Port.
* Must use Panache Repository.
* Must not contain business logic.

# 25. ENDPOINT EVOLUTION RULES

This section applies when:

```text
ModuleGenerationMode = INCREMENTAL
```

The module already exists.

The AI MUST analyze existing files before generating code.

The AI MUST determine:

* Which files already exist
* Which files require modification
* Which files must be created

The AI MUST reuse existing files whenever possible.

---

## Existing File Reuse Rules

If the file already exists:

```text
Module.java
ModuleRepository.java
ModuleEntity.java
ModulePanacheRepository.java
ModuleRepositoryAdapter.java
ModuleResponse.java
ModuleGraphQLMapper.java
ModuleMutationResolver.java
ModuleQueryResolver.java
```

The AI MUST modify the file.

The AI MUST NOT create a new version.

Forbidden:

```text
CompanyV2.java
CompanyRepositoryNew.java
CompanyMutationResolverV2.java
```

---

## New File Creation Rules

The following files are endpoint-specific and may be created for each endpoint:

```text
NameEndpointUseCase.java
NameEndpointService.java
NameEndpointInput.java
```

Examples:

```text
CreateCompanyUseCase.java
UpdateCompanyUseCase.java
DeleteCompanyUseCase.java
GetCompanyByIdUseCase.java
ListCompaniesUseCase.java
```

---

## Shared File Update Rules

The following files typically evolve with every new endpoint:

```text
ModuleMutationResolver.java
ModuleQueryResolver.java
ModuleRepository.java
ModuleRepositoryAdapter.java
ModulePanacheRepository.java
ModuleGraphQLMapper.java
ModuleResponse.java
```

The AI MUST extend these files rather than replace them.

---

# 26. GRAPHQL QUERY GENERATION RULES

This section applies to:

```text
Get*
Find*
List*
Search*
Count*
```

Examples:

```text
GetCompanyById
ListCompanies
SearchCompanies
```

---

## Resolver Rules

Queries MUST be added to:

```text
ModuleQueryResolver.java
```

Example:

```java
@Query
public CompanyResponse getCompanyById(UUID id)
```

---

## Use Case Rules

Each query creates:

```text
GetCompanyByIdUseCase
GetCompanyByIdService
```

---

## Repository Rules

Repository contracts may be expanded:

Example:

```java
Optional<Company> findById(UUID id);

Page<Company> findAll(...);
```

---

## DTO Rules

Query endpoints usually do not require Input DTOs.

Filter DTOs may be created when required.

Examples:

```text
CompanyFilter.java
```

---

# 27. GRAPHQL MUTATION GENERATION RULES

This section applies to:

```text
Create*
Update*
Delete*
Approve*
Reject*
Assign*
```

Examples:

```text
CreateCompany
UpdateCompany
DeleteCompany
```

---

## Resolver Rules

Mutations MUST be added to:

```text
ModuleMutationResolver.java
```

Example:

```java
@Mutation
public CompanyResponse createCompany(
    CreateCompanyInput input
)
```

---

## Use Case Rules

Each mutation creates:

```text
CreateCompanyUseCase
CreateCompanyService
```

or

```text
UpdateCompanyUseCase
UpdateCompanyService
```

---

## Input DTO Rules

Every mutation MUST have its own Input DTO.

Examples:

```text
CreateCompanyInput
UpdateCompanyInput
```

The AI MUST NOT reuse mutation input DTOs.

---

## Repository Rules

Repository contracts may be expanded.

Examples:

```java
save(...)
update(...)
delete(...)
exists(...)
```

---

# 28. REST ENDPOINT GENERATION RULES

This section applies only when:

```text
EndpointType = REST
```

defined by the SPEC.

---

## Controller Rules

Controllers belong to:

```text
infrastructure/rest
```

Example:

```text
CompanyResource.java
```

---

## HTTP Verb Mapping

Create:

```java
POST
```

Update:

```java
PUT
```

Delete:

```java
DELETE
```

Get:

```java
GET
```

List:

```java
GET
```

---

## Use Case Reuse Rules

REST MUST reuse the same Use Cases created for GraphQL.

Example:

```text
CreateCompanyUseCase
```

may be called by:

```text
GraphQL Resolver
REST Controller
```

The AI MUST NOT duplicate business logic.

---

# 29. BRUNO FILE GENERATION RULES

Every endpoint MUST generate a Bruno request.

Location:

```text
.bruno/Module/
```

Examples:

```text
CreateCompany.bru
UpdateCompany.bru
DeleteCompany.bru
GetCompanyById.bru
ListCompanies.bru
```

---

## GraphQL Bruno Rules

Mutation example:

```graphql
mutation CreateCompany {
}
```

Query example:

```graphql
query GetCompanyById {
}
```

---

## REST Bruno Rules

REST examples:

```http
POST /companies
GET /companies/{id}
PUT /companies/{id}
DELETE /companies/{id}
```

---

## Mandatory Contents

Every Bruno file MUST contain:

```text
Request
Variables
Example payload
Example response
```

---

# 30. VALIDATION RULES

Before generating code the AI MUST validate:

---

## Database Validation

Verify:

* Table exists
* Column exists
* Foreign key exists
* Constraint exists

Source of truth:

```text
backoffice-schema.sql
```

If validation fails:

```text
STOP GENERATION
```

---

## Class Validation

Verify:

* Class exists
* Interface exists
* Method exists
* Constructor exists

The AI MUST NOT invent APIs.

---

## Security Validation

Verify:

* Security interface exists
* Security implementation exists
* CDI bean exists

Example:

```text
AuthenticatedUserProvider
```

must have an implementation.

The AI MUST NOT assume one exists.

---

# 31. GRAPHQL BOOTSTRAP RULE

Every generated module MUST contain at least one Query resolver.

Location:

infrastructure/graphql

Example:

ModuleNameQueryResolver.java

Requirements:

* Annotated with @GraphQLApi
* Must expose at least one @Query operation
* Must compile without additional dependencies
* Must be generated even if EndpointNames only contains mutations

Example:

@GraphQLApi
public class CompanyQueryResolver {

```
@Query
public String companyHealth() {
    return "Company module active";
}
```

}

Purpose:

Prevent SmallRye GraphQL startup failures caused by mutation-only schemas.

---

# 32. COMPILATION VALIDATION RULES

Before finalizing generation the AI MUST perform a compilation safety review.

---

## Import Validation

Allowed:

```java
jakarta.*
```

Forbidden:

```java
javax.*
```

---

## Dependency Validation

Verify all constructor dependencies.

Example:

```java
public CreateCompanyService(
    CompanyRepository repository
)
```

The repository must exist.

---

## Bean Validation

For every injected dependency:

```java
@Inject
```

or constructor injection

The AI MUST verify:

```text
CDI bean exists
```

Otherwise:

```text
STOP GENERATION
```

---

## Method Validation

The AI MUST verify:

```text
method exists
```

before calling it.

Example:

Forbidden:

```java
currentUser.getId();
```

if the method was never verified.

---

# 33. FINAL CHECKLIST

Before generation completes:

```text
[ ] Module generation mode identified

[ ] Database schema validated

[ ] Tables validated

[ ] Columns validated

[ ] Existing files inspected

[ ] Existing files reused

[ ] No duplicate classes generated

[ ] Domain layer respected

[ ] Application layer respected

[ ] Infrastructure layer respected

[ ] GraphQL rules respected

[ ] REST rules respected

[ ] Bruno file generated

[ ] Security dependencies validated

[ ] Jakarta imports used

[ ] No javax imports generated

[ ] Constructor dependencies validated

[ ] CDI beans validated

[ ] Repository contracts validated

[ ] Compilation review completed

[ ] Endpoint generated successfully
```

If any item fails:

```text
STOP GENERATION
REPORT VALIDATION FAILURE
DO NOT GENERATE CODE
```

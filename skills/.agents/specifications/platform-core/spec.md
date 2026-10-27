# 1. Name

Platform Core Specification

# 2. Description

This Specification defines the complete system configuration required to generate a backend platform based on a modular hexagonal architecture.

This Specification establishes:

Workspace structure (real project folders)
System variables for project reuse
Skill binding and execution targets
Backend infrastructure definition
File-level generation requirements
Configuration and bootstrap artifacts
Generation lifecycle and validation flow

This Specification does NOT define:

Architecture rules (handled by Architecture Skill)
Business logic
Domain modeling
API design
Database schema design
Implementation details inside 

---

# 3. Variables (Specification Inputs)

This is the ONLY section that must be modified per project.

---

## Core Variables

BasePackage = io.joyit
ProjectName = PlatformCore

---

## Workspace Variables (REAL PROJECT STRUCTURE)

BackendContractDir = waia-cn-backend-chatbot

DatabaseContractDir = waia-cn-database

BrunoDir = waia-cn-bruno

SkillsDir = waia-cn-skills/.agents/skills

---

## Database Structure Variables

FlywayDir = waia-db-database/flyway

DatabaseAssetsDir = waia-cn-skills/.agents/database

DatabaseSchemaFile = <DatabaseAssetsDir>/schema.sql

---

## Backend Configuration Variables

Framework = Quarkus
JavaVersion = 21
QuarkusVersion = 3.8.3
ApiStyle = GraphQL
ArchitectureType = Hexagonal

---

## Infrastructure Variables

Database = PostgreSQL
Messaging = RabbitMQ
Storage = MinIO

---

## Rule

Variables MUST replace ALL hardcoded paths.

Workspace folders MUST reflect the real repository structure.

No directory path can be hardcoded anywhere in the Spec.

---

## Example

Correct usage:

<BackendContractDir>/src/main/java/<BasePackage>

<BackendContractDir>/src/main/resources

---

# # 4. Skill Registry & Binding

The Specification delegates all implementation and architecture generation to external Skills.

The Specification defines:

- which Skills must be loaded;
- where each Skill is located;
- which project scope each Skill governs.

The Specification NEVER duplicates implementation rules defined by Skills.

---

## Skill Registry

Registered Skills

01-Architecture-Skill

---

## Skill Locations

01-Architecture-Skill

<SkillsDir>/01-Architecture-Skill/SKILL.md

---

## Skill Execution Binding

Skill

01-Architecture-Skill

Location

<SkillsDir>/01-Architecture-Skill/SKILL.md

Bound Scope

<BackendContractDir>/

The Skill governs every artifact generated inside its bound scope.

---

## Execution Rules

Before generating any project artifact, the generator MUST:

1. Load this Specification.
2. Resolve every Specification Variable.
3. Load every registered Skill.
4. Apply every rule defined by each loaded Skill.
5. Generate every directory defined by the Skill.
6. Generate every file defined by the Skill.
7. Generate the contents of every file according to the owning Skill section.
8. Respect every dependency rule, restriction, and validation defined by the Skill.

The generator MUST NOT omit any mandatory directory or file defined by a loaded Skill.

The generator MUST NOT invent directories or files that are not defined by either:

- this Specification; or
- a loaded Skill.

---

## Responsibility Hierarchy

If a conflict exists:

Specification
    >
Skill

---

## Specification Responsibilities

The Specification defines only:

- project configuration;
- workspace variables;
- generation targets;
- file ownership;
- artifact ownership;
- Skill bindings.

---

## Skill Responsibilities

Skills define:

- project structure;
- directory structure;
- generated files;
- file contents;
- architecture rules;
- implementation rules;
- dependency rules;
- naming conventions;
- generation constraints;
- validation rules.

---

## Rule

The Specification MUST NEVER redefine implementation details already owned by a Skill.

Every generated directory and every generated file MUST be defined by exactly one Skill section.

Skills MUST NOT redefine project-specific configuration owned by the Specification.

---

# 5. Workspace Structure

ONLY physical structure (no behavior).

## Root

workspace/

├── <BackendContractDir>/
├── <DatabaseContractDir>/
├── <BrunoDir>/

## Backend Structure

<BackendContractDir>/

├── src/
│   ├── main/
│   │   ├── java/
│   │   │   └── <BasePackage>/
│   │   └── resources/
│   └── test/
├── pom.xml
├── docker-compose.yml
├── README.md
└── CHECKLIST.md

## Database Contract Structure

<DatabaseContractDir>/

├── flyway/
│   ├── V1__Initial_Schema.sql
│   ├── V2__*.sql

---

# 6. Output Artifacts (FILE-LEVEL DEFINITION)

⚠️ Aquí es donde estaba tu problema anterior.

Ahora NO es lista.
Ahora es Component Sections (igual que Skills).

---

# 6.1 pom.xml

Location

<BackendContractDir>/pom.xml

Purpose

Defines Maven build configuration for the backend project.

Responsibilities

Dependency management
Quarkus configuration
Java version enforcement
Build lifecycle definition

Allowed Content

Maven dependencies
Build plugins
Quarkus BOM

Forbidden Content

Business logic
Runtime code
Environment-specific values

---

# 6.2 docker-compose.yml

Location

<BackendContractDir>/docker-compose.yml

Purpose

Defines local infrastructure services required for development.

Responsibilities

PostgreSQL container
RabbitMQ container
MinIO container
Network configuration
Volume persistence

Allowed Content

Docker services
Environment variables
Ports mapping

Forbidden Content

Application logic
Business configuration
Domain configuration

---

# 6.3 application.properties

Location

<BackendContractDir>/src/main/resources/application.properties
Purpose

Defines the base application configuration shared across all runtime environments.

This file contains only environment-independent configuration required by the framework.

Responsibilities
Application metadata
HTTP server configuration
Base Quarkus configuration
Framework configuration
Flyway configuration
Hibernate configuration
GraphQL configuration
JWT verification configuration
Environment-independent settings
Global feature toggles
Allowed Content
Application name and version
HTTP host and port
Quarkus framework configuration
Flyway base configuration
Hibernate base configuration
GraphQL configuration
JWT issuer
JWT public key location
Feature flags shared by every environment
Global DevServices configuration
Forbidden Content
Database credentials
Database URLs
RabbitMQ connection settings
MinIO connection settings
SMTP configuration
API keys
Passwords
Secrets
Environment-specific values
Development-only configuration
Testing-only configuration
Production-only configuration
Rules

The file MUST contain only environment-independent configuration.

Runtime-specific configuration MUST be placed in the corresponding profile file.

The file MUST NOT contain credentials.

Quarkus DevServices MUST be disabled when infrastructure is provided externally.

JWT Configuration Rules

The generator MUST always configure JWT verification using the following properties:

mp.jwt.verify.publickey.location=security/publickey.pem
mp.jwt.verify.issuer=<ApplicationName>

where <ApplicationName> MUST match:

quarkus.application.name
Required Resource Structure

When JWT verification is enabled, the generator MUST ensure the following resource exists:

<BackendContractDir>
└── src
    └── main
        └── resources
            └── security
                └── publickey.pem

The public key file MUST be located exactly at:

src/main/resources/security/publickey.pem

The generator MUST NOT place the public key:

directly under src/main/resources/
inside any Java package
inside target/
inside any other directory
Validation Rules

Before generating application.properties, the generator MUST verify that:

src/main/resources/security/ exists
publickey.pem exists
publickey.pem is not empty

If any validation fails:

generation MUST STOP immediately
the generator MUST report the missing resource
the JWT configuration MUST NOT be generated
Property Validation Rules

The generator MUST generate the property exactly as:

mp.jwt.verify.publickey.location=security/publickey.pem

The generator MUST NOT:

omit the .pem extension
use classpath:
prepend /
generate an absolute path
generate a Windows path
generate a Linux path
rename the file
infer another resource location
Expected Configuration Example
quarkus.application.name=platform-core
quarkus.application.version=1.0.0-SNAPSHOT

quarkus.http.host=0.0.0.0
quarkus.http.port=8080

quarkus.flyway.migrate-at-start=true
quarkus.flyway.baseline-on-migrate=true
quarkus.flyway.locations=classpath:db/migration

quarkus.hibernate-orm.database.generation=none

mp.jwt.verify.publickey.location=security/publickey.pem
mp.jwt.verify.issuer=platform-core

quarkus.devservices.enabled=false

---

# 6.4 application-dev.properties (FIXED)

# Location
<BackendContractDir>/src/main/resources/application-dev.properties

Purpose
Defines environment-specific configuration for local development only.

Responsibilities
- Local infrastructure connections
- Development overrides
- Debug configuration

Allowed Content
- PostgreSQL connection
- RabbitMQ explicit channel configuration (NO WILDCARDS)
- MinIO configuration
- Logging configuration
- Localhost endpoints

Forbidden Content
- Wildcard messaging configuration (*)
- Production credentials
- Shared base configuration
- Application metadata
- Framework configuration duplication

---

# 6.5 application-test.properties

Location
<BackendContractDir>/src/main/resources/application-test.properties

Purpose

Testing environment configuration.

Responsibilities

In-memory or test database config
Mock configuration
Isolated execution settings

USE:
quarkus.datasource.jdbc.url=jdbc:postgresql://localhost:5432/platform
quarkus.datasource.username=postgres
quarkus.datasource.password=postgre

---

# 6.6 JWT Key Files (CORREGIDA)
Location
<BackendContractDir>/src/main/resources/security/

<BackendContractDir>/src/main/java/<BasePackage>/platform/security/jwt/JwtKeyGenerator.java
Purpose

Generates and persists RSA cryptographic key pair required by Platform Core for JWT signing and verification.

This artifact ensures deterministic, reproducible and valid cryptographic material compatible with JwtGenerator.

Artifacts
security/publickey.pem
security/privatekey.pem

platform/security/jwt/JwtKeyGenerator.java
Responsibilities
Generate a new RSA key pair (minimum 2048 bits).
Ensure deterministic encoding compatible with Java PKCS#8 format.
Convert private key to PKCS#8 PEM format.
Convert public key to X.509 SubjectPublicKeyInfo PEM format.

Create directory structure if it does not exist:

src/main/resources/security/
Write both PEM files using UTF-8 encoding.
Ensure generated PEM files are readable by standard Java KeyFactory.
Replace existing keys only when explicitly executed.
Key Format Rules (CRITICAL)
Private Key MUST be:
Algorithm: RSA
Format: PKCS#8

PEM header:

-----BEGIN PRIVATE KEY-----

PEM footer:

-----END PRIVATE KEY-----
Public Key MUST be:
Algorithm: RSA
Format: X.509 (SubjectPublicKeyInfo)

PEM header:

-----BEGIN PUBLIC KEY-----

PEM footer:

-----END PUBLIC KEY-----
Encoding Rules (VERY IMPORTANT)

The generator MUST:

Encode keys using Base64.getEncoder()
Insert line breaks every 64 characters
Remove any platform-specific whitespace issues
Ensure no extra characters exist before/after PEM blocks
Ensure output is strict ASCII/UTF-8
Validation Rules (MANDATORY)

Before writing files, the generator MUST:

Reconstruct private key from generated bytes
Validate with KeyFactory.getInstance("RSA")
Fail fast if key is not valid PKCS#8
Never write partially invalid PEM files

If validation fails:

The generator MUST throw a runtime exception and MUST NOT write files.

Allowed Content
RSA key generation logic
Java Security API usage (KeyPairGenerator, KeyFactory)
PEM encoding utilities
File system writing logic
Validation logic
Forbidden Content
Hardcoded keys
Pre-generated PEM strings
Non-RSA algorithms
EC or DSA keys
Incomplete PEM writing
Business logic
JWT logic
Authentication logic
Execution Rule

JwtKeyGenerator MUST be executable as a standalone Java application:

public static void main(String[] args)

It MUST:

generate keypair
validate keys
write files
print success message
Maven Execution Command

At the end of the file, MUST include:

// Execute:
//
// mvn exec:java "-Dexec.mainClass=<BasePackage>.platform.security.jwt.JwtKeyGenerator"

---

# 6.7 JwtGenerator

Location

<BackendContractDir>/src/main/java/<BasePackage>/platform/security/jwt/JwtGenerator.java

---

Purpose

Generates signed JWT access tokens using the RSA private key produced by JwtKeyGenerator.

This class is a Platform Security utility and MUST be usable both:

- as a standalone development tool
- as a reusable component for authentication infrastructure

---

Platform Security Bootstrap Dependency Rule

JwtGenerator is strictly dependent on JwtKeyGenerator.

The class MUST assume that:

- <BackendContractDir>/src/main/resources/security/privatekey.pem
- <BackendContractDir>/src/main/resources/security/publickey.pem

already exist BEFORE execution.

If these files are missing:

- execution MUST FAIL immediately
- a descriptive error MUST be thrown
- the class MUST NOT attempt to generate or repair keys

---

Responsibilities

- Load the RSA private key from application resources
- Validate existence of cryptographic material before usage
- Parse PEM encoded private key (PKCS#8)
- Strip PEM headers and footers
- Remove all whitespace characters
- Validate Base64 integrity of key content
- Build JWT claims
- Configure issuer information
- Configure expiration time
- Sign JWT using RSA private key
- Produce valid JWT token string
- Provide standalone execution entry point

---

Allowed Content

- JWT generation logic
- Private key loading
- PEM parsing and validation
- Base64 decoding
- RSA signing
- Claim construction
- Expiration handling
- Main method for standalone execution

---

Forbidden Content

- Authentication logic
- User validation
- Password handling
- Authorization rules
- HTTP endpoints
- GraphQL operations
- Business logic
- Persistence logic
- Any framework-specific business workflow

---

Rules

The private key MUST always be loaded from:

classpath:security/privatekey.pem

The public key MUST always exist at:

classpath:security/publickey.pem

The class MUST NEVER hardcode cryptographic keys.

The class MUST NOT attempt to generate or modify keys.

The class MUST assume cryptographic material is exclusively produced by JwtKeyGenerator.

---

PEM Processing Rules

Before decoding the private key, the implementation MUST:

- remove "-----BEGIN PRIVATE KEY-----"
- remove "-----END PRIVATE KEY-----"
- remove all whitespace characters
- validate that the remaining content is not empty
- validate that the remaining content is valid Base64

If validation fails:

- throw IllegalArgumentException
- include a clear message:
  "Invalid private key PEM format"

The class MUST NOT attempt to repair malformed input.

---

Execution Requirement

The class MUST expose:

public static void main(String[] args)

which:

- loads the private key
- generates a sample JWT
- prints the result to console

---

Execution Command (must be included at end of file)

// Execute:
//
// mvn exec:java "-Dexec.mainClass=<BasePackage>.platform.security.jwt.JwtGenerator"

Generated Files Rule

This specification defines exactly ONE Java source file.

The generator MUST create only:

<BackendContractDir>/src/main/java/<BasePackage>/platform/security/jwt/JwtGenerator.java

The generator MUST NOT create any additional Java classes related to JWT generation.

The following classes MUST NOT be generated:

- JwtConfig
- JwtConfiguration
- JwtService
- JwtProvider
- JwtFactory
- JwtHelper
- JwtUtils
- Any other supporting JWT class

All required logic MUST be implemented inside JwtGenerator.java.

The implementation MUST be completely self-contained.

JwtGenerator.java MUST contain all logic required for:

- loading the private key
- validating the PEM format
- constructing JWT claims
- signing the token
- standalone execution

The generator MUST NOT create any configuration class because JWT verification is configured exclusively through application.properties.

No CDI producer, configuration bean, helper class, service class, or framework configuration class is required.

JwtConfig.java MUST NOT be generated.


---

# 6.8 Initial Flyway Migration

Locations

The generator MUST create the initial Flyway migration in both locations:

<FlywayDir>/V1__Initial_Schema.sql

<BackendContractDir>/src/main/resources/db/migration/V1__Initial_Schema.sql

Both generated files MUST contain identical SQL.

---

Purpose

Defines the initial database schema used to bootstrap the application database.

The migration is generated from the canonical database schema maintained by the Database Skills layer.

The generated migration is shared by:

- the Database Contract project;
- the Backend Contract project used by Flyway at runtime.

---

Responsibilities

- Load the canonical database schema.
- Generate the initial Flyway migration.
- Preserve the SQL exactly as defined in the source.
- Generate identical migration files for both output locations.
- Establish the baseline database version (V1).

---

Allowed Content

- Pure SQL DDL statements only.
- CREATE TABLE statements.
- CREATE INDEX statements.
- CREATE SEQUENCE statements.
- ALTER TABLE statements.
- Constraint definitions (PK, FK, UNIQUE, CHECK, NOT NULL).

---

Forbidden Content

- Placeholder comments.
- Empty migrations.
- Sample data.
- Mock SQL.
- Business documentation.
- Modified or rewritten SQL.
- Runtime code.

---

Input Rules

The generator MUST load the SQL from:

<SkillsDir>/.agents/database/schema.sql

If this file does NOT exist:

- generation MUST STOP immediately;
- the system MUST report the missing schema source;
- no migration files MUST be generated.

---

Generation Rules

The generator MUST:

- copy the SQL exactly as-is;
- preserve formatting whenever possible;
- NOT modify the SQL;
- NOT reorder SQL statements;
- NOT infer missing SQL;
- NOT optimize SQL;
- NOT generate SQL independently.

The canonical schema file is the single source of truth.

---

Output Rules

The generator MUST create both:

<FlywayDir>/V1__Initial_Schema.sql

and

<BackendContractDir>/src/main/resources/db/migration/V1__Initial_Schema.sql

The generated files MUST:

- be non-empty;
- contain valid SQL;
- preserve the schema source exactly;
- contain identical SQL content;
- differ only by platform-specific line endings, if applicable.

The backend migration is mandatory because Flyway automatically scans:

src/main/resources/db/migration

during application startup.

---

Validation Rules

Before generating output, the generator MUST verify that:

- the schema file exists;
- the schema file is not empty;
- the schema contains non-whitespace SQL.

If validation fails:

- generation MUST STOP;
- neither migration file MUST be created.

---

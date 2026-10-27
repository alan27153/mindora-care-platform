# 1. Name

N8N Workflow Backend Generation Specification

---

# 2. Description

This Specification defines the system-level generation and orchestration rules for building a Quarkus Java backend following Hexagonal Architecture principles.

This Specification generates:
- The domain models, use cases, ports, services, and infrastructure adapters directly derived from the workflow document `workflow-analysis.md`.
- The physical Java and Configuration code files inside the target Quarkus project.

This Specification structures:
- Real project input and output paths based on workspace root `waia-project`.
- Concrete code templates, mapping directives, and file generation specs derived directly from `workflow-analysis.md`.

This Specification does NOT:
- Rely on external sub-skills for execution (executes autonomously directly from this Specification).
- Require manual intervention for code style or architectural setup.

---

# 3. Variables (Specification Inputs)

The following variables map directly to your local file structure and system configuration.

## Core Input Variables
WorkflowAnalysisFile = waia-cn-skills/.agents/docs/workflows/analysis/workflow-analysis.md
DatabaseSchemaFile = waia-cn-skills/.agents/database/schema.sql
ProjectName = waia-cn-backend-chatbot
BasePackageDot = io.joyit.platform
TargetModuleName = chatbot
FullModulePackageDot = io.joyit.platform.modules.chatbot

## Workspace Target Variables
WorkspaceRoot = .
BackendDir = waia-cn-backend-chatbot
FlywayDir = waia-cn-backend-chatbot/src/main/resources/db/migration
ResourcesDir = waia-cn-backend-chatbot/src/main/resources
JavaSourceDir = waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot
AgentsDir = waia-cn-skills/.agents

## System Variables
ArchitectureType = Hexagonal (Ports & Adapters)
ApiStyle = REST / JAX-RS
Framework = Quarkus 3.x
LanguageVersion = Java 17 / 21
Database = PostgreSQL / Reactive Panache (Mutiny)
Messaging = RabbitMQ / SmallRye Reactive Messaging
Storage = MinIO

---

# 4. Execution Mode & Rules

This Specification operates without external Skill dependencies. Generation is executed directly by reading `<WorkflowAnalysisFile>` and targeting `<JavaSourceDir>`.

## Generation Scope & Transformation Rules

1. **Domain Layer (`domain/model` & `domain/exception`):**
   - **Source:** `# Business Logic` and `# Database Usage`.
   - **Rules:** Pure Java Plain Old Java Objects (POJOs) or Enums. Absolutely ZERO external annotations (No Jackson, No Quarkus, No JPA/Panache).

2. **Ports Layer (`port/in` & `port/out`):**
   - **Source:** `# Recommended Use Cases` and `# Recommended Ports`.
   - **Rules:**
     - `port/in`: Java Interfaces defining primary use cases (e.g., `ProcessIncomingMessageUseCase.java`).
     - `port/out`: Java Interfaces defining SPIs for external communication or persistence (e.g., `WahaNotificationPort.java`, `DatabasePersistencePort.java`). Mutiny Reactive types (`Uni<T>`, `Multi<T>`) are allowed for async operations.

3. **Application Layer (`application/service` & `application/dto`):**
   - **Source:** `# Variables`, `# Expressions`, and business rules.
   - **Rules:**
     - `dto`: Standard Java Records (`record`) representing request/response/internal payloads.
     - `service`: Implementation of `port/in` interfaces. Annotated with `@ApplicationScoped`. Orchestrates calls to `port/out`.

4. **Infrastructure Layer (`infrastructure/adapter`):**
   - **Source:** `# Recommended Adapters` and `# External Services`.
   - **Rules:**
     - `adapter/in/rest`: JAX-RS Controllers (`@Path`, `@POST`, `@GET`) implementing incoming endpoints.
     - `adapter/out/persistence`: Reactive Panache Entities (`PanacheEntityBase`) and Repository implementations (`PanacheRepositoryBase`) implementing `port/out`.
     - `adapter/out/http`: MicroProfile REST Clients (`@RegisterRestClient`) mapping external APIs (e.g., WAHA).

5. **Configuration (`resources`):**
   - **Source:** `# Recommended Configuration`.
   - **Rules:** Key-value pairs appended to `<ResourcesDir>/application.properties`.

---

# 5. Workspace Structure

The physical target directory layout where artifacts will be created:

```text
waia-project/
├── waia-cn-skills/
│   └── .agents/
│       ├── database/
│       │   └── schema.sql
│       └── docs/
│           └── workflows/
│               └── analysis/
│                   └── workflow-analysis.md    <-- [SOURCE FILE]
│
└── waia-cn-backend-chatbot/                    <-- [TARGET PROJECT]
    ├── pom.xml
    ├── docker-compose.yml
    └── src/
        └── main/
            ├── java/
            │   └── io/joyit/platform/
            │       ├── configuration/
            │       ├── exception/
            │       ├── messaging/
            │       ├── security/
            │       ├── shared/
            │       ├── storage/
            │       └── modules/
            │           └── chatbot/            <-- [GENERATED MODULE TARGET]
            │               ├── domain/
            │               │   ├── model/
            │               │   └── exception/
            │               ├── port/
            │               │   ├── in/
            │               │   └── out/
            │               ├── application/
            │               │   ├── service/
            │               │   └── dto/
            │               └── infrastructure/
            │                   └── adapter/
            │                       ├── in/
            │                       │   └── rest/
            │                       └── out/
            │                           ├── persistence/
            │                           ├── http/
            │                           └── ai/
            └── resources/
                ├── application.properties
                └── db/
                    └── migration/
                        └── V1__Initial_Schema.sql
6. Output Artifacts & File Contents
This section explicitly defines the structure and Java template patterns to be generated for each layer based on <WorkflowAnalysisFile>.

A. Domain Models (domain/model/*)
Package: io.joyit.platform.modules.chatbot.domain.model

Template Spec:

Java
package io.joyit.platform.modules.chatbot.domain.model;

public class ChatMessage {
    private String id;
    private String senderId;
    private String content;
    private String status;

    public ChatMessage() {}

    public ChatMessage(String id, String senderId, String content, String status) {
        this.id = id;
        this.senderId = senderId;
        this.content = content;
        this.status = status;
    }

    // Getters, Setters, and Pure Business Logic methods
}
B. Ports (port/in/* & port/out/*)
In-Port Package: io.joyit.platform.modules.chatbot.port.in

Out-Port Package: io.joyit.platform.modules.chatbot.port.out

Template Spec (In-Port):

Java
package io.joyit.platform.modules.chatbot.port.in;

import io.smallrye.mutiny.Uni;
import io.joyit.platform.modules.chatbot.application.dto.ProcessMessageCommand;

public interface ProcessIncomingMessageUseCase {
    Uni<Void> execute(ProcessMessageCommand command);
}
Template Spec (Out-Port):

Java
package io.joyit.platform.modules.chatbot.port.out;

import io.smallrye.mutiny.Uni;
import io.joyit.platform.modules.chatbot.domain.model.ChatMessage;

public interface DatabasePersistencePort {
    Uni<ChatMessage> saveMessage(ChatMessage message);
}
C. Application Layer (application/dto/* & application/service/*)
DTO Package: io.joyit.platform.modules.chatbot.application.dto

Service Package: io.joyit.platform.modules.chatbot.application.service

Template Spec (DTO Record):

Java
package io.joyit.platform.modules.chatbot.application.dto;

public record ProcessMessageCommand(
    String sender,
    String message,
    String channel
) {}
Template Spec (Application Service):

Java
package io.joyit.platform.modules.chatbot.application.service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import io.smallrye.mutiny.Uni;
import io.joyit.platform.modules.chatbot.port.in.ProcessIncomingMessageUseCase;
import io.joyit.platform.modules.chatbot.port.out.DatabasePersistencePort;
import io.joyit.platform.modules.chatbot.application.dto.ProcessMessageCommand;

@ApplicationScoped
public class ProcessIncomingMessageService implements ProcessIncomingMessageUseCase {

    private final DatabasePersistencePort persistencePort;

    @Inject
    public ProcessIncomingMessageService(DatabasePersistencePort persistencePort) {
        this.persistencePort = persistencePort;
    }

    @Override
    public Uni<Void> execute(ProcessMessageCommand command) {
        // Business workflow sequence derived from workflow-analysis.md
        return Uni.createFrom().voidItem();
    }
}
D. Infrastructure REST Adapters (infrastructure/adapter/in/rest/*)
Package: io.joyit.platform.modules.chatbot.infrastructure.adapter.in.rest

Template Spec:

Java
package io.joyit.platform.modules.chatbot.infrastructure.adapter.in.rest;

import jakarta.ws.rs.*;
import jakarta.ws.rs.core.MediaType;
import jakarta.ws.rs.core.Response;
import jakarta.inject.Inject;
import io.smallrye.mutiny.Uni;
import io.joyit.platform.modules.chatbot.port.in.ProcessIncomingMessageUseCase;
import io.joyit.platform.modules.chatbot.application.dto.ProcessMessageCommand;

@Path("/api/v1/chatbot")
@Produces(MediaType.APPLICATION_JSON)
@Consumes(MediaType.APPLICATION_JSON)
public class ChatWebhookResource {

    private final ProcessIncomingMessageUseCase processUseCase;

    @Inject
    public ChatWebhookResource(ProcessIncomingMessageUseCase processUseCase) {
        this.processUseCase = processUseCase;
    }

    @POST
    @Path("/webhook")
    public Uni<Response> handleWebhook(ProcessMessageCommand command) {
        return processUseCase.execute(command)
                .map(unused -> Response.ok().build());
    }
}
E. Infrastructure Persistence Adapters (infrastructure/adapter/out/persistence/*)
Package: io.joyit.platform.modules.chatbot.infrastructure.adapter.out.persistence

Template Spec (Panache Entity & Repository Adapter):

Java
package io.joyit.platform.modules.chatbot.infrastructure.adapter.out.persistence;

import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import io.quarkus.hibernate.reactive.panache.PanacheEntity;
import jakarta.enterprise.context.ApplicationScoped;
import io.smallrye.mutiny.Uni;
import io.quarkus.hibernate.reactive.panache.PanacheRepository;
import io.joyit.platform.modules.chatbot.port.out.DatabasePersistencePort;
import io.joyit.platform.modules.chatbot.domain.model.ChatMessage;

@Entity
@Table(name = "chat_messages")
public class ChatMessageEntity extends PanacheEntity {
    public String senderId;
    public String content;
    public String status;
}

@ApplicationScoped
public class ClientRepositoryAdapter implements DatabasePersistencePort, PanacheRepository<ChatMessageEntity> {

    @Override
    public Uni<ChatMessage> saveMessage(ChatMessage message) {
        ChatMessageEntity entity = new ChatMessageEntity();
        entity.senderId = message.getSenderId();
        entity.content = message.getContent();
        entity.status = message.getStatus();

        return this.persist(entity)
                .map(e -> new ChatMessage(e.id.toString(), e.senderId, e.content, e.status));
    }
}
F. Infrastructure HTTP Rest Clients (infrastructure/adapter/out/http/*)
Package: io.joyit.platform.modules.chatbot.infrastructure.adapter.out.http

Template Spec:

Java
package io.joyit.platform.modules.chatbot.infrastructure.adapter.out.http;

import org.eclipse.microprofile.rest.client.inject.RegisterRestClient;
import jakarta.ws.rs.*;
import jakarta.ws.rs.core.MediaType;
import io.smallrye.mutiny.Uni;

@RegisterRestClient(configKey = "waha-api")
@Path("/api")
public interface N8nWahaRestClient {

    @POST
    @Path("/sendText")
    @Consumes(MediaType.APPLICATION_JSON)
    Uni<Void> sendMessage(Object payload);
}
G. Configuration Property Output
Target File: waia-cn-backend-chatbot/src/main/resources/application.properties

Appended Properties:

Properties
# Appended by Chatbot Backend Generation Spec
%dev.quarkus.datasource.db-kind=postgresql
%dev.quarkus.datasource.reactive.url=postgresql://localhost:5432/chatbot_db
waha-api/mp-rest/url=http://localhost:3000
7. Generation Flow
This section outlines the exact sequential execution phases for reading the workflow and emitting the Java files.

Plaintext
Phase 1: Source Verification
  ├── Open and validate existence of <WorkflowAnalysisFile>
  └── Extract nodes labeled "MIGRA AL BACKEND" from # Migration Decisions

Phase 2: Domain & Ports Generation
  ├── Read # Business Logic & # Database Usage from analysis
  ├── Write pure Java classes to waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot/domain/model/
  ├── Write Use Case Interfaces to waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot/port/in/
  └── Write SPI Interfaces to waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot/port/out/

Phase 3: Application Layer Generation
  ├── Read # Variables and # Recommended Use Cases
  ├── Write Request/Response Records to waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot/application/dto/
  └── Write Quarkus @ApplicationScoped Services implementing in-ports to application/service/

Phase 4: Infrastructure Layer Generation
  ├── Read # Recommended Adapters & # External Services
  ├── Write JAX-RS Resources to infrastructure/adapter/in/rest/
  ├── Write Panache Entities & Repositories to infrastructure/adapter/out/persistence/
  └── Write MicroProfile REST Clients to infrastructure/adapter/out/http/

Phase 5: Configuration & Validation
  ├── Append environment variables from # Recommended Configuration to application.properties
  ├── Verify package statements match package io.joyit.platform.modules.chatbot.*
  └── Verify zero circular dependencies exist between Hexagonal packages

---

### Cambios principales realizados en la especificación:
1. **Definición limpia de rutas:** `JavaSourceDir` ahora apunta explícitamente a `waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot`.
2. **Corrección de Paquetes:** Se unificaron los imports y declaraciones de paquete usando exactamente `io.joyit.platform.modules.chatbot.*`.
3. **Mantenimiento de Nombres:** Se ajustaron las referencias para alinearse con los archivos reales de tu proyecto (como `ProcessIncomingMessageUseCase`, `ChatWebhookResource` y `waia-cn-backend-chatbot`)
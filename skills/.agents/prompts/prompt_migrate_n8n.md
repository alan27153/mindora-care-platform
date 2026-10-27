Load and analyze:

1. waia-cn-skills/.agents/specifications/backend-generation/SPEC.md
2. waia-cn-skills/.agents/docs/workflows/analysis/workflow-analysis.md

TASK:

Generate the backend code for the module target defined in SPEC.md, strictly following Hexagonal Architecture (Ports & Adapters) and Quarkus standards.

The SPEC.md and workflow-analysis.md files are the single source of truth.

RULES:

* Read variables and target paths strictly from SPEC.md.
* Read domain logic, ports, use cases, and adapters strictly from workflow-analysis.md.
* Follow the target package structure: io.joyit.platform.modules.chatbot
* Respect Hexagonal Architecture boundaries:
  - domain: Pure Java, ZERO framework/Quarkus dependencies.
  - port: Interfaces for input (Use Cases) and output (Repositories/Clients).
  - application: Quarkus @ApplicationScoped service implementations and Java Record DTOs.
  - infrastructure: JAX-RS controllers, Panache repositories, REST clients.
* Do not invent business logic not specified in workflow-analysis.md.
* Do not invent external integrations or endpoints not documented in the analysis.
* Do not execute runtime commands or build commands (e.g., mvn, ./mvnw).
* If required information is missing or ambiguous, stop immediately and report it.

OUTPUT SEQUENCE:

Phase 1 - Context & Mapping Summary
* List identified Use Cases, Ports, Adapters, and Models extracted from workflow-analysis.md.

Phase 2 - Target Directory Tree
* Display the physical directory layout to be populated under:
  waia-cn-backend-chatbot/src/main/java/io/joyit/platform/modules/chatbot/

Phase 3 - Code Generation
* Generate code in 4 clear, sequential sub-steps:
  3.1 Domain Layer (Models, Value Objects, Domain Exceptions)
  3.2 Ports Layer (in/ports and out/ports)
  3.3 Application Layer (Use Case Services & DTOs)
  3.4 Infrastructure Layer (JAX-RS Rest Resources, Panache Repositories, REST Clients)

Phase 4 - Configuration & Environment Updates
* Show the key-value properties to append to application.properties.

Phase 5 - Validation Checklist
* Verify zero direct framework imports in domain model classes.
* Verify proper package declarations (io.joyit.platform.modules.chatbot.*).
* Verify non-blocking/reactive annotations if applicable.

FINAL RULE:

If any hexagonal rule or import boundary is violated during generation, stop, report the violation, and fix it before outputting the final files.

CREATE TABLE client (
    id BIGSERIAL PRIMARY KEY,
    phone VARCHAR(20) UNIQUE,
    ip VARCHAR(45),
    channel VARCHAR(30) NOT NULL,
    country VARCHAR(100),
    name VARCHAR(150),
    dni VARCHAR(20),
    gmail VARCHAR(255),
    failed_attempts INT DEFAULT 0,
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_until TIMESTAMP,
    state VARCHAR(30) DEFAULT 'NORMAL',
    created_at TIMESTAMP DEFAULT NOW(),
    last_interaction TIMESTAMP
);

CREATE TABLE advisor (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR NOT NULL,
    phone VARCHAR NOT NULL,
    email VARCHAR,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE documents (
    id BIGSERIAL PRIMARY KEY,
    content TEXT,
    metadata JSONB,
    embedding vector(1536)
);

CREATE TABLE ticket (
    id BIGSERIAL PRIMARY KEY,

    client_id BIGINT,
    inquiry_id BIGINT,
    advisor_id BIGINT,

    description TEXT,

    status VARCHAR DEFAULT 'OPEN',

    created_at TIMESTAMP DEFAULT NOW(),

    closed_at TIMESTAMP
);

CREATE TABLE inquiry (

    id BIGSERIAL PRIMARY KEY,

    client_id BIGINT,

    question TEXT,

    answer TEXT,

    intent VARCHAR,

    category VARCHAR,

    ticket_created BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT NOW()

);

CREATE TABLE conversation_metric (

    id BIGSERIAL PRIMARY KEY,

    client_id BIGINT,

    day DATE NOT NULL,

    prompt_tokens INT DEFAULT 0,

    completion_tokens INT DEFAULT 0,

    total_tokens INT DEFAULT 0,

    messages INT DEFAULT 0,

    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE(client_id,day)

);

CREATE TABLE conversation_analysis (

    id BIGSERIAL PRIMARY KEY,

    client_id BIGINT,

    sentiment VARCHAR,

    summary TEXT,

    recommendation TEXT,

    created_at TIMESTAMP DEFAULT NOW()

);

CREATE TABLE n8n_chat_histories (

    id SERIAL PRIMARY KEY,

    session_id VARCHAR NOT NULL,

    message JSONB NOT NULL

);
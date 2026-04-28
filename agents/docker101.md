### Gemini

```code
quando executo com docker composer ud -d recebo erro, resolva isso e documente em agents/docker101.md

mensagem de erro:
failed to solve: process "/bin/sh -c docker-php-ext-install     pdo_mysql     zip     mbstring     json" did not complete successfully: exit code: 2
```

# Docker 101: Troubleshooting & PHP 8.2 Setup

## 1. Erro de Permissão (Docker Socket)
**Sintoma:** `permission denied while trying to connect to the docker API`.
**Causa:** O usuário atual não tem permissão para acessar o socket do Docker (`/var/run/docker.sock`).
**Solução:**
1. Adicione o usuário ao grupo docker: `sudo usermod -aG docker $USER`
2. Reinicie a sessão (logout/login) ou execute `newgrp docker`.

---

## 2. Erro de Compilação no Dockerfile (Exit Code 2)
**Sintoma:** `failed to solve: process "/bin/sh -c docker-php-ext-install ... json" did not complete successfully`.

### Problemas Identificados:
* **Extensão `json`:** Desde o PHP 8.0, a extensão JSON é parte integrante do PHP e não pode mais ser instalada via `docker-php-ext-install`. Tentar instalá-la causa falha no build.
* **Extensão `mbstring`:** Em imagens Alpine recentes, as dependências para mbstring mudaram. Se o binário base já a possui, a tentativa de recompilação sem flags específicas de biblioteca pode falhar.

### Dockerfile Otimizado para PHP 8.2:
```dockerfile
FROM php:8.2-fpm-alpine

# 1. Dependências de sistema
RUN apk add --no-cache curl git unzip zip libzip-dev oniguruma-dev

# 2. Extensões PHP
# Remova 'json' e 'mbstring' da lista de instalação manual
RUN docker-php-ext-install pdo_mysql zip

# 3. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
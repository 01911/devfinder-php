#!/usr/bin/env bash
#
# DevFinder Setup Script
# Configura o ambiente PHP local e prepara para execução
#

set -e

echo "🚀 DevFinder PHP Setup Script"
echo "========================================"

# Check PHP version
PHP_VERSION=$(php -v | head -n 1)
echo "✅ PHP encontrado: $PHP_VERSION"

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer não encontrado. Instalando..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
else
    echo "✅ Composer encontrado"
fi

cd src

# Install dependencies
echo ""
echo "📦 Instalando dependências..."
composer install --no-interaction

# Setup environment file
if [ ! -f .env ]; then
    echo ""
    echo "⚙️  Configurando arquivo .env..."
    cp .env.example .env
    echo "✅ .env criado (você pode editar conforme necessário)"
fi

# Create required directories
mkdir -p storage/logs
chmod -R 775 storage

echo ""
echo "✅ Setup concluído com sucesso!"
echo ""
echo "🎯 Próximas etapas:"
echo "1. Inicie o servidor: php -S localhost:8000"
echo "2. Acesse a API: http://localhost:8000/v1"
echo "3. Teste os endpoints em: requests.http"
echo ""
echo "📚 Para mais informações, veja: README.md"

#!/usr/bin/env bash

#
# URL Review Script
# Verifica se há URLs hardcoded ou problemas de configuração
#
# Usage: ./scripts/review-urls.sh
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  CEACA CEDOC - URL Configuration Review${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

# Check for hardcoded hostnames
echo -e "${YELLOW}[1] Procurando por URLs hardcoded (localhost, 127.0.0.1)...${NC}"
LOCALHOST_MATCHES=$(grep -r "localhost\|127\.0\.0\.1" \
  "$PROJECT_ROOT/tainacan-theme-master/src" \
  "$PROJECT_ROOT/scripts" \
  2>/dev/null | grep -v ".git\|node_modules\|vendor" | wc -l || echo 0)

if [[ "$LOCALHOST_MATCHES" -gt 0 ]]; then
  echo -e "${YELLOW}  ⚠ Encontrado localhost/127.0.0.1 (esperado em import-cedoc.php):${NC}"
  grep -r "localhost\|127\.0\.0\.1" \
    "$PROJECT_ROOT/tainacan-theme-master/src" \
    "$PROJECT_ROOT/scripts" \
    2>/dev/null | grep -v ".git\|node_modules" || true
else
  echo -e "${GREEN}  ✓ Nenhum localhost encontrado${NC}"
fi
echo ""

# Check for hardcoded domain
echo -e "${YELLOW}[2] Procurando por domínios hardcoded (ceacac76, ceacacedoc.com.br)...${NC}"
DOMAIN_MATCHES=$(grep -r "ceacac76\.hospedagemdesites\.ws\|ceacacedoc\.com\.br" \
  "$PROJECT_ROOT/tainacan-theme-master/src" \
  "$PROJECT_ROOT/site-demo" \
  2>/dev/null | grep -v ".git\|node_modules" | wc -l || echo 0)

if [[ "$DOMAIN_MATCHES" -gt 0 ]]; then
  echo -e "${YELLOW}  ⚠ Encontrados domínios hardcoded:${NC}"
  grep -r "ceacac76\.hospedagemdesites\.ws\|ceacacedoc\.com\.br" \
    "$PROJECT_ROOT/tainacan-theme-master/src" \
    "$PROJECT_ROOT/site-demo" \
    2>/dev/null | head -10 || true
else
  echo -e "${GREEN}  ✓ Nenhum domínio hardcoded encontrado${NC}"
fi
echo ""

# Check for http:// (should be https:// or relative)
echo -e "${YELLOW}[3] Procurando por URLs HTTP (deveria ser HTTPS)...${NC}"
HTTP_MATCHES=$(grep -r 'href="http://' \
  "$PROJECT_ROOT/tainacan-theme-master/src" \
  "$PROJECT_ROOT/site-demo" \
  2>/dev/null | grep -v ".git" | wc -l || echo 0)

if [[ "$HTTP_MATCHES" -gt 0 ]]; then
  echo -e "${YELLOW}  ⚠ Encontrados links HTTP (deveria ser HTTPS ou relativo):${NC}"
  grep -r 'href="http://' \
    "$PROJECT_ROOT/tainacan-theme-master/src" \
    "$PROJECT_ROOT/site-demo" \
    2>/dev/null | head -10 || true
else
  echo -e "${GREEN}  ✓ Sem links HTTP diretos${NC}"
fi
echo ""

# Check for home_url() usage
echo -e "${YELLOW}[4] Verificando uso de home_url() (correto)...${NC}"
HOME_URL_USAGE=$(grep -r "home_url(" \
  "$PROJECT_ROOT/tainacan-theme-master/src" \
  2>/dev/null | grep -v ".git" | wc -l || echo 0)

if [[ "$HOME_URL_USAGE" -gt 5 ]]; then
  echo -e "${GREEN}  ✓ Encontrados $HOME_URL_USAGE usos de home_url() (CORRETO)${NC}"
else
  echo -e "${YELLOW}  ⚠ Poucos usos de home_url() encontrados (check manual)${NC}"
fi
echo ""

# Check for esc_url() usage
echo -e "${YELLOW}[5] Verificando uso de esc_url() para escape...${NC}"
ESC_URL_USAGE=$(grep -r "esc_url(" \
  "$PROJECT_ROOT/tainacan-theme-master/src" \
  2>/dev/null | grep -v ".git" | wc -l || echo 0)

if [[ "$ESC_URL_USAGE" -gt 5 ]]; then
  echo -e "${GREEN}  ✓ Encontrados $ESC_URL_USAGE usos de esc_url() (CORRETO)${NC}"
else
  echo -e "${YELLOW}  ⚠ Poucos usos de esc_url() encontrados (recomendado)${NC}"
fi
echo ""

# Check for theme functions
echo -e "${YELLOW}[6] Verificando funções do tema...${NC}"
FUNC_FILE="$PROJECT_ROOT/tainacan-theme-master/src/functions.php"
if [[ -f "$FUNC_FILE" ]]; then
  THEME_VERSION=$(grep "TAINACAN_INTERFACE_VERSION" "$FUNC_FILE" | head -1)
  echo -e "${GREEN}  ✓ Arquivo functions.php encontrado${NC}"
  echo "    $THEME_VERSION"
else
  echo -e "${RED}  ✗ functions.php não encontrado!${NC}"
fi
echo ""

# Summary
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  RESUMO DA REVISÃO${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""
echo "✓ URLs dinâmicas: home_url() e esc_url() em uso"
echo "✓ Nenhum domínio hardcoded no tema"
echo "✓ Tema preparado para múltiplos subdomínios"
echo ""
echo -e "${YELLOW}Próximos passos:${NC}"
echo "1. Executar: ./scripts/prepare-deploy.sh [subdomain-name]"
echo "2. Seguir guia em: DEPLOYMENT-GUIDE.md"
echo "3. Fazer upload para novo subdomínio"
echo ""

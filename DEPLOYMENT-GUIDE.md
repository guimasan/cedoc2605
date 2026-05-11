# GUIA DE DEPLOYMENT - CEACA CEDOC para Hostgator

## 📋 Resumo Executivo

Este guia descreve como fazer upload do site CEACA CEDOC para um novo subdomínio no servidor Hostgator.

**Informações Importantes:**
- **Domínio**: ceacac76.hospedagemdesites.ws
- **Backup atual**: backup-5.5.2026_11-17-33_ceacac76.tar.gz
- **Banco de dados**: ceacac76_wp56.sql.gz
- **Tema**: tainacan-interface (customizado)
- **Versão WordPress**: 5.6
- **PHP requerido**: 7.4+ (recomendado 8.0+)

---

## 🚀 Passo 1: Preparar Ambiente no Hostgator

### 1.1 Criar Novo Subdomínio

1. Acesse cPanel do Hostgator
2. Vá para **Addon Domains** ou **Subdomains**
3. Crie novo subdomínio, ex: `cedoc-nova.ceacac76.hospedagemdesites.ws`
4. Documento Root: `/public_html/cedoc-nova` (criar pasta se não existir)
5. Confirme criação

**Aguarde 5-15 minutos para propagação de DNS**

### 1.2 Criar Banco de Dados

1. No cPanel, vá para **MySQL Databases**
2. Crie novo banco de dados:
   - Nome: `ceacac76_cedoc_nova` (ou similar)
   - Anotam o nome exato
3. Vá para **MySQL Users**
4. Crie novo usuário:
   - Usuário: `ceacac76_cedoc_user` (ou similar)
   - Senha: *escolha uma senha forte*
   - Anotam credentials
5. Atribua o usuário ao banco com **ALL PRIVILEGES**

**Salve em lugar seguro:**
```
Database Name: [seu-bd-aqui]
Database User: [seu-usuario-aqui]
Database Password: [sua-senha-aqui]
Database Host: localhost
```

### 1.3 Obter Credenciais FTP

1. No cPanel, vá para **FTP Accounts**
2. Anote as credenciais de FTP principal ou crie nova conta
3. Server: ftp.ceacac76.hospedagemdesites.ws
4. Usuário e Senha: *anotam*

---

## 📤 Passo 2: Preparar Arquivos Localmente

### 2.1 Executar Script de Preparação

```bash
cd /home/surya/Área\ de\ trabalho/CEACA/CEDOC/cedoc2605
./scripts/prepare-deploy.sh cedoc-nova
```

Isso criará pasta `.deploy/` com:
- Tema Tainacan customizado
- Scripts de deployment
- Documentação

### 2.2 Preparar Backup do Banco de Dados

O arquivo `ceacac76_wp56.sql.gz` já existe no diretório raiz.

**Você vai precisar de:**
1. `backup-5.5.2026_11-17-33_ceacac76.tar.gz` (backup completo) - extrair apenas:
   - `mysql/ceacac76_wp56.sql.gz` (banco de dados)
   - `homedir/public_html/wp-config.php` (configurações)

**Ou use cPanel Backup:**
1. No cPanel, vá para **Backup** (ou **Full Backup**)
2. Encontre o backup mais recente
3. Restaure em novo subdomínio (se disponível)

---

## 📁 Passo 3: Upload de Arquivos via FTP

### 3.1 Estrutura de Upload

```
Destino: public_html/cedoc-nova/
Fazer upload dos seguintes:
├── wp-config.php              ← CRÍTICO (atualizar credentials)
├── wp-admin/                  ← Do backup original
├── wp-includes/               ← Do backup original
├── wp-content/
│   ├── themes/
│   │   └── tainacan-interface/  ← Tema customizado
│   ├── plugins/               ← Plugins necessários
│   └── uploads/               ← (opcional, para arquivos anteriores)
├── .htaccess                  ← Se tiver rewrite rules
└── [outros arquivos]          ← wp-load.php, index.php, etc
```

### 3.2 Usar FileZilla ou Similar

1. Abra FileZilla (ou similar)
2. Conecte com credenciais FTP
3. Navegue para: `/public_html/cedoc-nova/`
4. Faça upload:
   - **Primeiro**: wp-config.php (raiz do subdomínio)
   - **Depois**: wp-content/themes/tainacan-interface/
   - **Depois**: demais pastas

**Dicas:**
- Compresse arquivos para upload mais rápido
- Depois descompacte via File Manager do cPanel

### 3.3 Verificar Permissões de Arquivo

No File Manager do cPanel:
1. Selecione cada pasta/arquivo
2. Clique direito → **Change Permissions**
3. Defina:
   - **Directories**: 755
   - **Files**: 644
   - **wp-config.php**: 600 (mais seguro)

---

## 🗄️ Passo 4: Restaurar Banco de Dados

### 4.1 Extrair SQL do Backup

Se usando arquivo `.tar.gz`:

```bash
# Extrair apenas o SQL
tar -xzf backup-5.5.2026_11-17-33_ceacac76.tar.gz \
  -C /tmp \
  "*/mysql/ceacac76_wp56.sql.gz"

# Descompactar
gunzip /tmp/*/mysql/ceacac76_wp56.sql.gz

# Localizar arquivo
find /tmp -name "ceacac76_wp56.sql" -type f
```

### 4.2 Importar via phpMyAdmin (Recomendado)

1. Acesse cPanel → **phpMyAdmin**
2. Selecione **banco de dados novo** na esquerda
3. Clique abas: **Import**
4. Escolha arquivo: `ceacac76_wp56.sql` (ou `.gz`)
5. Clique **Go** e aguarde importação

**Esperado:** Tabelas começando com `wp_` aparecem

### 4.3 Importar via SSH (Alternativo)

```bash
# Se tiver acesso SSH
mysql -h localhost -u [DB_USER] -p [DB_NAME] < ceacac76_wp56.sql

# Ou descompactar e importar:
gunzip < ceacac76_wp56.sql.gz | mysql -h localhost -u [DB_USER] -p [DB_NAME]
```

---

## ⚙️ Passo 5: Configurar WordPress

### 5.1 Atualizar wp-config.php

Via File Manager ou SSH, edite `public_html/cedoc-nova/wp-config.php`:

```php
// Encontre e atualize:
define('DB_NAME', 'seu-novo-database');    // ← novo BD
define('DB_USER', 'seu-novo-usuario');     // ← novo usuário
define('DB_PASSWORD', 'sua-nova-senha');   // ← nova senha
define('DB_HOST', 'localhost');            // geralmente localhost
```

### 5.2 Atualizar URLs do Site

**Opção A: Via phpMyAdmin (seguro)**

1. phpMyAdmin → selecione banco → aba **SQL**
2. Execute comando:
```sql
UPDATE wp_options 
SET option_value='https://cedoc-nova.ceacac76.hospedagemdesites.ws' 
WHERE option_name IN ('siteurl', 'home');
```

3. Clique **Go**

**Opção B: Via SSH (se tiver WP-CLI)**

```bash
wp option update siteurl "https://cedoc-nova.ceacac76.hospedagemdesites.ws"
wp option update home "https://cedoc-nova.ceacac76.hospedagemdesites.ws"
```

**Opção C: Via painel WordPress admin**

1. Acesse: `https://cedoc-nova.ceacac76.hospedagemdesites.ws/wp-admin`
2. Login com credenciais do backup
3. **Settings** → **General**
4. Atualize:
   - WordPress Address: `https://cedoc-nova.ceacac76.hospedagemdesites.ws`
   - Site Address: `https://cedoc-nova.ceacac76.hospedagemdesites.ws`
5. **Save Changes**

### 5.3 Resetar Senha de Admin (Se Necessário)

Via phpMyAdmin:

```sql
-- Primeiro, copie o hash de senha do usuário existente
SELECT user_login, user_pass FROM wp_users LIMIT 1;

-- Ou crie nova senha (usando hash MD5 + salto é inseguro)
-- Melhor usar WP-CLI ou painel admin
```

Ou via SSH com WP-CLI:
```bash
wp user update admin --prompt=user_pass
```

---

## ✅ Passo 6: Verificar Instalação

### 6.1 Teste de Acesso

1. Abra navegador
2. Visite: `https://cedoc-nova.ceacac76.hospedagemdesites.ws`
3. **Esperado**: Página inicial do CEDOC carrega normalmente

### 6.2 Teste de Admin

1. Visite: `https://cedoc-nova.ceacac76.hospedagemdesites.ws/wp-admin`
2. Faça login com credenciais originais
3. **Esperado**: Painel WordPress abre, com coleções e itens visíveis

### 6.3 Teste de Funcionalidades

- [ ] Homepage carrega
- [ ] Menu navegação funciona
- [ ] Collections (Acervo) acessíveis
- [ ] Busca funciona
- [ ] Páginas internas carregam
- [ ] Imagens/mídias aparecem
- [ ] Links de compartilhamento funcionam
- [ ] 404 page funciona (teste URL falsa)

### 6.4 Teste de Console/Debug

Ativar debug se algo não funcionar:

**wp-config.php:**
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Erros aparecerão em: `/wp-content/debug.log`

---

## 🔒 Passo 7: Segurança & Manutenção

### 7.1 Ativar HTTPS/SSL

1. cPanel → **AutoSSL** ou **SSL/TLS**
2. Ativar AutoSSL (geralmente já está)
3. Certificado Let's Encrypt gratuito
4. Aguarde propagação (minutos a horas)
5. Teste: `https://cedoc-nova...` deve carregar com cadeado verde

### 7.2 Criar Backup Imediato

1. cPanel → **Backup**
2. Crie backup completo do novo subdomínio
3. Guarde em local seguro

### 7.3 Desabilitar Acesso a wp-config.php

**Via .htaccess** (se não existir, crie em raiz):

```apache
<files wp-config.php>
order allow,deny
deny from all
</files>
```

### 7.4 Mudar Prefixo de Banco (Recomendado)

Via phpMyAdmin, renomeie tabelas de `wp_` para `wpii_` ou similar (opcional, segurança por obscuridade).

---

## 🐛 Troubleshooting

### Problema: "Erro ao estabelecer conexão com BD"

**Soluções:**
1. Verifique credenciais em wp-config.php
2. phpMyAdmin → teste conexão com mesmas credenciais
3. Certifique-se que usuário foi atribuído ao banco
4. Se host não é localhost, tente 127.0.0.1

### Problema: "Página em branco" (White Screen of Death)

**Soluções:**
1. Ative debug em wp-config.php
2. Verifique `/wp-content/debug.log`
3. Testes comuns:
   - PHP version compatível (7.4+)
   - Memory limit suficiente (64MB+)
   - Arquivo não corrompido durante upload

```bash
# Via SSH, teste PHP
php -v
php -m | grep mysql  # deve conter "mysqli"
```

### Problema: "Tema não carrega" ou "CSS/JS quebrados"

**Soluções:**
1. Verifique permissões da pasta tema (755)
2. Cheque estrutura: `wp-content/themes/tainacan-interface/`
3. No admin, **Appearance** → **Themes**, ative tema
4. Limpe cache do navegador (Ctrl+Shift+Del)

### Problema: "Imagens não aparecem"

**Soluções:**
1. Upload `/wp-content/uploads/` foi completado?
2. Permissões em 755?
3. No admin, **Settings** → **Permalinks**, clique **Save Changes**

### Problema: "Permalink/URL rewrite não funciona"

**Soluções:**
1. Verifique `.htaccess` existe em raiz
2. Apache mod_rewrite ativado (geralmente está em Hostgator)
3. Via SSH: `a2enmod rewrite` (se tiver acesso)
4. No WordPress admin: **Settings** → **Permalinks** → **Save Changes**

---

## 📞 Suporte & Recursos

- **Hostgator Docs**: https://www.hostgator.com.br/suporte/
- **WordPress Docs**: https://wordpress.org/support/
- **Tainacan**: https://tainacan.org/suporte/
- **Erro MySQL**: https://dev.mysql.com/doc/ 

---

## ✨ Próximos Passos Após Deploy

1. **Atualizações**: Verifique updates de plugins/tema (com cautela)
2. **Performance**: Instale cache plugin (WP Super Cache, W3TC)
3. **SEO**: Configure plugin SEO (Yoast, RankMath)
4. **Backup Automático**: Configure backups automáticos via cPanel ou plugin
5. **Monitoramento**: Configure alertas de erro no painel

---

**Última atualização**: 11 de maio de 2026
**Versão guia**: 1.0

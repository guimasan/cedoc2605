README - Empacotamento para Hostgator

Instruções rápidas para subir o site no Hostgator como subdomínio:

1. Crie um subdomínio (ex.: subdominio.seudominio.com) no painel da Hostgator.
2. No painel WordPress do subdomínio (ou instale o WordPress na pasta do subdomínio), instale o tema presente no arquivo gerado `cedoc-hostgator-package.zip` → pasta `tainacan-theme-master`.
3. Edite o arquivo `wp-config.php` do WordPress instalado para apontar para o banco de dados original do servidor (host, nome do BD, usuário e senha). Faça backup do `wp-config.php` antes.

Exemplo (substitua pelos seus valores):

```php
define('DB_NAME', 'SEU_BANCO');
define('DB_USER', 'SEU_USUARIO');
define('DB_PASSWORD', 'SUA_SENHA');
define('DB_HOST', 'localhost');
```

4. Se o banco original estiver em outro servidor, confirme acesso remoto e permissões. Em muitos casos você só precisa apontar `DB_HOST` para o host correto.
5. Após o tema estar ativo, acesse qualquer página adicionando `?layout=1`, `?layout=2` ou `?layout=3` na URL para alternar entre as versões.
6. As três propostas de layout estão disponíveis como variantes do tema; o seletor de versão no topo alterna automaticamente o parâmetro `layout`.

Se você quer que o subdomínio já suba com conteúdo real, rode o importador do projeto depois que o WordPress estiver funcionando:

```bash
wp eval-file scripts/import-cedoc.php
```

Esse importador cria a coleção `acervo-cedoc`, a taxonomia de categorias, páginas internas e itens com imagens públicas de referência, então o site não fica vazio.

Observações importantes:
- Este pacote contém o tema e os protótipos estáticos em `site-demo/prototipos/` para referência. O conteúdo dinâmico depende do WordPress + Tainacan e da base de dados apontada.
- Não inclua credenciais em arquivos públicos. Use o `wp-config.php` do WordPress no servidor para definir credenciais.

Se quiser, eu gero agora o arquivo zip pronto para upload e o pacote de instalação com instruções passo a passo.

# CEACA-CEDOC

Projeto WordPress + Tainacan do CEACA/CEDOC, preparado para usar a mesma base de dados do backup do servidor e manter o código do site em um repositório GitHub separado.

## O que este repositório contém

- Tema Tainacan customizado em `tainacan-theme-master/`
- Scripts de importação e deploy em `scripts/`
- Documentação de migração, deploy e revisão de design
- Arquivos de demonstração em `site-demo/`

## O que não deve entrar no Git

- Backups do banco e do servidor
- Arquivos `.sql`, `.sql.gz`, `.tar.gz` e `.zip`
- `node_modules/` e `vendor/`
- Credenciais locais e arquivos de restauração do Hostgator

## Fluxo recomendado

1. Restaurar o banco de dados do backup do servidor no MySQL local ou no Hostgator.
2. Subir apenas o WordPress com o tema Tainacan customizado.
3. Executar o importador para copiar o conteúdo do WordPress fonte para as páginas locais.
4. Validar a home, categorias e subcategorias.
5. Publicar o código deste repositório no GitHub como `CEACA-CEDOC`.

## Estrutura principal

- `docker-compose.yml` - ambiente local de desenvolvimento
- `scripts/` - utilitários de importação e deploy
- `tainacan-theme-master/` - tema customizado
- `site-demo/` - referência visual

## Observação sobre a base de dados

A base de dados principal deve vir do backup do servidor. Este repositório guarda o código e os scripts, não o dump completo do banco de produção.

## Como preparar o repositório local

```bash
git init
git add .
git status
```

Depois, crie o repositório `CEACA-CEDOC` no GitHub e aponte o remote.
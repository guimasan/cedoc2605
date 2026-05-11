# QUICK START - Deploy em 10 Minutos

Se você quer apenas o essencial, siga isso:

## 📋 Checklist Rápido

```bash
# 1. Preparar arquivos localmente
./scripts/prepare-deploy.sh cedoc-nova

# 2. No Hostgator cPanel:
   ✓ Criar subdomínio: cedoc-nova.ceacac76.hospedagemdesites.ws
   ✓ Criar MySQL BD: ceacac76_cedoc_nova
   ✓ Criar MySQL user: ceacac76_cedoc_user com TODAS permissões
   ✓ Anotar: DB_NAME, DB_USER, DB_PASSWORD, DB_HOST

# 3. Upload via FTP:
   → public_html/cedoc-nova/wp-config.php (ATUALIZAR credenciais)
   → public_html/cedoc-nova/wp-content/themes/tainacan-interface/
   → Resto dos arquivos

# 4. Restaurar BD via cPanel phpMyAdmin:
   ✓ Import: ceacac76_wp56.sql.gz

# 5. Executar no servidor via SSH:
   mysql -h localhost -u [USER] -p [BD] -e \
   "UPDATE wp_options SET option_value='https://cedoc-nova.ceacac76.hospedagemdesites.ws' WHERE option_name IN ('siteurl','home');"

# 6. Acessar site:
   https://cedoc-nova.ceacac76.hospedagemdesites.ws
```

## 🚨 Erros Comuns

| Erro | Solução |
|------|---------|
| "Erro ao conectar BD" | Cheque credenciais em wp-config.php |
| "Página em branco" | Ative debug em wp-config.php, cheque /wp-content/debug.log |
| "Tema não carrega" | Verifique permissões (755) em wp-content/themes/ |
| "Imagens quebradas" | Executar Permalinks reset: Settings → Permalinks → Save |
| "SSL não funciona" | Ativar AutoSSL em cPanel, aguardar propagação |

## 📚 Documentação Completa

Para guia passo-a-passo detalhado, veja: [DEPLOYMENT-GUIDE.md](DEPLOYMENT-GUIDE.md)

## 🆘 Precisa de Ajuda?

1. Verifique logs: `/wp-content/debug.log`
2. Teste phpMyAdmin connection
3. Verifique permissões de arquivo
4. Leia [DEPLOYMENT-GUIDE.md](DEPLOYMENT-GUIDE.md) seção Troubleshooting

---

**Tempo estimado**: 15-30 minutos (rede/servidor dependente)

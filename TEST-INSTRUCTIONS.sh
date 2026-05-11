#!/usr/bin/env bash

#
# INSTRUÇÕES DE TESTE - MELHORIAS DE DESIGN
#
# Este arquivo descreve como testar todas as melhorias implementadas
# no site CEACA CEDOC.
#

cat << 'EOF'

╔═══════════════════════════════════════════════════════════════════════════╗
║                                                                           ║
║       🎨 TESTE DAS MELHORIAS DE DESIGN - CEACA CEDOC                     ║
║                                                                           ║
║              Siga as instruções abaixo para validar tudo                 ║
║                                                                           ║
╚═══════════════════════════════════════════════════════════════════════════╝


📋 ETAPA 1: PREPARAR AMBIENTE
═════════════════════════════════════════════════════════════════════════════

1.1 Inicie o Docker:
    $ cd /home/surya/Área\ de\ trabalho/CEACA/CEDOC/cedoc2605
    $ docker-compose up -d

1.2 Aguarde ~30 segundos para o WordPress iniciar

1.3 Abra navegador em: http://localhost:8080


📋 ETAPA 2: VALIDAR HOMEPAGE
═════════════════════════════════════════════════════════════════════════════

Testes Visuais:

2.1 [ ] Página inicial carrega normalmente
2.2 [ ] Não há blocos amarelos (CEDOC) redundantes
2.3 [ ] Não há blocos vermelhos (CEACA) redundantes
2.4 [ ] Título visível: "CEACA - Centro de Estudos e Aplicação da Capoeira"
2.5 [ ] Subtexto "Abra cada categoria..." não aparece
2.6 [ ] Carousel funciona (próximo/anterior)
2.7 [ ] Indicadores do carousel funcionam
2.8 [ ] Carousel tem imagens


📋 ETAPA 3: VALIDAR DROPDOWNS DE CATEGORIAS
═════════════════════════════════════════════════════════════════════════════

3.1 [ ] Seção de categorias tem aparência profissional
3.2 [ ] Cada categoria tem preview de imagem
3.3 [ ] Ao clicar em categoria, dropdown expande
3.4 [ ] Texto "CEACA" muda de cor ao expandir
3.5 [ ] Subcategorias aparecem em grid
3.6 [ ] Subcategorias são clicáveis (links funcionam)
3.7 [ ] Dropdown fecha ao clicar novamente


📋 ETAPA 4: VALIDAR MARCADOR DE IMAGENS ALEATÓRIAS
═════════════════════════════════════════════════════════════════════════════

4.1 Imagens com Marcador Laranja:
    [ ] Algumas imagens têm bolinha laranja no canto superior direito
    [ ] Bolinha tem animação pulsante (fica pulsando)
    [ ] Ao passar mouse sobre bolinha, aparece tooltip

4.2 Tooltip:
    [ ] Texto do tooltip: "Imagem de demonstração - selecionada aleatoriamente"
    [ ] Tooltip aparece e desaparece ao mover mouse

4.3 Imagens sem Marcador:
    [ ] Imagens catalogadas não têm bolinha laranja
    [ ] Aparecem normalmente sem marcador


📋 ETAPA 5: VALIDAR RESPONSIVIDADE
═════════════════════════════════════════════════════════════════════════════

5.1 Desktop (1200px+):
    [ ] Layout completo e bem distribuído
    [ ] Carousel com imagem grande
    [ ] Dropdowns em linha

5.2 Tablet (768px-1199px):
    [ ] Layout adaptado
    [ ] Componentes redimensionam
    [ ] Texto legível

5.3 Mobile (< 768px):
    [ ] Abrir: F12 → Ctrl+Shift+M (Device Toolbar)
    [ ] Componentes empilham verticalmente
    [ ] Botões acessíveis
    [ ] Imagens redimensionam
    [ ] Sem horizontal scroll


📋 ETAPA 6: EXECUTAR IMPORTAÇÃO WORDPRESS
═════════════════════════════════════════════════════════════════════════════

6.1 Abra terminal em nova aba:
    $ cd /home/surya/Área\ de\ trabalho/CEACA/CEDOC/cedoc2605
    
6.2 Execute script de importação:
    $ docker-compose exec wordpress php scripts/import-wordpress-content.php

6.3 Validar saída:
    [ ] Mensagem "Starting import from institucional..."
    [ ] Linha para cada página: "Importing: [page] → [slug]"
    [ ] ✓ Checkmark para cada página importada
    [ ] Resumo final com contagem de importadas


📋 ETAPA 7: VALIDAR CONTEÚDO IMPORTADO
═════════════════════════════════════════════════════════════════════════════

7.1 Verifique páginas institucional:
    [ ] Acesse: http://localhost:8080/institucional
    [ ] Tem conteúdo textual (não vazio)
    [ ] Tem imagens (se tiverem no original)

7.2 Verifique página de equipe:
    [ ] Acesse: http://localhost:8080/equipe
    [ ] Conteúdo do WordPress appear (equipeceaca)
    [ ] Imagens importadas

7.3 Verifique outras páginas:
    [ ] historico
    [ ] missao
    [ ] memorias-e-projetos
    [ ] mestres
    [ ] eventos
    [ ] leis
    [ ] premios
    [ ] educacao-e-publicacoes


📋 ETAPA 8: VALIDAR PERFORMANCE
═════════════════════════════════════════════════════════════════════════════

8.1 Abra DevTools: F12

8.2 Aba Network:
    [ ] Carregamento < 3 segundos (homepage)
    [ ] Nenhum erro 404
    [ ] Imagens carregam
    [ ] CSS carregando corretamente

8.3 Aba Performance:
    [ ] Nenhum erro no console (F12 → Console)
    [ ] Avisos OK (avisos de deprecation não importam)

8.4 Aba Styles:
    [ ] cedoc-enhanced.css carregado
    [ ] Estilos aplicando corretamente
    [ ] Gradientes visíveis


📋 ETAPA 9: VALIDAR ACESSIBILIDADE
═════════════════════════════════════════════════════════════════════════════

9.1 Navegação por Teclado:
    [ ] Tab navega entre elementos
    [ ] Dropdown pode ser aberto com Enter
    [ ] Links podem ser ativados com Enter
    [ ] Focus visível (contorno preto)

9.2 Cores e Contraste:
    [ ] Texto legível em todos os backgrounds
    [ ] Vermelho (#C41E3A) tem bom contraste
    [ ] Branco em fundo vermelho legível

9.3 Imagens:
    [ ] Todas têm alt text
    [ ] Descrições adequadas


📋 ETAPA 10: TESTES FINAIS
═════════════════════════════════════════════════════════════════════════════

10.1 Navegação Geral:
     [ ] Links de menu funcionam
     [ ] Breadcrumb funciona
     [ ] Botões responsivos

10.2 Mobile Last Check:
     [ ] Abrir em celular/tablet
     [ ] Tudo funciona corretamente
     [ ] Sem erros no console

10.3 Browser Diferentes:
     [ ] Chrome/Edge
     [ ] Firefox
     [ ] Safari (se disponível)


✨ TESTE FINAL - SCREENSHOT
═════════════════════════════════════════════════════════════════════════════

Depois de validar tudo, tire screenshot:

1. Full page screenshot (F12 → ... → Capture full page)
2. Salve com nome: ceaca-homepage-novo.png
3. Compare com versão anterior (se tiver)


═════════════════════════════════════════════════════════════════════════════

✅ SE TODOS OS TESTES PASSAREM:
  → Site está pronto para próximas etapas
  → Pronto para deploy em novo subdomínio Hostgator
  → Documentação completa em DESIGN-IMPROVEMENTS.md


❌ SE ENCONTRAR PROBLEMAS:
  1. Verifique console (F12 → Console)
  2. Cheque errors em: docker-compose logs wordpress
  3. Limpe cache: Ctrl+Shift+Del (navegador)
  4. Hard refresh: Ctrl+Shift+R
  5. Releia DESIGN-IMPROVEMENTS.md


═════════════════════════════════════════════════════════════════════════════
Data: 11 de maio de 2026
Status: Pronto para Teste
═══════════════════════════════════════════════════════════════════════════════

EOF

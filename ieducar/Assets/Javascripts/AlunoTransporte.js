// Cadastro de alunos no transporte em lote.
//
// Ao selecionar a turma (#ref_cod_turma), busca os alunos ativos e monta uma
// tabela com rota / ponto de embarque / destino editáveis por linha,
// pré-preenchendo o vínculo de transporte já existente de cada aluno.

(function ($) {
  'use strict';

  var rotas = [];

  // Container onde a tabela é renderizada (criado logo abaixo dos filtros).
  var $container = $('<div>').attr('id', 'lista-alunos-transporte')
                             .css('margin-top', '12px');

  // ---------------------------------------------------------------------------
  // Helpers de montagem de inputs por linha
  // ---------------------------------------------------------------------------

  function buildRotaSelect(aluno) {
    var $select = $('<select>').addClass('rota-select')
                               .attr('name', 'alunos[' + aluno.idpes + '][rota]');

    $('<option>').val('').text('Selecione uma rota').appendTo($select);

    $.each(rotas, function (i, rota) {
      var $opt = $('<option>').val(rota.id).text(rota.nome);
      if (String(rota.id) === String(aluno.rota)) {
        $opt.attr('selected', 'selected');
      }
      $opt.appendTo($select);
    });

    return $select;
  }

  function buildPontoSelect(aluno) {
    var $select = $('<select>').addClass('ponto-select')
                               .attr('name', 'alunos[' + aluno.idpes + '][ponto]');

    $('<option>').val('').text('Selecione uma rota acima').appendTo($select);

    return $select;
  }

  function buildDestinoInput(aluno) {
    var $wrapper = $('<span>');

    var $hidden = $('<input>').attr('type', 'hidden')
                              .addClass('destino-id')
                              .attr('name', 'alunos[' + aluno.idpes + '][destino]')
                              .val(aluno.destino || '');

    var $search = $('<input>').attr('type', 'text')
                              .addClass('destino-search')
                              .attr('size', 30)
                              .attr('placeholder', 'Informe o código ou nome da pessoa jurídica')
                              .val(aluno.destino ? (aluno.destino + ' - ' + aluno.destino_nome) : '');

    $wrapper.append($hidden).append($search);

    return $wrapper;
  }

  // Indica se a linha do elemento está habilitada (checkbox "Incluir" ativo).
  function isRowEnabled($el) {
    return !$el.closest('tr').hasClass('linha-desabilitada');
  }

  // Carrega os pontos de uma rota dentro do select de ponto da linha,
  // reutilizando o endpoint legado ponto_xml.php.
  function loadPontos($pontoSelect, rotaId, pontoSelecionado) {
    // Mantém o select desabilitado se a linha não estiver incluída.
    var disabledFinal = !isRowEnabled($pontoSelect);

    if (!rotaId) {
      $pontoSelect.empty().prop('disabled', disabledFinal);
      $('<option>').val('').text('Selecione uma rota acima').appendTo($pontoSelect);
      return;
    }

    $pontoSelect.empty().prop('disabled', true);
    $('<option>').val('').text('Carregando pontos...').appendTo($pontoSelect);

    $.get('/intranet/ponto_xml.php', { rota: rotaId }, function (xml) {
      var $pontos = $(xml).find('ponto');

      $pontoSelect.empty().prop('disabled', disabledFinal);

      if (!$pontos.length) {
        $('<option>').val('').text('Rota sem pontos').appendTo($pontoSelect);
        return;
      }

      $('<option>').val('').text('Selecione um ponto').appendTo($pontoSelect);

      $pontos.each(function () {
        var $ponto = $(this);
        var $opt = $('<option>').val($ponto.attr('cod_ponto')).text($ponto.text());
        if (pontoSelecionado && String($ponto.attr('cod_ponto')) === String(pontoSelecionado)) {
          $opt.attr('selected', 'selected');
        }
        $opt.appendTo($pontoSelect);
      });
    });
  }

  // Botão (ícone de cópia) que replica o valor da primeira linha nas demais.
  function copyIcon(campo, title) {
    return $('<button>').attr('type', 'button')
                        .addClass('copiar-coluna')
                        .attr('data-campo', campo)
                        .attr('title', title)
                        .html(
                          '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" ' +
                          'stroke="currentColor" stroke-width="2" stroke-linecap="round" ' +
                          'stroke-linejoin="round">' +
                          '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>' +
                          '<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1">' +
                          '</path></svg>'
                        );
  }

  // Habilita/desabilita todos os campos editáveis de uma linha. Os campos
  // desabilitados não são enviados no submit, então o checkbox "Incluir"
  // controla efetivamente quais alunos terão alterações gravadas.
  function setRowEnabled($row, enabled) {
    $row.find('.rota-select, .ponto-select, .destino-search, .destino-id')
        .prop('disabled', !enabled);
    $row.toggleClass('linha-desabilitada', !enabled);
  }

  // ---------------------------------------------------------------------------
  // Renderização da tabela
  // ---------------------------------------------------------------------------

  function renderTabela(alunos) {
    $container.empty();

    if (!alunos || !alunos.length) {
      $container.html('<p class="error">Nenhum aluno ativo encontrado para a turma selecionada.</p>');
      return;
    }

    var $table = $('<table>').addClass('tablelistagem').css('width', '100%');

    var $incluirTodos = $('<input>').attr('type', 'checkbox')
                                    .attr('id', 'incluir-todos')
                                    .attr('title', 'Marcar/desmarcar todos');

    var $thead = $('<thead>').append(
      $('<tr>')
        .append($('<td>').addClass('formdktd').css('text-align', 'center')
          .append($incluirTodos).append(' Incluir'))
        .append($('<td>').addClass('formdktd').text('Nome'))
        .append($('<td>').addClass('formdktd')
          .append('Rota ').append(copyIcon('rota', 'Copiar a rota da primeira linha para as demais')))
        .append($('<td>').addClass('formdktd')
          .append('Ponto de embarque ').append(copyIcon('ponto', 'Copiar o ponto da primeira linha para as demais')))
        .append($('<td>').addClass('formdktd')
          .append('Destino (caso diferente da rota) ').append(copyIcon('destino', 'Copiar o destino da primeira linha para as demais')))
    );

    var $tbody = $('<tbody>');

    $.each(alunos, function (i, aluno) {
      // Linha "preenchida" = já possui vínculo de transporte no ano filtrado.
      var preenchido = !!aluno.cod_pessoa_transporte;

      var $incluir = $('<input>').attr('type', 'checkbox')
                                 .addClass('incluir-check')
                                 .prop('checked', preenchido);

      var $rotaSelect = buildRotaSelect(aluno);
      var $pontoSelect = buildPontoSelect(aluno);

      var $row = $('<tr>').attr('data-idpes', aluno.idpes)
        .append($('<td>').css('text-align', 'center').append($incluir))
        .append($('<td>').text(aluno.nome))
        .append($('<td>').append($rotaSelect))
        .append($('<td>').append($pontoSelect))
        .append($('<td>').append(buildDestinoInput(aluno)));

      $tbody.append($row);

      // Estado inicial: só fica habilitada para edição quando já vem preenchida.
      setRowEnabled($row, preenchido);

      // Pré-carrega os pontos da rota já vinculada ao aluno.
      if (aluno.rota) {
        loadPontos($pontoSelect, aluno.rota, aluno.ponto);
      }
    });

    $table.append($thead).append($tbody);

    $container.append(
      $('<h4>').text('Alunos da turma (' + alunos.length + ')')
    ).append($table);

    ativarAutocompleteDestino();
  }

  // ---------------------------------------------------------------------------
  // Eventos
  // ---------------------------------------------------------------------------

  function carregarAlunos(turmaId) {
    $container.html('<p>Carregando alunos...</p>');

    $.ajax({
      url: '/module/Api/AlunoTransporte',
      dataType: 'json',
      data: {
        oper: 'get',
        resource: 'aluno-transporte',
        turma: turmaId,
        ano: $('#ano').val()
      },
      success: function (data) {
        rotas = data.rotas || [];
        renderTabela(data.alunos || []);
      },
      error: function () {
        $container.html('<p class="error">Não foi possível carregar os alunos da turma.</p>');
      }
    });
  }

  // Cascateamento rota -> ponto por linha (delegação de evento).
  $container.on('change', '.rota-select', function () {
    var $row = $(this).closest('tr');
    var $pontoSelect = $row.find('.ponto-select');
    loadPontos($pontoSelect, $(this).val(), null);
  });

  // "Incluir" por linha: habilita/desabilita os campos da linha.
  $container.on('change', '.incluir-check', function () {
    setRowEnabled($(this).closest('tr'), this.checked);
  });

  // "Incluir" no cabeçalho: marca/desmarca todas as linhas de uma vez.
  $container.on('change', '#incluir-todos', function () {
    var checked = this.checked;
    $container.find('.incluir-check').each(function () {
      $(this).prop('checked', checked);
      setRowEnabled($(this).closest('tr'), checked);
    });
  });

  // Ícones de cópia: replicam o valor da primeira linha nas demais.
  $container.on('click', '.copiar-coluna', function () {
    copiarPrimeiraLinha($(this).data('campo'));
  });

  function copiarPrimeiraLinha(campo) {
    var $rows = $container.find('tbody tr');
    if ($rows.length < 2) {
      return;
    }

    var $first = $rows.first();
    var $others = $rows.slice(1);

    if (campo === 'rota') {
      var rota = $first.find('.rota-select').val();
      $others.each(function () {
        // Dispara o change para recarregar os pontos da rota copiada.
        $(this).find('.rota-select').val(rota).trigger('change');
      });
    } else if (campo === 'ponto') {
      var ponto = $first.find('.ponto-select').val();
      $others.each(function () {
        $(this).find('.ponto-select').val(ponto);
      });
    } else if (campo === 'destino') {
      var destinoId = $first.find('.destino-id').val();
      var destinoText = $first.find('.destino-search').val();
      $others.each(function () {
        $(this).find('.destino-id').val(destinoId);
        $(this).find('.destino-search').val(destinoText);
      });
    }
  }

  // Busca do destino (pessoa jurídica) por linha.
  //
  // Reaproveita o componente padrão `simpleSearch` (SimpleSearch.js), o mesmo
  // usado em Pessoatransporte via inputsHelper()->simpleSearchPessoaj. Ele lê o
  // searchPath/params de .data('simple-search-options'), grava o idpes
  // selecionado em .data('hidden-input-id') e exibe "idpes - nome" no texto.
  function ativarAutocompleteDestino() {
    $container.find('.destino-search').each(function () {
      var $search = $(this);
      var $hidden = $search.siblings('.destino-id');

      $search.data('simple-search-options', {
        searchPath: '/module/Api/Pessoaj?oper=get&resource=pessoaj-search',
        params: {},
        canSearch: function () { return true; }
      });
      $search.data('hidden-input-id', $hidden);

      $search.autocomplete({
        minLength: 1,
        autoFocus: true,
        source: function (request, response) {
          return simpleSearch.search(this.element, request, response);
        },
        select: function (event, ui) {
          return simpleSearch.handleSelect(event, ui);
        },
        change: function () {
          if ($.trim($search.val()) === '') {
            $hidden.val('');
          }
        }
      });
    });
  }

  // ---------------------------------------------------------------------------
  // Inicialização
  // ---------------------------------------------------------------------------

  // Estilos do ícone de cópia e da linha desabilitada.
  function injetarEstilos() {
    if ($('#aluno-transporte-styles').length) {
      return;
    }

    $('<style>').attr('id', 'aluno-transporte-styles').text(
      '.copiar-coluna{background:transparent;border:none;color:#4CAF50;cursor:pointer;' +
      'padding:0 2px;vertical-align:middle;line-height:1;}' +
      '.copiar-coluna:hover{color:#357a38;}' +
      '.copiar-coluna:focus{outline:none;}' +
      '#lista-alunos-transporte tr.linha-desabilitada{opacity:.55;}'
    ).appendTo('head');
  }

  $(document).ready(function () {
    var $turma = $('#ref_cod_turma');

    injetarEstilos();

    // Os botões Gravar/Cancelar ficam na última linha (.linhaBotoes) da própria
    // tabela do formulário. Insere a lista de alunos como uma linha imediatamente
    // antes dessa linha, para que a tabela apareça acima dos botões.
    var $linhaBotoes = $('#btn_enviar').closest('tr.linhaBotoes');

    if ($linhaBotoes.length) {
      $('<tr>').append(
        $('<td>').attr('colspan', 2).append($container)
      ).insertBefore($linhaBotoes);
    } else {
      // Fallback: logo após o bloco de filtros.
      $turma.closest('table').after($container);
    }

    $turma.on('change', function () {
      var turmaId = $(this).val();
      if (turmaId) {
        carregarAlunos(turmaId);
      } else {
        $container.empty();
      }
    });
  });
})(jQuery);

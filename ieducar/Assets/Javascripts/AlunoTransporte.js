(function ($) {
  'use strict';

  var $container = $('<div>').attr('id', 'lista-alunos-transporte')
                             .css('margin-top', '12px');

  function isRowEnabled($el) {
    return !$el.closest('tr').hasClass('linha-desabilitada');
  }

  function loadPontos($pontoSelect, rotaId, pontoSelecionado) {
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

  function setRowEnabled($row, enabled) {
    $row.find('.rota-select, .ponto-select, .destino-search, .destino-id')
        .prop('disabled', !enabled);
    $row.toggleClass('linha-desabilitada', !enabled);
  }

  function carregarAlunos(turmaId) {
    $container.html('<p>Carregando alunos...</p>');

    $.ajax({
      url: '/transporte/aluno/cadastro-em-lote/alunos',
      dataType: 'html',
      data: {
        turma: turmaId,
        ano: $('#ano').val()
      },
      success: function (html) {
        $container.html(html);
        initTabela();
      },
      error: function () {
        $container.html('<p class="error">Não foi possível carregar os alunos da turma.</p>');
      }
    });
  }

  function initTabela() {
    $container.find('tbody tr').each(function () {
      var $row = $(this);
      var $rotaSelect = $row.find('.rota-select');
      var $pontoSelect = $row.find('.ponto-select');
      var rota = $rotaSelect.val();

      if (rota) {
        loadPontos($pontoSelect, rota, $pontoSelect.data('selected'));
      }
    });

    ativarAutocompleteDestino();
  }

  $container.on('change', '.rota-select', function () {
    var $row = $(this).closest('tr');
    var $pontoSelect = $row.find('.ponto-select');
    loadPontos($pontoSelect, $(this).val(), null);
  });

  $container.on('change', '.incluir-check', function () {
    setRowEnabled($(this).closest('tr'), this.checked);
  });

  $container.on('change', '#incluir-todos', function () {
    var checked = this.checked;
    $container.find('.incluir-check').each(function () {
      $(this).prop('checked', checked);
      setRowEnabled($(this).closest('tr'), checked);
    });
  });

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

  $(document).ready(function () {
    var $turma = $('#ref_cod_turma');

    var $linhaBotoes = $('#btn_enviar').closest('tr.linhaBotoes');

    if ($linhaBotoes.length) {
      $('<tr>').append(
        $('<td>').attr('colspan', 2).append($container)
      ).insertBefore($linhaBotoes);
    } else {
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

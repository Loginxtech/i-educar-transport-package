@extends('layout.default')

@section('content')
    <form id="formcadastro" action="{{ route('transporte.aluno-em-lote.store') }}" method="post">
        @csrf

        <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0">
            <tbody>
            <tr>
                <td class="formdktd" colspan="2" height="24"><b>Cadastro de alunos no transporte em lote</b></td>
            </tr>

            <tr>
                <td class="formmdtd" valign="top"><span class="form">Ano *</span></td>
                <td class="formmdtd" valign="top">
                    <select class="geral" name="ano" id="ano" style="width: 435px;">
                        @foreach ($anos as $ano)
                            <option value="{{ $ano }}" @selected($ano === $anoAtual)>{{ $ano }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td class="formmdtd" valign="top"><span class="form">Instituição *</span></td>
                <td class="formmdtd" valign="top">
                    <select class="geral" id="ref_cod_instituicao" style="width: 435px;">
                        <option value="">Selecione uma instituição</option>
                        @foreach ($instituicoes as $cod => $nome)
                            <option value="{{ $cod }}" @selected(count($instituicoes) === 1)>{{ $nome }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td class="formmdtd" valign="top"><span class="form">Escola *</span></td>
                <td class="formmdtd" valign="top">
                    <select class="geral" id="escola_id" style="width: 435px;">
                        <option value="">Selecione uma instituição acima</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td class="formmdtd" valign="top"><span class="form">Curso *</span></td>
                <td class="formmdtd" valign="top">
                    <select class="geral" id="curso_id" style="width: 435px;">
                        <option value="">Selecione uma escola acima</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td class="formmdtd" valign="top"><span class="form">Série *</span></td>
                <td class="formmdtd" valign="top">
                    <select class="geral" id="serie_id" style="width: 435px;">
                        <option value="">Selecione um curso acima</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td class="formmdtd" valign="top"><span class="form">Turma *</span></td>
                <td class="formmdtd" valign="top">
                    <select class="geral" id="ref_cod_turma" style="width: 435px;">
                        <option value="">Selecione uma série acima</option>
                    </select>
                </td>
            </tr>

            <tr class="linhaBotoes">
                <td colspan="2" align="center">
                    <div class="separator"></div>
                    <input type="button" id="btn_enviar" class="botaolistagem" value="Gravar"
                           onclick="document.getElementById('formcadastro').submit();">
                    <input type="button" class="botaolistagem" value="Cancelar"
                           onclick="window.location.href='/intranet/transporte_pessoa_lst.php';">
                </td>
            </tr>
            </tbody>
        </table>
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
    <link rel="stylesheet" type="text/css"
          href="{{ Asset::get('/vendor/legacy/Portabilis/Assets/Plugins/Chosen/chosen.css') }}"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>

    <style>
        .copiar-coluna {
            background: transparent;
            border: none;
            color: #4CAF50;
            cursor: pointer;
            font-size: 16px;
            vertical-align: middle;
            transition: color 0.3s;
        }

        .copiar-coluna:hover { color: #45a049; }
        .copiar-coluna:focus { outline: none; box-shadow: none; }

        #lista-alunos-transporte tr.linha-desabilitada { opacity: .55; }
    </style>
@endpush

@push('scripts')
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/Portabilis/Assets/Javascripts/ClientApi.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/Portabilis/Assets/Plugins/Chosen/chosen.jquery.min.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/DynamicInput.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/Escola.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/Curso.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/Serie.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/Turma.js') }}"></script>
    <script type="text/javascript"
            src="{{ Asset::get('/vendor/legacy/TransporteEscolar/Assets/Javascripts/AlunoTransporte.js') }}"></script>

    {{-- Dispara o cascateamento inicial caso a instituição já venha pré-selecionada. --}}
    <script type="text/javascript">
        (function ($) {
            $(function () {
                $('#ref_cod_instituicao').trigger('change');
            });
        })(jQuery);
    </script>
@endpush

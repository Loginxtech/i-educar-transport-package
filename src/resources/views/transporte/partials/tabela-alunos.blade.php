@if (empty($alunos))
    <p class="error">Nenhum aluno ativo encontrado para a turma selecionada.</p>
@else
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0">
        <thead>
        <tr>
            <td class="formdktd" colspan="5" height="24"><b>Alunos da turma ({{ count($alunos) }})</b></td>
        </tr>
        <tr>
            <td class="formmdtd bold" valign="top" style="text-align: center;">
                <span class="form"><b>
                    <input type="checkbox" id="incluir-todos" title="Marcar/desmarcar todos"> Incluir
                </b></span>
            </td>
            <td class="formmdtd bold" valign="top"><span class="form"><b>Nome</b></span></td>
            <td class="formmdtd bold" valign="top">
                <span class="form"><b>Rota</b></span>
                @include('transport::transporte.partials._copiar-icone', [
                    'campo' => 'rota',
                    'title' => 'Copiar a rota da primeira linha para as demais',
                ])
            </td>
            <td class="formmdtd bold" valign="top">
                <span class="form"><b>Ponto de embarque</b></span>
                @include('transport::transporte.partials._copiar-icone', [
                    'campo' => 'ponto',
                    'title' => 'Copiar o ponto da primeira linha para as demais',
                ])
            </td>
            <td class="formmdtd bold" valign="top">
                <span class="form"><b>Destino (caso diferente da rota)</b></span>
                @include('transport::transporte.partials._copiar-icone', [
                    'campo' => 'destino',
                    'title' => 'Copiar o destino da primeira linha para as demais',
                ])
            </td>
        </tr>
        </thead>
        <tbody>
        @foreach ($alunos as $aluno)
            @php $preenchido = !empty($aluno['cod_pessoa_transporte']); @endphp

            <tr data-idpes="{{ $aluno['idpes'] }}" @class(['linha-desabilitada' => !$preenchido])>
                <td class="formmdtd" valign="top" style="text-align: center;">
                    <input type="checkbox" class="incluir-check" @checked($preenchido)>
                </td>

                <td class="formmdtd" valign="top"><span class="form">{{ $aluno['nome'] }}</span></td>

                <td class="formmdtd" valign="top">
                    <select class="rota-select" name="alunos[{{ $aluno['idpes'] }}][rota]" @disabled(!$preenchido)>
                        <option value="">Selecione uma rota</option>
                        @foreach ($rotas as $rota)
                            <option value="{{ $rota['id'] }}"
                                @selected((string) $rota['id'] === (string) $aluno['rota'])>{{ $rota['nome'] }}</option>
                        @endforeach
                    </select>
                </td>

                <td class="formmdtd" valign="top">
                    <select class="ponto-select" name="alunos[{{ $aluno['idpes'] }}][ponto]"
                            data-selected="{{ $aluno['ponto'] }}" @disabled(!$preenchido)>
                        <option value="">Selecione uma rota acima</option>
                    </select>
                </td>

                <td class="formmdtd" valign="top">
                    <span>
                        <input type="hidden" class="destino-id" name="alunos[{{ $aluno['idpes'] }}][destino]"
                               value="{{ $aluno['destino'] }}" @disabled(!$preenchido)>
                        <input type="text" class="destino-search" size="30"
                               placeholder="Informe o código ou nome da pessoa jurídica"
                               value="{{ $aluno['destino'] ? $aluno['destino'] . ' - ' . $aluno['destino_nome'] : '' }}"
                               @disabled(!$preenchido)>
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

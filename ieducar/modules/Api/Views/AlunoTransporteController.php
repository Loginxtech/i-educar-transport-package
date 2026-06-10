<?php

class AlunoTransporteController extends ApiCoreController
{
    protected $_processoAp = 21240;
    protected $_nivelAcessoOption = App_Model_NivelAcesso::SOMENTE_ESCOLA;

    /**
     * Lista de rotas do ano filtrado, para montar os selects de cada linha.
     */
    protected function getRotas($ano)
    {
        $sql = 'SELECT cod_rota_transporte_escolar AS id, descricao, ano
                FROM modules.rota_transporte_escolar
                WHERE ano = $1
                ORDER BY descricao';

        $rotas = $this->fetchPreparedQuery($sql, [$ano]);

        $resultado = [];

        foreach ($rotas as $rota) {
            $resultado[] = [
                'id' => $rota['id'],
                'nome' => $this->toUtf8($rota['descricao'], ['transform' => true]) . ' - ' . $rota['ano'],
            ];
        }

        return $resultado;
    }

    /**
     * Alunos ativos da turma informada, já trazendo o vínculo de transporte
     * existente (se houver) para pré-preencher as colunas editáveis.
     */
    protected function getAlunos()
    {
        $turma = $this->getRequest()->turma;
        $ano = $this->getRequest()->ano;

        if (!is_numeric($turma)) {
            $this->messenger->append('Selecione uma turma válida.');

            return ['alunos' => [], 'rotas' => []];
        }

        if (!is_numeric($ano)) {
            $this->messenger->append('Selecione um ano válido.');

            return ['alunos' => [], 'rotas' => []];
        }

        $sql = 'SELECT
                    a.cod_aluno,
                    p.idpes,
                    p.nome,
                    pt.cod_pessoa_transporte,
                    pt.ref_cod_rota_transporte_escolar AS rota,
                    pt.ref_cod_ponto_transporte_escolar AS ponto,
                    pt.ref_idpes_destino AS destino,
                    pd.nome AS destino_nome
                FROM pmieducar.matricula_turma mt
                INNER JOIN pmieducar.matricula m ON m.cod_matricula = mt.ref_cod_matricula
                INNER JOIN pmieducar.aluno a ON a.cod_aluno = m.ref_cod_aluno
                INNER JOIN cadastro.pessoa p ON p.idpes = a.ref_idpes
                LEFT JOIN LATERAL (
                    SELECT ptx.cod_pessoa_transporte,
                           ptx.ref_cod_rota_transporte_escolar,
                           ptx.ref_cod_ponto_transporte_escolar,
                           ptx.ref_idpes_destino
                    FROM modules.pessoa_transporte ptx
                    INNER JOIN modules.rota_transporte_escolar rtx
                        ON rtx.cod_rota_transporte_escolar = ptx.ref_cod_rota_transporte_escolar
                    WHERE ptx.ref_idpes = p.idpes
                      AND rtx.ano = $2
                    ORDER BY ptx.cod_pessoa_transporte DESC
                    LIMIT 1
                ) pt ON true
                LEFT JOIN cadastro.pessoa pd ON pd.idpes = pt.ref_idpes_destino
                WHERE mt.ref_cod_turma = $1
                  AND mt.ativo = 1
                  AND m.ativo = 1
                ORDER BY (pt.cod_pessoa_transporte IS NULL), p.nome';

        $alunos = $this->fetchPreparedQuery($sql, [$turma, $ano]);

        $resultado = [];

        foreach ($alunos as $aluno) {
            $resultado[] = [
                'cod_aluno' => $aluno['cod_aluno'],
                'idpes' => $aluno['idpes'],
                'nome' => $this->toUtf8($aluno['nome'], ['transform' => true]),
                'cod_pessoa_transporte' => $aluno['cod_pessoa_transporte'],
                'rota' => $aluno['rota'],
                'ponto' => $aluno['ponto'],
                'destino' => $aluno['destino'],
                'destino_nome' => $this->toUtf8($aluno['destino_nome'], ['transform' => true]),
            ];
        }

        return ['alunos' => $resultado, 'rotas' => $this->getRotas($ano)];
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'aluno-transporte')) {
            $this->appendResponse($this->getAlunos());
        } else {
            $this->notImplementedOperationError();
        }
    }
}

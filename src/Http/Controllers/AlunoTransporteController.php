<?php

namespace iEducar\Packages\Transport\Http\Controllers;

use App\Http\Controllers\Controller;
use App_Model_IedFinder;
use iEducar\Packages\Transport\Models\PessoaTransporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlunoTransporteController extends Controller
{
    public function create()
    {
        $this->menu(21240);

        $this->breadcrumb('Cadastro de alunos no transporte em lote', [
            url('intranet/educar_transporte_escolar_index.php') => 'Transporte escolar',
        ]);

        $anoAtual = (int) date('Y');

        return view('transport::transporte.cadastro-em-lote', [
            'anos' => range($anoAtual + 1, 2020),
            'anoAtual' => $anoAtual,
            'instituicoes' => App_Model_IedFinder::getInstituicoes(),
        ]);
    }

    public function alunos(Request $request)
    {
        $turma = $request->query('turma');
        $ano = $request->query('ano');

        if (!is_numeric($turma) || !is_numeric($ano)) {
            return response('');
        }

        $sql = '
            SELECT
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
                  AND rtx.ano = ?
                ORDER BY ptx.cod_pessoa_transporte DESC
                LIMIT 1
            ) pt ON true
            LEFT JOIN cadastro.pessoa pd ON pd.idpes = pt.ref_idpes_destino
            WHERE mt.ref_cod_turma = ?
              AND mt.ativo = 1
              AND m.ativo = 1
            ORDER BY (pt.cod_pessoa_transporte IS NULL), p.nome
        ';

        $alunos = collect(DB::select($sql, [$ano, $turma]))->map(function ($aluno) {
            return [
                'cod_aluno' => $aluno->cod_aluno,
                'idpes' => $aluno->idpes,
                'nome' => $aluno->nome,
                'cod_pessoa_transporte' => $aluno->cod_pessoa_transporte,
                'rota' => $aluno->rota,
                'ponto' => $aluno->ponto,
                'destino' => $aluno->destino,
                'destino_nome' => $aluno->destino_nome,
            ];
        })->all();

        return view('transport::transporte.partials.tabela-alunos', [
            'alunos' => $alunos,
            'rotas' => $this->rotasDoAno($ano),
        ]);
    }

    public function store(Request $request)
    {
        $ano = $request->input('ano');
        $alunos = $request->input('alunos', []);

        if (!is_numeric($ano)) {
            return back()->with('error', 'Selecione um ano válido.');
        }

        if (empty($alunos)) {
            return back()->with('error', 'Nenhum aluno foi selecionado.');
        }

        $rotaIdsDoAno = DB::table('modules.rota_transporte_escolar')
            ->where('ano', $ano)
            ->pluck('cod_rota_transporte_escolar')
            ->all();

        $gravados = 0;

        DB::transaction(function () use ($alunos, $rotaIdsDoAno, &$gravados) {
            foreach ($alunos as $idpes => $dados) {
                $rota = $dados['rota'] ?? null;

                if (empty($rota) || !in_array($rota, $rotaIdsDoAno)) {
                    continue;
                }

                $atributos = [
                    'ref_cod_rota_transporte_escolar' => $rota,
                    'ref_cod_ponto_transporte_escolar' => ($dados['ponto'] ?? null) ?: null,
                    'ref_idpes_destino' => ($dados['destino'] ?? null) ?: null,
                ];

                $existente = PessoaTransporte::where('ref_idpes', $idpes)
                    ->whereIn('ref_cod_rota_transporte_escolar', $rotaIdsDoAno)
                    ->orderByDesc('cod_pessoa_transporte')
                    ->first();

                if ($existente) {
                    $existente->update($atributos);
                } else {
                    PessoaTransporte::create($atributos + ['ref_idpes' => $idpes]);
                }

                $gravados++;
            }
        });

        return redirect('/intranet/transporte_pessoa_lst.php')
            ->with('success', "Transporte cadastrado em lote para {$gravados} aluno(s).");
    }

    protected function rotasDoAno($ano): array
    {
        return DB::table('modules.rota_transporte_escolar')
            ->where('ano', $ano)
            ->orderBy('descricao')
            ->get(['cod_rota_transporte_escolar AS id', 'descricao', 'ano'])
            ->map(function ($rota) {
                return [
                    'id' => $rota->id,
                    'nome' => $rota->descricao . ' - ' . $rota->ano,
                ];
            })->all();
    }
}

<?php

namespace iEducar\Packages\Transport\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaTransporte extends Model
{
    protected $table = 'modules.pessoa_transporte';

    protected $primaryKey = 'cod_pessoa_transporte';

    public $timestamps = false;

    protected $fillable = [
        'ref_idpes',
        'ref_cod_rota_transporte_escolar',
        'ref_cod_ponto_transporte_escolar',
        'ref_idpes_destino',
        'observacao',
        'turno',
    ];
}

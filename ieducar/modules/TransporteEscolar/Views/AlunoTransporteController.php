<?php

class AlunoTransporteController extends Portabilis_Controller_Page_EditController
{
    protected $_dataMapper = 'Usuario_Model_FuncionarioDataMapper';
    protected $_titulo = 'i-Educar - Cadastro de alunos no transporte em lote';

    protected $_nivelAcessoOption = App_Model_NivelAcesso::SOMENTE_ESCOLA;
    protected $_processoAp = 21240;
    protected $_deleteOption = false;

    protected function _preConstruct()
    {
        $this->_options = $this->mergeOptions([
            'edit_success' => '/intranet/transporte_pessoa_lst.php',
        ], $this->_options);

        $this->breadcrumb('Cadastro de alunos no transporte em lote', [
            url('intranet/educar_transporte_escolar_index.php') => 'Transporte escolar',
        ]);
    }

    protected function _initNovo()
    {
        return false;
    }

    protected function _initEditar()
    {
        return false;
    }

    public function Gerar()
    {
        $this->url_cancelar = '/intranet/transporte_pessoa_lst.php';

        // Filtros para localizar a turma cujos alunos serão vinculados em lote.
        $this->inputsHelper()->dynamic([
            'ano',
            'instituicao',
            'escola',
            'curso',
            'serie',
            'turma',
        ]);

        // jQuery UI é necessário para o autocomplete do destino (pessoa
        // jurídica) por linha — o EditController não o carrega por padrão.
        Portabilis_View_Helper_Application::loadJQueryUiLib($this);

        // O destino por linha reutiliza o mesmo componente de busca de pessoa
        // jurídica do cadastro individual (Pessoatransporte::simpleSearchPessoaj),
        // garantindo o mesmo comportamento (busca por código/nome -> grava idpes).
        Portabilis_View_Helper_Application::loadJavascript(
            $this,
            '/vendor/legacy/Portabilis/Assets/Javascripts/Frontend/Inputs/SimpleSearch.js'
        );

        // A tabela de alunos (com rota/ponto/destino editáveis por linha) é
        // montada dinamicamente pelo AlunoTransporte.js ao selecionar a turma.
        $this->loadResourceAssets($this->getDispatcher());
    }
}

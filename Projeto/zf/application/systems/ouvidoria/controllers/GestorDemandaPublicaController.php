<?php

/**
 * Classe responsável pelo registro das Demandas de Públicas
 *
 * @author Arthur Barbosa, Pedro Henrique
 * @category Senac - Projeto Integrador
 */
class Ouvidoria_GestorDemandaPublicaController extends Marca_Controller_Abstract_Operacao
{

    public function init()
    {
        // Carrega o método de inicialização da classe pai
        parent::init();

        Zend_Loader::loadClass("ChamadoModel");
        Zend_Loader::loadClass("ChamadoArquivoModel");
        Zend_Loader::loadClass("ChamadoNotaModel");

        // Carrega a classe de tradução
        Zend_Loader::loadClass("Marca_ConverteCharset");

    }

    /**
     * PostDispatch: método para carregar dropdowns apenas uma vez
     *
     * Método age depois do código de uma action, mas antes da página renderizar
     *
     * @return void
     */
    public function postDispatch()
    {

        $ouvOcor = New ChamadoModel();

        // Array contendo as "actions" que não devem passar por aqui
        //(não é necessário colocar actions que possuam isXmlHttpRequest (ajax))
        $array_exclusao = array("excluir", "relatorio");

        // Verifica a action que está
        $action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

        // Verifica se não está no array de exclusão e se não veio de um forward
        if ((!in_array($action, $array_exclusao)) && ($this->getRequest()->isDispatched())) {

            $this->view->ddwNatureza = $ouvOcor->comboNatureza();

            $this->view->ddwStatus = $ouvOcor->comboStatus();

        }
    }

    // Método index
    public function indexAction() {

        // Redireciona para ação de pesquisar
        $this->_forward('pesquisar');

    }

    public function pesquisarAction()
    {
        // Captura a sessão
        $sessao = new Zend_Session_Namespace("portoweb");

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

        $chamado = new ChamadoModel();

        // Separa a string deixando de fora  os últimos 4 caracteres
        $params['seq_chamado'] = substr($params['cd_protocolo'], 0, -4);
        // Separa a string pegando somente os últimos 4 caracteres
        $params['ano_chamado'] = substr($params['cd_protocolo'], -4);

        $select = $chamado->queryBuscaChamado($params)->orderByList();

        $return = $chamado->fetchAll($select);

        // Recebe a instância do paginator por singleton
        $paginator = Zend_Paginator::factory($return);

        // Seta o número da página corrente
        $pagina = $this->_getParam("pagina", 1);

        // Define a página corrente
        $paginator->setCurrentPageNumber($pagina);

        // Define o total de linhas por página
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);

        // Joga para a view a paginação
        $this->view->paginator = $paginator;

        // Reenvia os valores para o formuláriom q definir
        $this->_helper->RePopulaFormulario->repopular($params);

        // Libera a memória
        unset($chamado);
    }

    // Método novo
    public function novoAction() {}

    // Método selecionar
    public function selecionarAction()
    {
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

        // Captura a sessão
        $sessao = new Zend_Session_Namespace("portoweb");

        //instancia o objeto da modelo
        $chamado = new ChamadoModel();

        $select = $chamado->queryBuscaChamado($params)->orderByList();

        $return = $chamado->fetchRow($select);

        $return = $return->toArray();

        if (is_null($return['USUARIO_VERIFICADOR'])) {
            if ($return['USUARIO_VERIFICADOR'] != $return['USUARIO_CADASTRO']) {
                $dadosUpdateRelator = array(
                    "USUARIO_VERIFICADOR"    => trim($sessao->perfil->CD_USUARIO)
                );

                $where = "SEQ_CHAMADO = {$return['SEQ_CHAMADO']} and ANO_CHAMADO = {$return['ANO_CHAMADO']}";
                $update = $chamado->update($dadosUpdateRelator, $where);

                if ($update) {
                    $return['USUARIO_VERIFICADOR'] = trim($sessao->perfil->CD_USUARIO); // Somente ao fazer o update
                }
            }
        }

        $this->_helper->RePopulaFormulario->repopular($return, "lower");

        unset($ouvOcor);

    }

    // Método salvar
    public function salvarAction()
    {
        // Captura a sessão
        $sessao = new Zend_Session_Namespace("portoweb");

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

        // Instancia as classes Models
        $chamado = new ChamadoModel();

        //função para que se o parâmetro estiver vazio, trocar para nulo
        function troca_para_null(&$item, $key)
        {
            if (trim($item) === "") {
                $item = null;
            }
        }

        // percorre cada item da array $params, trocando para nulo os valores vazios
        array_walk($params, "troca_para_null");

        if ($params["operacao"] === "novo") {

            $dados = array(
                "SEQ_CHAMADO" => (intval($chamado->nextVal()) === 1) ? 1001 : (intval($chamado->nextVal()) - 1),
                "ANO_CHAMADO" => date("Y"),
                "DTHR_INS" => new Zend_Db_Expr("SYSDATE"),
                "USUARIO_CADASTRO" => trim($sessao->perfil->CD_USUARIO),
                "CD_STATUS_RELATO" => intval(1),
                "CD_NATUREZA_RELATO" => $params["cd_natureza_relato"],
                "RELATO_SOLICITANTE" => $params["relato_solicitante"],
                "TITULO_RELATO" => $params["titulo_relato"]
            );

            $insert = $chamado->insert($dados);

            if ($insert) {
                //exibe mensagem de confirmação
                echo "<script>Base.montaMensagemSistema(Array('Registro Salvo com Sucesso!'), 'SUCESSO', 2);</script>";

                $params["seq_chamado"] = $dados["SEQ_CHAMADO"];
                $params["ano_chamado"] = $dados["ANO_CHAMADO"];

                // Redireciona para ação de selecionar
                $this->_forward("selecionar", null, null, $params);
            } else {
                //exibe mensagem de erro
			    echo "<script>Base.montaMensagemSistema(Array('Erro na Validação dos Dados Inseridos.'), 'ERRO', 3);</script>";

                $params = $this->_helper->LimpaParametrosRequisicao->limpar();

                // Redireciona para ação de selecionar
                $this->_forward('pesquisar');
            }

        } else if ($params['operacao'] === 'editar') {

            $dadosUpdate = array(
                "CD_STATUS_RELATO" => $params['cd_status_relato'],
            );

            $where = "SEQ_CHAMADO = {$params['seq_chamado']} and ANO_CHAMADO = {$params['ano_chamado']}";

            $update = $chamado->update($dadosUpdate, $where);

            if ($update) {
                //exibe mensagem de confirmação
                echo "<script>Base.montaMensagemSistema(Array('Registro Salvo com Sucesso!'), 'SUCESSO', 2);</script>";

                // Redireciona para ação de selecionar
                $this->_forward("selecionar", null, null, $params);
            } else {
                //exibe mensagem de erro
			    echo "<script>Base.montaMensagemSistema(Array('Erro na Validação dos Dados Inseridos.'), 'ERRO', 3);</script>";

                $params = $this->_helper->LimpaParametrosRequisicao->limpar();

                // Redireciona para ação de selecionar
                $this->_forward('pesquisar');
            }
        }
    }

    public function telaArquivoAction()
    {

        // layout-flutuante
        $this->_helper->layout->setLayout("layout-flutuante");

        $mensagemSistema = Zend_Registry::get("mensagemSistema");

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        $arq = new ChamadoArquivoModel();

        //Cria o objeto responsavel pelo upload
        $upload = new Zend_File_Transfer_Adapter_Http();

        // Seta o destino no servidor para onde o arquivo vai
        $upload->setDestination("../public/uploads/arquivos");

        // Chama método na model para executar a query
        $select = $arq->buscaArquivos($params);

        // Recupera o sistema selecionado
        $retQuery = $arq->fetchAll($select);

        $linhas = $retQuery->toArray();

        $retorno = array();
        $ret = array();
        if (count($linhas) > 0) {

            foreach ($linhas as $linha) {
                $retorno = array(
                    "seq_arq_ouvidoria" => $linha['SEQ_ARQ_OUVIDORIA'],
                    "seq_ocor" => $linha['SEQ_OCOR'],
                    "ano_ocor" => $linha['ANO_OCOR'],
                    "no_arquivo" => $linha['NO_ARQUIVO'],
                    "local_arquivo" => $linha['LOCAL_ARQUIVO'],
                    "descricao" => $linha['DESCRICAO'],
                    "dthr_ins" => $linha['DTHR_INS'],
                    "link" => $this->view->baseUrl() . "/public/images/botoes/"
                );

                array_push($ret, $retorno);
            }
        }

        $this->view->arquivos = $ret;

        if (isset($params['no_arquivo']) && strlen($params['no_arquivo']) > 0 && $params['no_arquivo'] != 'undefined') {

            $params['no_arquivo'] = trim($params['no_arquivo']);

            $query = $arq->verificaArquivo($params, $params['no_arquivo']);

            $resultado_teste = $arq->fetchRow($query);

            if ($resultado_teste->EXISTE == 0 && ($_FILES["local_arquivo"]["size"] > 0 && $_FILES["local_arquivo"]["size"] < 2097152)) {

                $dados = array(
                    "SEQ_ARQ_CHAMADO"  => intval($arq->nextVal()),
                    "SEQ_CHAMADO"  => $params['seq_chamado'],
                    "ANO_CHAMADO"  => $params['ano_chamado'],
                    "LOCAL_ARQUIVO"  => $_FILES['local_arquivo']['name'],
                    "DESCRICAO" => $params['descricao'],
                    "DTHR_INS" => new Zend_Db_Expr("SYSDATE")
                );

                $params['no_arquivo'] = str_replace(" ", "_", trim($params["no_arquivo"]));

                // Melhor salvar com chaves para manter melhor registro dos arquivos, até para exibir.
                $dados['NO_ARQUIVO'] = $dados['SEQ_ARQ_CHAMADO'] . "_" . $params['seq_chamado'] . "_" . $params['ano_chamado'] . "_" . strtoupper($params['no_arquivo']);

                // Insere os dados na tabela
                $arq->insert($dados);
                // Separa por ponto a string com o nome do arquivo para pegar a extensão do arquivo
                $temp = explode(".", $_FILES['local_arquivo']['name']);

                // Monta o novo nome do arquivo
                $names = $dados['SEQ_ARQ_CHAMADO'] . "_" . $params['seq_chamado'] . "_" . $params['ano_chamado'] . "_" . strtoupper($params['no_arquivo']) . "." . $temp[1];;

                $_FILES['arquivo']['name'] = $names;
                $dirPadrao = '../public/uploads/arquivos/';
                $caminhoArquivo = $dirPadrao . $_FILES['arquivo']['name'];
                $caminhoTemporario = $_FILES['local_arquivo']['tmp_name'];

                move_uploaded_file($caminhoTemporario, $caminhoArquivo);

                // Registra a mensagem do sistema
                $mensagemSistema->send(serialize(array("msg" => array("Arquivo Salvo com Sucesso!"), "titulo" => "SUCESSO", "tipo" => "2")));

                header("Refresh:0");

            } else if ($resultado_teste->EXISTE == 0 && ($_FILES["local_arquivo"]["size"] >= 2097152)) {

                // Registra a mensagem do sistema
                $mensagemSistema->send(serialize(array("msg" => array("Tamanho da imagem maior do que o permitido! Máximo 2 MB."), "titulo" => "ERRO", "tipo" => "3")));

                // Redireciona para o selecionar
                $this->_forward("selecionar", null, null, $params);
            }
        }

        unset($arq);
    }

    public function excluirArquivoXAction()
    {

        try {
            // layout-flutuante
            $this->_helper->layout->setLayout("layout-flutuante");

            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();

            $arq = new ChamadoArquivoModel();

            $chavesArquivo = explode("_", $params["no_arquivo"]);

            $where = "SEQ_ARQ_CHAMADO = " . $chavesArquivo[0] . " AND " .
                     "SEQ_CHAMADO = " . $chavesArquivo[1] . " AND " .
                     "ANO_CHAMADO = " . $chavesArquivo[2] . " AND " .
                     "NO_ARQUIVO = '" . $params["no_arquivo"] . "'";

            // Atualiza os dados
            $delete = $arq->delete($where);

            $teste = unlink("../public/uploads/arquivos/" . $params["no_arquivo"] . "." . $params['extensao']);

            $retornoJSON[] = array("teste" => "1", "deletou" => $teste);

            // Helper json
            $this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    public function telaNotaAction()
    {

        // layout-flutuante
        $this->_helper->layout->setLayout("layout-flutuante");

        // Captura a sessão
        $sessao = new Zend_Session_Namespace("portoweb");

        $mensagemSistema = Zend_Registry::get("mensagemSistema");

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        $nota = new ChamadoNotaModel();

        // Chama método na model para executar a query
        $select = $nota->buscaNotas($params);

        // Recupera o sistema selecionado
        $retQuery = $nota->fetchAll($select);

        $linhas = $retQuery->toArray();

        $retorno = array();
        $ret = array();
        if (count($linhas) > 0) {

            foreach ($linhas as $linha) {
                $retorno = array(
                    "seq_nota_chamado" => $linha['SEQ_NOTA_CHAMADO'],
                    "seq_chamado"  => $linha['SEQ_CHAMADO'],
                    "ano_chamado"  => $linha['ANO_CHAMADO'],
                    "relato"    => $linha['RELATO'],
                    "cd_relator" => $linha['CD_RELATOR'],
                    "dthr_ins"  => $linha['DTHR_INS']
                );

                array_push($ret, $retorno);
            }
        }

        $this->view->notas = $ret;

        if (isset($params['relato']) && strlen($params['relato']) > 0 && isset($params['insere_relato'])) {

            $dados = array(
                "seq_nota_chamado"  => intval($nota->nextVal()),
                "seq_chamado"  => $params['seq_chamado'],
                "ano_chamado"  => $params['ano_chamado'],
                "RELATO"    => $params['relato'],
                "CD_RELATOR" => trim($sessao->perfil->CD_USUARIO),
                "DTHR_INS" => new Zend_Db_Expr("SYSDATE")
            );

            // Insere os dados na tabela
            $insere =  $nota->insert($dados);

            if ($insere) {
                // Registra a mensagem do sistema
                $mensagemSistema->send(serialize(array("msg" => array("Nota Salva com Sucesso!"), "titulo" => "SUCESSO", "tipo" => "2")));

                header("Refresh:0");
            } else {

                // Registra a mensagem do sistema
                $mensagemSistema->send(serialize(array("msg" => array("Erro na inserção do relato."), "titulo" => "ERRO", "tipo" => "3")));

                // Redireciona para o selecionar
                $this->_forward("selecionar", null, null, $params);
            }

        }

        unset($nota);
    }

    // Método excluir
    public function excluirAction() { }

    // Método relatório
    public function relatorioAction() { }

}

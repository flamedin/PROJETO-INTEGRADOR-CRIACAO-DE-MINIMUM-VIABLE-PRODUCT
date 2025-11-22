<?php
/**
 * Modelo da classe Chamado
 *
 * @filesource
 * @author          Arthur Barbosa, Pedro Henrique
 * @copyright
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class ChamadoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'CHAMADO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('SEQ_CHAMADO');


    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;


    /**
     *  Tabelas que a classe faz referencia
     *
     * @var array
     */
    //protected $_referenceMap = array ();

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'SEQ_CHAMADO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'ANO_CHAMADO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'DTHR_INS',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'USUARIO_CADASTRO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'TITULO_RELATO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'CD_NATUREZA_RELATO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'RELATO_SOLICITANTE',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'CD_STATUS_RELATO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
                            );

    /**
     * Retorna a query com os Chamados(registros)
     *
     *
     */
    public function queryBuscaChamado(&$params=array()) {

    	try {

            $wherePeriodo = "1=1";
            if($params["dt_ini"] && $params["dt_fim"])
                $wherePeriodo = "DTHR_INS BETWEEN TO_DATE('". $params["dt_ini"] ." 00:00','DD/MM/YYYY HH24:MI') AND TO_DATE('". $params["dt_fim"] ." 23:59','DD/MM/YYYY HH24:MI')";
            else if($params["dt_ini"] && !$params["dt_fim"])
                $wherePeriodo = "DTHR_INS >= TO_DATE ('". $params["dt_ini"] ." 00:00','DD/MM/YYYY HH24:MI')";
            else if(!$params["dt_ini"] && $params["dt_fim"])
                $wherePeriodo = "DTHR_INS <= TO_DATE ('". $params["dt_fim"] ." 23:59','DD/MM/YYYY HH24:MI')";


            // Busca as guias de entrada
            $where = $this->addWhere(array($wherePeriodo))
                          ->addWhere(array("SEQ_CHAMADO  = ?" => $params["seq_chamado"]))
                          ->addWhere(array("ANO_CHAMADO = ?" => $params["ano_chamado"]))
                          ->addWhere(array("CD_STATUS_RELATO = ?" => $params["cd_status_relato"]))
                          ->getWhere();

            // Monta a busca na view
            $select = $this->select()
                           ->setIntegrityCheck(false)
                           ->from($this, array("SEQ_CHAMADO",
                                               "ANO_CHAMADO",
                                               "PROTOCOLO" => "SEQ_CHAMADO || ANO_CHAMADO",
                                               "DTHR_INS",
                                               "USUARIO_CADASTRO",
                                               "CD_NATUREZA_RELATO",
                                               "CD_STATUS_RELATO",
                                               "RELATO_SOLICITANTE",
                                               "TITULO_RELATO"
                                               ))
                           ->where($where)
                           ->order("PROTOCOLO DESC");

            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Retorna um array com as abreveações definidas na tabela
     *
     */
    public function comboNatureza() {

    	try {

            return array(
                "1" => "REPARO EM VIAS PÚBLICAS",
                "2" => "REPARO EM ILUMUNAÇÃO PÚBLICA",
                "3" => "REPARO EM PRÓPRIOS PÚBLICOS",
                "4" => "REPAROS CAUSADOS POR OBRAS",
                "5" => "OUTROS"
            );

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Retorna um array com as abreveações definidas na tabela
     *
     */
    public function comboStatus() {

    	try {

            return array(
                "1" => "RECEBIDO",
                "2" => "EM ANÁLISE",
                "3" => "CONCLUÍDO"
            );

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

}
?>

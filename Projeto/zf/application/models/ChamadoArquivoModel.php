<?php
/**
 * Modelo da classe ChamadoArquivo
 *
 * @filesource
 * @author          Arthur Barbosa, Pedro Henrique
 * @copyright
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class ChamadoArquivoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'CHAMADO_ARQUIVO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('SEQ_ARQ_CHAMADO');


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
                            array('name'         =>'SEQ_ARQ_CHAMADO',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'SEQ_CHAMADO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'ANO_CHAMADO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'DTHR_INS',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'NO_ARQUIVO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'LOCAL_ARQUIVO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
                            );

    /**
     *
     *
     *
     */
    public function buscaArquivos(&$params=array()) {

    	try {

            // Define os filtros para a cosulta
            $where = $this->addWhere(array("SEQ_ARQ_CHAMADO = ?" => $params['seq_arq_chamado']))
                        ->addWhere(array("SEQ_CHAMADO = ?" => $params['seq_chamado']))
                        ->addWhere(array("ANO_CHAMADO = ?" => $params['ano_chamado']))
                        ->getWhere();

            // Monta e executa a consulta
            $select = $this->select()
                        ->from(array("CHAMADO_ARQUIVO"), array("SEQ_ARQ_CHAMADO",
                                                                    "SEQ_CHAMADO",
                                                                    "ANO_CHAMADO",
                                                                    "NO_ARQUIVO",
                                                                    "LOCAL_ARQUIVO",
                                                                    "DESCRICAO",
                                                                    "DTHR_INS"))
                        ->where($where)
                        ->order("SEQ_ARQ_CHAMADO ASC");

            // Retorna a consulta
            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function verificaArquivo(&$params=array(), $nome_arquivo) {

        try {

            // Define os filtros para a cosulta
            $where = $this->addWhere(array("SEQ_ARQ_CHAMADO = ?" => $params['seq_arq_chamado']))
                        ->addWhere(array("SEQ_CHAMADO = ?" => $params['seq_chamado']))
                        ->addWhere(array("ANO_CHAMADO = ?" => $params['ano_chamado']))
                        ->addWhere(array("NO_ARQUIVO = ?" => $nome_arquivo))
                        ->getWhere();

            // Monta e executa a consulta
            $select = $this->select()
                        ->from(array("CHAMADO_ARQUIVO"), array("COUNT(NO_ARQUIVO) AS EXISTE"))
                        ->where($where);

            // Retorna a consulta
            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }

    }

}
?>

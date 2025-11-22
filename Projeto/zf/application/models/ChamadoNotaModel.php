<?php
/**
 * Modelo da classe ChamadoNota
 *
 * @filesource
 * @author          Arthur Barbosa, Pedro Henrique
 * @copyright
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class ChamadoNotaModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'CHAMADO_NOTA';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('SEQ_NOTA_CHAMADO');


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
                            array('name'         =>'SEQ_NOTA_CHAMADO',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'SEQ_CHAMADO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'ANO_CHAMADO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'CD_RELATOR',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'DTHR_INS',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
                            );

    /**
     *
     *
     *
     */
    public function buscaNotas(&$params=array()) {

    	try {

            // Define os filtros para a cosulta
            $where = $this->addWhere(array("SEQ_NOTA_CHAMADO = ?" => $params['seq_nota_chamado']))
                        ->addWhere(array("SEQ_CHAMADO = ?" => $params['seq_chamado']))
                        ->addWhere(array("ANO_CHAMADO = ?" => $params['ano_chamado']))
                        ->getWhere();

            // Monta e executa a consulta
            $select = $this->select()
                        ->from(array("CHAMADO_NOTA"), array("SEQ_NOTA_CHAMADO",
                                                                    "SEQ_CHAMADO",
                                                                    "ANO_CHAMADO",
                                                                    "CD_RELATOR",
                                                                    "RELATO",
                                                                    "DTHR_INS"))
                        ->where($where)
                        ->order("SEQ_NOTA_CHAMADO ASC");

            // Retorna a consulta
            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

}
?>

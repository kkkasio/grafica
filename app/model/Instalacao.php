<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;

/**
 * Instalacao Active Record
 * @author  <Kásio Eduardo>
 */
class Instalacao extends TRecord
{
	const TABLENAME = 'instalacoes';
	const PRIMARYKEY = 'id';
	const IDPOLICY =  'max'; // {max, serial}


	private $funcionario;
	private $venda;

	/**
	 * Constructor method
	 */
	public function __construct($id = NULL, $callObjectLoad = TRUE)
	{
		parent::__construct($id, $callObjectLoad);
		parent::addAttribute('date');
		parent::addAttribute('periodo');
		parent::addAttribute('funcionario_id');
		parent::addAttribute('venda_id');
		parent::addAttribute('descricao');
		parent::addAttribute('cor');
		parent::addAttribute('status');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
	}


	/**
	 * Method set_funcionario
	 * Sample of usage: $Instalacao->funcionario = $object;
	 * @param $object Instance of SystemUser
	 */
	public function set_funcionario(SystemUser $object)
	{
		$this->funcionario = $object;
		$this->funcionario_id = $object->id;
	}

	/**
	 * Method get_funcionario
	 * Sample of usage: $Instalacao->funcionario->attribute;
	 * @returns SystemUser instance
	 */
	public function get_funcionario()
	{
		// loads the associated object
		if (empty($this->funcionario))
			$this->funcionario = new SystemUser($this->funcionario_id);

		// returns the associated object
		return $this->funcionario;
	}


	/**
	 * Method set_venda
	 * Sample of usage: $Instalacao->venda = $object;
	 * @param $object Instance of Venda
	 */
	public function set_venda(Venda $object)
	{
		$this->venda = $object;
		$this->venda_id = $object->id;
	}

	/**
	 * Method get_venda
	 * Sample of usage: $Instalacao->venda->attribute;
	 * @returns Venda instance
	 */
	public function get_venda()
	{
		// loads the associated object
		if (empty($this->venda))
			$this->venda = new Venda($this->venda_id);

		// returns the associated object
		return $this->venda;
	}


	public function verificaDataPeriodo($date, $periodo)
	{
		return Instalacao::where('date', '=', $date)->where('periodo', '=', $periodo)->count();
	}
}

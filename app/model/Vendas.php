<?php

use Adianti\Database\TRecord;

/**
 * Vendas Active Record
 * @author  <your-name-here>
 */
class Vendas extends TRecord
{
	const TABLENAME = 'vendas';
	const PRIMARYKEY = 'id';
	const IDPOLICY =  'max'; // {max, serial}
	const CREATEDAT = 'created_at';
	const UPDATEDAT = 'updated_at';


	private $cliente;

	/**
	 * Constructor method
	 */
	public function __construct($id = NULL, $callObjectLoad = TRUE)
	{
		parent::__construct($id, $callObjectLoad);
		parent::addAttribute('numero');
		parent::addAttribute('cliente_id');
		parent::addAttribute('vendedor_id');
		parent::addAttribute('valor_real');
		parent::addAttribute('valor_recebido');
		parent::addAttribute('desconto');
		parent::addAttribute('forma_pagamento');
		parent::addAttribute('status');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
	}


	/**
	 * Method set_cliente
	 * Sample of usage: $vendas->cliente = $object;
	 * @param $object Instance of Cliente
	 */
	public function set_cliente(Cliente $object)
	{
		$this->cliente = $object;
		$this->cliente_id = $object->id;
	}

	/**
	 * Method get_cliente
	 * Sample of usage: $vendas->cliente->attribute;
	 * @returns Cliente instance
	 */
	public function get_cliente()
	{
		// loads the associated object
		if (empty($this->cliente))
			$this->cliente = new Cliente($this->cliente_id);

		// returns the associated object
		return $this->cliente;
	}
}

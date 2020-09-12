<?php

use Adianti\Database\TRecord;

/**
 * VendaItem Active Record
 * @author  <your-name-here>
 */
class VendaItem extends TRecord
{
	const TABLENAME  = 'venda_item';
	const PRIMARYKEY = 'id';
	const IDPOLICY   =  'max'; // {max, serial}
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';


	/**
	 * Constructor method
	 */
	public function __construct($id = NULL, $callObjectLoad = TRUE)
	{
		parent::__construct($id, $callObjectLoad);
		parent::addAttribute('venda_id');
		parent::addAttribute('produto_id');
		parent::addAttribute('quantidade');
		parent::addAttribute('preco');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
	}
}

<?php

use Adianti\Database\TRecord;

/**
 * Pagamentos Active Record
 * @author  <Kásio Eduardo>
 */
class Pagamentos extends TRecord
{
  const TABLENAME = 'pagamentos';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'max'; // {max, serial}


  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('valor');
    parent::addAttribute('venda_id');
    parent::addAttribute('vendedor_id');
    parent::addAttribute('data_pagamento');
    parent::addAttribute('pago');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }
}

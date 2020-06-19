<?php

use Adianti\Database\TRecord;

/**
 * Categorias Active Record
 * @author  <your-name-here>
 */
class Categoria extends TRecord
{
  const TABLENAME = 'categorias';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'serial'; // {max, serial}
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('nome');
    parent::addAttribute('ativo');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }
}

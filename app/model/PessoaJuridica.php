<?php

use Adianti\Database\TRecord;

/**
 * PessoaJuridica Active Record
 * @author  <your-name-here>
 */
class PessoaJuridica extends TRecord
{
  const TABLENAME = 'pessoa_juridica';
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
    parent::addAttribute('cliente_id');
    parent::addAttribute('cnpj');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }


  /**
   * Method set_cliente
   * Sample of usage: $pessoa_juridica->cliente = $object;
   * @param $object Instance of Cliente
   */
  public function set_cliente(Cliente $object)
  {
    $this->cliente = $object;
    $this->cliente_id = $object->id;
  }

  /**
   * Method get_cliente
   * Sample of usage: $pessoa_juridica->cliente->attribute;
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

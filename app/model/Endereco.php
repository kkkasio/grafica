<?php

use Adianti\Database\TRecord;

/**
 * Endereco Active Record
 * @author  <your-name-here>
 */
class Endereco extends TRecord
{
  const TABLENAME = 'endereco';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'max'; // {max, serial}
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  private $cidade;
  private $estado;
  private $cliente;

  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('cliente_id');
    parent::addAttribute('cep');
    parent::addAttribute('logradouro');
    parent::addAttribute('numero');
    parent::addAttribute('complemento');
    parent::addAttribute('bairro');
    parent::addAttribute('estado_id');
    parent::addAttribute('cidade_id');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }


  /**
   * Method set_cidade
   * Sample of usage: $endereco->cidade = $object;
   * @param $object Instance of Cidade
   */
  public function set_cidade(Cidade $object)
  {
    $this->cidade = $object;
    $this->cidade_id = $object->id;
  }

  /**
   * Method get_cidade
   * Sample of usage: $endereco->cidade->attribute;
   * @returns Cidade instance
   */
  public function get_cidade()
  {
    // loads the associated object
    if (empty($this->cidade))
      $this->cidade = new Cidade($this->cidade_id);

    // returns the associated object
    return $this->cidade;
  }


  /**
   * Method set_estado
   * Sample of usage: $endereco->estado = $object;
   * @param $object Instance of Estado
   */
  public function set_estado(Estado $object)
  {
    $this->estado = $object;
    $this->estado_id = $object->id;
  }

  /**
   * Method get_estado
   * Sample of usage: $endereco->estado->attribute;
   * @returns Estado instance
   */
  public function get_estado()
  {
    // loads the associated object
    if (empty($this->estado))
      $this->estado = new Estado($this->estado_id);

    // returns the associated object
    return $this->estado;
  }


  /**
   * Method set_cliente
   * Sample of usage: $endereco->cliente = $object;
   * @param $object Instance of Cliente
   */
  public function set_cliente(Cliente $object)
  {
    $this->cliente = $object;
    $this->cliente_id = $object->id;
  }

  /**
   * Method get_cliente
   * Sample of usage: $endereco->cliente->attribute;
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

  public function store()
  {
    parent::store();
  }
}

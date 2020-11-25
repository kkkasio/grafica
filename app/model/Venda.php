<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;
use Adianti\Widget\Form\TFile;

/**
 * Vendas Active Record
 * @author  <Kásio Eduardo>
 */
class Venda extends TRecord
{
  const TABLENAME  = 'vendas';
  const PRIMARYKEY = 'id';
  const IDPOLICY   =  'max'; // {max, serial}
  const CREATEDAT  = 'created_at';
  const UPDATEDAT  = 'updated_at';


  private $cliente;
  private $vendedor;

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
    parent::addAttribute('previsao_entrega');
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

  public function set_vendedor(SystemUser $object)
  {
    $this->vendedor = $object;
    $this->vendedor_id = $object->id;
  }

  /**
   * Method get_vendedor
   * Sample of usage: $vendas->vendedor->attribute;
   * @returns SystemUser instance
   */
  public function get_vendedor()
  {
    // loads the associated object
    if (empty($this->vendedor))
      $this->vendedor = new SystemUser($this->vendedor_id);

    // returns the associated object
    return $this->vendedor;
  }

  public function get_produtos()
  {
    return VendaItem::where('venda_id', '=', $this->id)->load();
  }

  public function get_totalPago()
  {
    return Pagamentos::where('venda_id', '=', $this->id)->where('pago', '=', 'S')->sumBy('valor');
  }
  public function get_faltaPagar()
  {
    return Pagamentos::where('venda_id', '=', $this->id)->where('pago', '=', 'N')->sumBy('valor');
  }

  public function get_pagamentos()
  {
    return Pagamentos::where('venda_id', '=', $this->id)->load();
  }
}

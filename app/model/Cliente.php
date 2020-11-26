<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;

/**
 * Clientes Active Record
 * @author  <Kásio Eduardo>
 */
class Cliente extends TRecord
{
  const TABLENAME = 'clientes';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'max';
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  private $telefones;
  private $cliente;

  private $estado;
  private $cidade;
  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('nome');
    parent::addAttribute('email');
    parent::addAttribute('cep');
    parent::addAttribute('tipo');
    parent::addAttribute('logradouro');
    parent::addAttribute('numero');
    parent::addAttribute('complemento');
    parent::addAttribute('bairro');
    parent::addAttribute('estado_id');
    parent::addAttribute('cidade_id');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }


  public function get_estado()
  {
    // loads the associated object
    if (empty($this->estado))
      $this->estado = new Estado($this->estado_id);

    // returns the associated object
    return $this->estado;
  }

  public function get_cidade()
  {
    // loads the associated object
    if (empty($this->cidade))
      $this->cidade = new Cidade($this->cidade_id);

    // returns the associated object
    return $this->cidade;
  }

  public function clearParts()
  {
    $this->telefones = array();
  }

  public function getTelefone()
  {
    return $this->telefones;
  }

  public function addTelefone(Telefone $object)
  {
    $this->telefones[] = $object;
  }

  /**
   * Load the object and its aggregates
   * @param $id object ID
   */
  public function load($id)
  {

    $this->contacts = parent::loadComposite('Telefone', 'cliente_id', $id);

    // load the object itself
    return parent::load($id);
  }

  /**
   * Store the object and its aggregates
   */
  public function store()
  {
    parent::store();

    parent::saveComposite('Telefone', 'cliente_id', $this->id, $this->telefones);
  }

  /**
   * Delete the object and its aggregates
   * @param $id object ID
   */
  public function delete($id = NULL)
  {
    $id = isset($id) ? $id : $this->id;
    parent::deleteComposite('Telefone', 'cliente_id', $id);

    // delete the object itself
    parent::delete($id);
  }

  public function get_cliente()
  {
    if (empty($this->cliente))
      if ($this->tipo === 'Física') {
        $this->cliente = new PessoaFisica($this->id);
      } else {
        $this->cliente = new PessoaJuridica($this->id);
      }

    return $this->cliente;
  }

  public function get_documento()
  {
    $this->get_cliente();

    if ($this->tipo === 'Física')
      return $this->mask($this->cliente->cpf, '###.###.###-##');
    else
      return $this->mask($this->cliente->cnpj, '##.###.###/####-##');
  }

  public function getVendas()
  {
    return Venda::where('cliente_id', '=', $this->id)->load();
  }

  private function mask($value, $mask)
  {
    $maskared = '';
    $k = 0;

    for ($i = 0; $i <= strlen($mask) - 1; $i++) {
      if ($mask[$i] == '#') {
        if (isset($value[$k]))
          $maskared .= $value[$k++];
      } else {
        if (isset($mask[$i]))
          $maskared .= $mask[$i];
      }
    }
    return $maskared;
  }
}

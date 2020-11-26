<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * ClienteForm Master/Detail
 * @author  <your name here>
 */
class ClienteForm extends TPage
{
  protected $form; // form
  protected $detail_list;
  protected $datagrid;


  /**
   * Page constructor
   */
  public function __construct()
  {
    parent::__construct();

    // creates the form
    $this->form = new BootstrapFormBuilder('form_Cliente');
    $this->form->appendPage('Dados do Cliente');

    $this->form->setFormTitle('Cliente - Pessoa Física');
    $this->form->setClientValidation(TRUE);
    $this->form->generateAria();

    // master fields
    $id = new THidden('id');
    $nome = new TEntry('nome');
    $email = new TEntry('email');
    $tipo = new TEntry('tipo');

    $cpf = new TEntry('cpf');
    $id_pessoa = new THidden('id_pessoa');
    $nascimento = new TDate('nascimento');

    $cep = new TEntry('cep');
    $logradouro = new TEntry('logradouro');
    $numero = new TEntry('numero');
    $complemento = new TEntry('complemento');
    $bairro = new TEntry('bairro');

    $filtro = new TCriteria;
    $filtro->add(new TFilter('id', '<', '0'));

    $estado_id = new TDBCombo('estado_id', 'grafica', 'Estado', 'id', 'nome');
    $cidade_id = new TDBCombo('cidade_id', 'grafica', 'Cidade', 'id', 'nome');

    $estado_id->enableSearch();

    $nascimento->setSize('40%');
    $id->setSize('30%');

    $nome->addValidation('Nome', new TRequiredValidator);
    $email->addValidation('Nome', new TRequiredValidator);
    $cpf->addValidation('Nome', new TRequiredValidator);
    $nascimento->addValidation('Nome', new TRequiredValidator);
    $cep->addValidation('Nome', new TRequiredValidator);
    $logradouro->addValidation('Nome', new TRequiredValidator);
    $bairro->addValidation('Nome', new TRequiredValidator);
    $estado_id->addValidation('Nome', new TRequiredValidator);
    $cidade_id->addValidation('Nome', new TRequiredValidator);



    $nascimento->setMask('dd/mm/yyyy');
    $nascimento->setDatabaseMask('yyyy-mm-dd');
    $cpf->setMask('999.999.999-99', TRUE);
    $cpf->setExitAction(new TAction([$this, 'onExitCPF']));
    $cep->setMask('99.999-999', TRUE);
    $tipo->setValue('Física');
    $tipo->setEditable(FALSE);

    $estado_id->setChangeAction(new TAction([$this, 'onChangeEstado']));
    $cep->setExitAction(new TAction([$this, 'onExitCEP']));


    // detalhe telefone
    $detail_uniqid = new THidden('detail_uniqid');
    $detail_id = new THidden('detail_id');
    $detail_tipo = new TCombo('detail_tipo');
    $detail_ddd = new TEntry('detail_ddd');
    $detail_numero = new TEntry('detail_numero');

    $detail_tipo->addItems(['Fixo' => 'Fixo', 'Celular' => 'Celular']);
    $detail_tipo->setSize('30%');
    $detail_ddd->setMaxLength(2);

    if (!empty($id) or !empty($id_pessoa)) {
      $id->setEditable(FALSE);
      $id_pessoa->setEditable(FALSE);
    }

    // master fields
    $this->form->addFields([], [$id], [], [$id_pessoa]);
    $this->form->addFields([new TLabel('CPF')], [$cpf]);
    $this->form->addFields([new TLabel('Nome')], [$nome], [new TLabel('Email')], [$email]);
    $this->form->addFields([new TLabel('Data de Nascimento')], [$nascimento], [new TLabel('Tipo')], [$tipo]);

    $this->form->addContent([new TFormSeparator('Endereço')]);


    $this->form->addFields([new TLabel('CEP')], [$cep]);
    $this->form->addFields([new TLabel('Logradouro')], [$logradouro], [new TLabel('Numero')], [$numero]);
    $this->form->addFields([new TLabel('Complemento')], [$complemento], [new TLabel('Bairro')], [$bairro]);
    $this->form->addFields([new TLabel('Estado')], [$estado_id], [new TLabel('Cidade')], [$cidade_id]);

    // detail fields
    $this->form->addContent([new TFormSeparator('Telefones')]);
    $this->form->addFields([$detail_uniqid]);
    $this->form->addFields([$detail_id]);


    $add = TButton::create('add', [$this, 'onDetailAdd'], 'Adicionar Telefone', 'fa:plus-circle green');
    $add->getAction()->setParameter('static', '1');
    //$this->form->addFields();


    $this->form->addFields([new TLabel('')], [new TLabel('Tipo')], [$detail_tipo], [new TLabel('DDD')], [$detail_ddd], [new TLabel('Número')], [$detail_numero], [], [$add]);

    $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
    $this->detail_list->setId('Telefone_list');
    $this->detail_list->generateHiddenFields();
    $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";

    // items
    $this->detail_list->addColumn(new TDataGridColumn('uniqid', 'Uniqid', 'center'))->setVisibility(false);
    $this->detail_list->addColumn(new TDataGridColumn('id', 'Id', 'center'))->setVisibility(false);
    $this->detail_list->addColumn(new TDataGridColumn('tipo', 'Tipo', 'left', 100));
    $this->detail_list->addColumn(new TDataGridColumn('ddd', 'DDD', 'left', 100));
    $this->detail_list->addColumn(new TDataGridColumn('numero', 'Número', 'left', 100));

    // detail actions
    $action1 = new TDataGridAction([$this, 'onDetailEdit']);
    $action1->setFields(['uniqid', '*']);

    $action2 = new TDataGridAction([$this, 'onDetailDelete']);
    $action2->setField('uniqid');

    // add the actions to the datagrid
    $this->detail_list->addAction($action1, _t('Edit'), 'fa:edit blue');
    $this->detail_list->addAction($action2, _t('Delete'), 'far:trash-alt red');

    $this->detail_list->createModel();

    $panel = new TPanelGroup;
    $panel->add($this->detail_list);
    $panel->getBody()->style = 'overflow-x:auto';
    $this->form->addContent([$panel]);




    $this->form->appendPage('Vendas do Cliente');

    $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
    $this->datagrid->style = 'width: 100%';
    $this->datagrid->datatable = 'true';


    $column_id = new TDataGridColumn('id', '#', 'right');
    $column_numero = new TDataGridColumn('numero', 'Número', 'left');
    $column_created_at = new TDataGridColumn('created_at', 'Data', 'left');
    $column_previsao_entrega = new TDataGridColumn('previsao_entrega', 'Entrega', 'left');
    $column_vendedor_id = new TDataGridColumn('vendedor->name', 'Vendedor', 'left');
    $column_valor_real = new TDataGridColumn('valor_real', 'Valor Real', 'left');
    $column_forma_pagamento = new TDataGridColumn('forma_pagamento', 'Forma Pagamento', 'left');
    $column_status = new TDataGridColumn('status', 'Status', 'left');

    $this->datagrid->addColumn($column_id);
    $this->datagrid->addColumn($column_numero);
    $this->datagrid->addColumn($column_vendedor_id);
    $this->datagrid->addColumn($column_valor_real);
    $this->datagrid->addColumn($column_forma_pagamento);
    $this->datagrid->addColumn($column_status);
    $this->datagrid->addColumn($column_created_at);
    $this->datagrid->addColumn($column_previsao_entrega);


    $column_valor_real->setTransformer(function ($value) {
      if (is_numeric($value))
        return 'R$ ' . number_format($value, 2, ',', '.');
    });

    $column_previsao_entrega->setTransformer(function ($value) {
      return TDate::date2br($value);
    });

    $column_created_at->setTransformer(function ($value) {
      return TDate::date2br($value);
    });

    $this->datagrid->createModel();

    $this->form->addContent([$this->datagrid]);




    $this->form->addAction('Save',  new TAction([$this, 'onSave'], ['static' => '1']), 'fa:save green');
    $this->form->addActionLink('Novo Cliente',  new TAction([$this, 'onClear']), 'fa:eraser red');

    // create the page container
    $container = new TVBox;
    $container->style = 'width: 100%';
    $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);


    parent::add($container);
  }


  /**
   * Clear form
   * @param $param URL parameters
   */
  public function onClear($param)
  {
    $this->form->clear(TRUE);
  }

  /**
   * Add detail item
   * @param $param URL parameters
   */
  public function onDetailAdd($param)
  {
    try {
      $this->form->validate();
      $data = $this->form->getData();

      $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();

      $grid_data = [];
      $grid_data['uniqid'] = $uniqid;
      $grid_data['id'] = $data->detail_id;
      $grid_data['tipo'] = $data->detail_tipo;
      $grid_data['ddd'] = $data->detail_ddd;
      $grid_data['numero'] = $data->detail_numero;

      // insert row dynamically
      $row = $this->detail_list->addItem((object) $grid_data);
      $row->id = $uniqid;

      TDataGrid::replaceRowById('Telefone_list', $uniqid, $row);

      // clear detail form fields
      $data->detail_uniqid = '';
      $data->detail_id = '';
      $data->detail_tipo = '';
      $data->detail_ddd = '';
      $data->detail_numero = '';

      // send data, do not fire change/exit events
      TForm::sendData('form_Cliente', $data, false, false);
    } catch (Exception $e) {
      $this->form->setData($this->form->getData());
      new TMessage('error', $e->getMessage());
    }
  }

  /**
   * Edit detail item
   * @param $param URL parameters
   */
  public static function onDetailEdit($param)
  {
    $data = new stdClass;
    $data->detail_uniqid = $param['uniqid'];
    $data->detail_id = $param['id'];
    $data->detail_tipo = $param['tipo'];
    $data->detail_ddd = $param['ddd'];
    $data->detail_numero = $param['numero'];

    // send data, do not fire change/exit events
    TForm::sendData('form_Cliente', $data, false, false);
  }

  /**
   * Delete detail item
   * @param $param URL parameters
   */
  public static function onDetailDelete($param)
  {
    // clear detail form fields
    $data = new stdClass;
    $data->detail_uniqid = '';
    $data->detail_id = '';
    $data->detail_tipo = '';
    $data->detail_ddd = '';
    $data->detail_numero = '';

    // send data, do not fire change/exit events
    TForm::sendData('form_Cliente', $data, false, false);

    // remove row
    TDataGrid::removeRowById('Telefone_list', $param['uniqid']);
  }

  /**
   * Load Master/Detail data from database to form
   */
  public function onEdit($param)
  {
    try {
      TTransaction::open('grafica');

      if (isset($param['key'])) {
        $key = $param['key'];

        $object = new Cliente($key);

        $items  = Telefone::where('cliente_id', '=', $key)->load();

        $pessoaFisica = PessoaFisica::where('cliente_id', '=', $key)->load();
        $pessoa = new stdClass;
        $pessoa = $pessoaFisica[0];
        $pessoa->id_pessoa = $pessoaFisica[0]->id;
        $this->form->setData($pessoa);


        foreach ($items as $item) {
          $item->uniqid = uniqid();
          $row = $this->detail_list->addItem($item);
          $row->id = $item->uniqid;
        }


        //preencher o datagrid com as
        $this->datagrid->addItems($object->getVendas());



        $this->form->setData($object);
        TTransaction::close();
      } else {
        $this->form->clear(TRUE);
      }
    } catch (Exception $e) // in case of exception
    {
      new TMessage('error', $e->getMessage());
      TTransaction::rollback();
    }
  }

  /**
   * Save the Master/Detail data from form to database
   */
  public function onSave($param)
  {
    try {
      // open a transaction with database
      TTransaction::open('grafica');

      $data = $this->form->getData();
      $this->form->validate();

      $master = new Cliente;
      $master->fromArray((array) $data);
      $master->store();


      $pessoaFisica = new PessoaFisica;
      $pessoaFisica->fromArray((array) $data);

      if (!empty($data->id_pessoa)) {
        $pessoaFisica->id = $data->id_pessoa;
      }

      $pessoaFisica->cliente_id = $master->id;
      $pessoaFisica->store();

      Telefone::where('cliente_id', '=', $master->id)->delete();

      if ($param['Telefone_list_tipo']) {
        foreach ($param['Telefone_list_tipo'] as $key => $item_id) {
          $detail = new Telefone;
          $detail->tipo  = $param['Telefone_list_tipo'][$key];
          $detail->ddd  = $param['Telefone_list_ddd'][$key];
          $detail->numero  = $param['Telefone_list_numero'][$key];
          $detail->cliente_id = $master->id;
          $detail->store();
        }
      }
      TTransaction::close(); // close the transaction

      TForm::sendData('form_Cliente', (object) ['id' => $master->id]);

      new TMessage('info', 'Cliente foi salvo');
    } catch (Exception $e) // in case of exception
    {
      new TMessage('error', $e->getMessage());
      $this->form->setData($this->form->getData()); // keep form data
      TTransaction::rollback();
    }
  }

  public static function onChangeEstado($param)
  {
    try {
      TTransaction::open('grafica');
      if (!empty($param['estado_id'])) {
        $criteria = TCriteria::create(['estado_id' => $param['estado_id']]);

        TDBCombo::reloadFromModel('form_Cliente', 'cidade_id', 'grafica', 'Cidade', 'id', '{nome} ({id})', 'nome', $criteria, TRUE);
      } else {
        TCombo::clearField('form_Cliente', 'cidade_id');
      }
      TTransaction::close();
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    }
  }

  public static function onExitCEP($param)
  {
    session_write_close();

    try {
      $cep = preg_replace('/[^0-9]/', '', $param['cep']);
      $url = 'https://viacep.com.br/ws/' . $cep . '/json/unicode/';

      $content = @file_get_contents($url);

      if ($content !== false) {
        $cep_data = json_decode($content);
        $data = new stdClass;

        if (is_object($cep_data) && empty($cep_data->erro)) {
          TTransaction::open('grafica');
          $estado = Estado::where('uf', '=', $cep_data->uf)->first();
          $cidade = Cidade::where('codigo_ibge', '=', $cep_data->ibge)->first();
          TTransaction::close();

          $data->logradouro = $cep_data->logradouro;
          $data->complemento = $cep_data->complemento;
          $data->bairro = $cep_data->bairro;
          $data->estado_id = $estado->id ?? '';
          $data->cidade_id = $cidade->id ?? '';

          TForm::sendData('form_Cliente', $data, false, true);
        } else {
          $data->logradouro = '';
          $data->complemento = '';
          $data->bairro = '';
          $data->estado_id = '';
          $data->cidade_id = '';

          TForm::sendData('form_Cliente', $data, false, true);
        }
      }
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    }
  }

  public static function onExitCPF($param)
  {
    try {
      if (!empty($param['cpf'])) {
        TTransaction::open('grafica');

        $cpf = str_replace(array('.', '-'), '', $param['cpf']);
        $pessoaFisica = PessoaFisica::where('cpf', '=', $cpf)->first();

        if ($pessoaFisica) {
          $action = new TAction(['ClienteForm', 'onEdit'], ['key' => $pessoaFisica->cliente->id]);
          new TMessage('info', 'Cliente já cadastrado', $action);
        }
      }
    } catch (Exception $e) {
      new TMessage('info', $e->getMessage());
    } finally {
      TTransaction::close();
    }
  }
}

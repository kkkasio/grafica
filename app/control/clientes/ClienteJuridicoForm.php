<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TTransaction;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * ClienteForm Registration
 * @author  <Kásio Eduardo>
 */
class ClienteJuridicoForm extends TPage
{
  protected $form; // form
  protected $detail_list;

  use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods

  /**
   * Class constructor
   * Creates the page and the registration form
   */
  function __construct()
  {
    parent::__construct();


    $this->setDatabase('grafica');              // defines the database
    $this->setActiveRecord('Cliente');     // defines the active record

    // creates the form
    $this->form = new BootstrapFormBuilder('form_Cliente');
    $this->form->setFormTitle('Cliente - Pessoa Juridica');
    $this->form->setClientValidation(true);
    $this->form->generateAria();


    // create the form fields
    $id = new THidden('id');
    $nome = new TEntry('nome');
    $email = new TEntry('email');
    $tipo = new TEntry('tipo');

    $cnpj = new TEntry('cnpj');
    $id_empresa = new THidden('id_empresa');

    $cep = new TEntry('cep');
    $logradouro = new TEntry('logradouro');
    $numero = new TEntry('numero');
    $complemento = new TEntry('complemento');
    $bairro = new TEntry('bairro');

    $filtro = new TCriteria;
    $filtro->add(new TFilter('id', '<', '0'));

    $estado_id = new TDBCombo('estado_id', 'grafica', 'Estado', 'id', 'nome');
    $cidade_id = new TDBCombo('cidade_id', 'grafica', 'Cidade', 'id', 'nome');

    //detalhe telefone
    $detail_uniqid = new THidden('detail_uniqid');
    $detail_id = new THidden('detail_id');
    $detail_tipo = new TCombo('detail_tipo');
    $detail_ddd = new TEntry('detail_ddd');
    $detail_numero = new TEntry('detail_numero');

    $detail_tipo->addItems(['Fixo' => 'Fixo', 'Celular' => 'Celular']);
    $detail_tipo->setSize('30%');
    $detail_ddd->setMaxLength(2);


    $estado_id->setChangeAction(new TAction([$this, 'onChangeEstado']));
    $cep->setExitAction(new TAction([$this, 'onExitCEP']));
    $cnpj->setExitAction(new TAction([$this, 'onExitCNPJ']));


    // add the fields
    $this->form->addFields([], [$id], [], [$id_empresa]);
    $this->form->addFields([new TLabel('CNPJ')], [$cnpj], [new TLabel('Email')], [$email]);
    $this->form->addFields([new TLabel('Nome Fantasia')], [$nome], [new TLabel('Tipo')], [$tipo]);


    $this->form->addContent([new TFormSeparator('Endereço')]);

    $this->form->addFields([new TLabel('CEP')], [$cep])->layout = ['col-sm-2 control-label', 'col-sm-4'];
    $this->form->addFields([new TLabel('Logradouro')], [$logradouro], [new TLabel('Numero')], [$numero]);
    $this->form->addFields([new TLabel('Complemento')], [$complemento], [new TLabel('Bairro')], [$bairro]);
    $this->form->addFields([new TLabel('Estado')], [$estado_id], [new TLabel('Cidade')], [$cidade_id]);


    $cnpj->setMask('99.999.999/9999-99');
    $cep->setMask('99999-999');
    $tipo->setValue('Jurídica');
    $tipo->setEditable(FALSE);

    $id->setSize('100%');
    $nome->setSize('100%');
    $email->setSize('100%');


    $cidade_id->enableSearch();
    $estado_id->enableSearch();



    if (!empty($id)) {
      $id->setEditable(FALSE);
      $id_empresa->setEditable(FALSE);
    }

    $nome->addValidation('Nome Fantasia', new TRequiredValidator);
    $email->addValidation('Email', new TEmailValidator);
    $cnpj->addValidation('CNPJ', new TRequiredValidator);
    $cep->addValidation('CEP', new TRequiredValidator);
    $logradouro->addValidation('Logradouro', new TRequiredValidator);
    $complemento->addValidation('Complemento', new TRequiredValidator);
    $estado_id->addValidation('Estado', new TRequiredValidator);
    $cidade_id->addValidation('Cidade', new TRequiredValidator);
    $numero->addValidation('Numero', new TRequiredValidator);


    // details fields
    $this->form->addContent([new TFormSeparator('Telefones')]);
    $this->form->addFields([$detail_uniqid]);
    $this->form->addFields([$detail_id]);

    $add = TButton::create('add', [$this, 'onDetailAdd'], 'Adicionar Telefone', 'fa:plus-circle green');
    $add->getAction()->setParameter('static', '1');

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

    // create the form actions
    $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save green');
    //$btn->class = 'btn btn-sm btn-primary';
    $this->form->addActionLink('Novo Cliente',  new TAction([$this, 'onEdit']), 'fa:eraser red');

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 100%';
    $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);

    parent::add($container);
  }

  public function onClear($param)
  {
    $this->form->clear();
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

      /*
      if (empty($data->ddd)) {
        throw new Exception('The field fieldX is required');
      }*/

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

        $pessoaJuridica = PessoaJuridica::where('cliente_id', '=', $key)->load();
        $empresa = new stdClass;
        $empresa = $pessoaJuridica[0];
        $empresa->id_empresa = $pessoaJuridica[0]->id;

        $this->form->setData($empresa);

        foreach ($items as $item) {
          $item->uniqid = uniqid();
          $row = $this->detail_list->addItem($item);
          $row->id = $item->uniqid;
        }
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


  public function onSave($param)
  {
    try {
      TTransaction::open('grafica');

      $data = $this->form->getData();
      $this->form->validate();

      $master = new Cliente;
      $master->fromArray((array) $data);
      $master->store();


      $pessoaJuridica = new PessoaJuridica;
      $pessoaJuridica->fromArray((array) $data);

      if (!empty($data->id_empresa)) {
        $pessoaJuridica->id = $data['id_empresa'];
      }


      $pessoaJuridica->cliente_id = $master->id;
      $pessoaJuridica->store();

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
      TTransaction::close();

      TForm::sendData('form_Cliente', (object) ['id' => $master->id]);

      new TMessage('info', 'Cliente foi Salvo');
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
      $this->form->setData($this->form->getData());
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

  public static function onExitCNPJ($param)
  {
    try {
      if (!empty($param['cnpj'])) {
        TTransaction::open('grafica');

        $pessoaJuridica = PessoaJuridica::where('cnpj', '=', $param['cnpj'])->first();

        if ($pessoaJuridica) {
          $action = new TAction(['ClienteJuridicoForm', 'onEdit'], ['key' => $pessoaJuridica->cliente->id]);
          new TMessage('info', 'Cliente já cadastrado', $action);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $param['cnpj']);
        $url =  "https://www.receitaws.com.br/v1/cnpj/" . $cnpj;

        $content = @file_get_contents($url);

        if ($content !== false) {
          $cnpj_data = json_decode($content);
          $data = new stdClass;

          if (is_object($cnpj_data) && $cnpj_data->status !== 'ERRO') {
            $data->nome  = $cnpj_data->nome;
            $data->email = $cnpj_data->email;
            $data->cep   = $cnpj_data->cep;

            TForm::sendData('form_Cliente', $data, false, true);
          }
        }
      }
    } catch (Exception $e) {
      new TMessage('info', $e->getMessage());
    } finally {
      TTransaction::close();
    }
  }
}

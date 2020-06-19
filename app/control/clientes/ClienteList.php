<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;


/**
 * ClienteList Listing
 * @author  <Kásio Eduardo>
 */
class ClienteList extends TPage
{
  protected $form;     // registration form
  protected $datagrid; // listing
  protected $pageNavigation;
  protected $formgrid;
  protected $deleteButton;

  use Adianti\base\AdiantiStandardListTrait;

  /**
   * Page constructor
   */
  public function __construct()
  {
    parent::__construct();

    $this->setDatabase('grafica');            // defines the database
    $this->setActiveRecord('Cliente');   // defines the active record
    $this->setDefaultOrder('id', 'asc');         // defines the default order
    $this->setLimit(10);
    // $this->setCriteria($criteria) // define a standard filter


    $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
    $this->addFilterField('email', 'like', 'email'); // filterField, operator, formField

    // creates the form
    $this->form = new BootstrapFormBuilder('form_search_Cliente');
    $this->form->setFormTitle('Cliente');


    // create the form fields

    $nome = new TEntry('nome');
    $email = new TEntry('email');


    // add the fields

    $this->form->addFields([new TLabel('Nome')], [$nome]);
    $this->form->addFields([new TLabel('Email')], [$email]);


    // set sizes
    $nome->setSize('100%');
    $email->setSize('100%');


    // keep the form filled during navigation with session data
    $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

    // add the search form actions
    $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
    $btn->class = 'btn btn-sm btn-primary waves-effect';
    $btnFisico = $this->form->addActionLink(('Novo Cliente Pessoa Física'), new TAction(['ClienteForm', 'onClear']), 'fa:plus withe');
    $btnFisico->class = 'btn btn-lg btn-success waves-effect';
    $btnJuridico = $this->form->addActionLink(('Novo Cliente Pessoa Jurídica'), new TAction(['ClienteJuridicoForm', 'onClear']), 'fa:plus withe');
    $btnJuridico->class = 'btn btn-success waves-effect';

    // creates a Datagrid
    $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
    $this->datagrid->style = 'width: 100%';
    $this->datagrid->datatable = 'true';

    // creates the datagrid columns
    $column_id = new TDataGridColumn('id', '#', 'right');
    $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
    $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'left');
    $column_email = new TDataGridColumn('email', 'Email', 'left');
    $column_telefone = new TDataGridColumn('Telefone', 'Telefone', 'left');
    $column_created_at = new TDataGridColumn('created_at', 'Data de Registro', 'left');

    // add the columns to the DataGrid
    $this->datagrid->addColumn($column_id);
    $this->datagrid->addColumn($column_nome);
    $this->datagrid->addColumn($column_tipo);
    $this->datagrid->addColumn($column_email);
    //$this->datagrid->addColumn($column_telefone);
    $this->datagrid->addColumn($column_created_at);


    $column_created_at->setTransformer(function ($value) {
      if ($value) {
        try {
          $date = new DateTime($value);
          return $date->format('d/m/Y hh:mm');
        } catch (Exception $e) {
          return $value;
        }
      }
      return $value;
    });


    $action1 = new TDataGridAction(array($this, 'onView'), ['tipo' => '{tipo}', 'id' => '{id}']);
    //$action1->setDisplayCondition(array($this, 'displayColumn'));

    $action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);
    $action3 = new TDataGridAction(array($this, 'onView'), ['tipo' => '{tipo}', 'id' => '{id}']);

    $this->datagrid->addAction($action3, 'Visualizar', 'fa:search green');
    $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
    $this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');


    $this->datagrid->createModel();

    $this->pageNavigation = new TPageNavigation;
    $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

    $panel = new TPanelGroup('', 'white');
    $panel->add($this->datagrid);
    $panel->addFooter($this->pageNavigation);

    // header actions
    $dropdown = new TDropDown(_t('Export'), 'fa:list');
    $dropdown->setPullSide('right');
    $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
    $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table blue');
    $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf red');
    $panel->addHeaderWidget($dropdown);

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 100%';
    $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);
    $container->add($panel);

    parent::add($container);
  }

  public function displayColumn($object)
  {
    var_dump($object->tipo);
    if ($object->tipo === 'Física') {
      return TRUE;
    }
    return FALSE;
  }

  public static function onView($param)
  {

    $parametros['key'] = $param['id'];

    if ($param['tipo'] === 'Física') {
      AdiantiCoreApplication::loadPage('ClienteForm', 'onEdit', $parametros);
    } else {
      AdiantiCoreApplication::loadPage('ClienteJuridicoForm', 'onEdit',  $parametros);
    }
  }
}

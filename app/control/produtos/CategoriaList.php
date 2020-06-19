<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * CategoriaList Listing
 * @author  <your name here>
 */
class CategoriaList extends TPage
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
    $this->setActiveRecord('Categoria');   // defines the active record
    $this->setDefaultOrder('id', 'asc');         // defines the default order
    $this->setLimit(10);
    // $this->setCriteria($criteria) // define a standard filter

    $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
    $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
    $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField

    // creates the form
    $this->form = new BootstrapFormBuilder('form_search_Categoria');
    $this->form->setFormTitle('Categoria');


    // create the form fields

    $nome = new TEntry('nome');
    $ativo = new TRadioGroup('ativo');

    $ativo->addItems(['Y' => 'Sim', 'N' => 'Não']);
    $ativo->setLayout('horizontal');
    $ativo->setValue('Y');


    // add the fields    
    $this->form->addFields([new TLabel('Nome')], [$nome]);
    $this->form->addFields([new TLabel('Ativo')], [$ativo]);


    // set sizes
    $nome->setSize('100%');
    $ativo->setSize('100%');


    // keep the form filled during navigation with session data
    $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

    // add the search form actions
    $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
    $btn->class = 'btn btn-sm btn-primary';
    $this->form->addActionLink(_t('New'), new TAction(['CategoriaForm', 'onEdit']), 'fa:plus green');

    // creates a Datagrid
    $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
    $this->datagrid->style = 'width: 100%';
    $this->datagrid->datatable = 'true';
    // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


    // creates the datagrid columns
    $column_id = new TDataGridColumn('id', '#', 'right');
    $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
    $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');
    $column_created_at = new TDataGridColumn('created_at', 'Criado em', 'left');
    $column_updated_at = new TDataGridColumn('updated_at', 'Última Atualização', 'left');

    $column_ativo->setTransformer(function ($value, $object, $row) {

      if ($value == 'Y') {
        $div = new TElement('span');
        $div->class = "label label-success";
        $div->style = "text-shadow:none; font-size:13px";
        $div->add('Sim');
        return $div;
      } else if ($value == 'N') {
        $div = new TElement('span');
        $div->class = "label label-danger";
        $div->style = "text-shadow:none; font-size:13px";
        $div->add('Não');
        return $div;
      }
    });

    $column_created_at->setTransformer(function ($value) {
      if ($value) {
        try {
          $date = new DateTime($value);
          return $date->format('d/m/Y - H:m');
        } catch (Exception $e) {
          return $value;
        }
      }
      return $value;
    });

    $column_updated_at->setTransformer(function ($value) {
      if ($value) {
        try {
          $date = new DateTime($value);
          return $date->format('d/m/Y - H:m');
        } catch (Exception $e) {
          return $value;
        }
      }
      return $value;
    });


    // add the columns to the DataGrid
    $this->datagrid->addColumn($column_id);
    $this->datagrid->addColumn($column_nome);
    $this->datagrid->addColumn($column_ativo);
    $this->datagrid->addColumn($column_created_at);
    $this->datagrid->addColumn($column_updated_at);


    $action1 = new TDataGridAction(['CategoriaForm', 'onEdit'], ['id' => '{id}']);
    $action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

    $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
    $this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');

    // create the datagrid model
    $this->datagrid->createModel();

    // creates the page navigation
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
    // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);
    $container->add($panel);

    parent::add($container);
  }
}

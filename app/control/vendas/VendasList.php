<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * VendasList Listing
 * @author  <your name here>
 */
class VendasList extends TPage
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
		$this->setActiveRecord('Venda');   // defines the active record
		$this->setDefaultOrder('id', 'asc');         // defines the default order
		$this->setLimit(10);
		// $this->setCriteria($criteria) // define a standard filter

		$this->addFilterField('id', '=', 'id'); // filterField, operator, formField
		$this->addFilterField('numero', 'like', 'numero'); // filterField, operator, formField
		$this->addFilterField('cliente_id', '=', 'cliente_id'); // filterField, operator, formField
		$this->addFilterField('vendedor_id', 'like', 'vendedor_id'); // filterField, operator, formField
		$this->addFilterField('forma_pagamento', 'like', 'forma_pagamento'); // filterField, operator, formField
		$this->addFilterField('created_at', 'like', 'created_at'); // filterField, operator, formField

		// creates the form
		$this->form = new BootstrapFormBuilder('form_search_Vendas');
		$this->form->setFormTitle('Vendas');


		// create the form fields
		$id = new TEntry('id');
		$numero = new TEntry('numero');
		$cliente_id = new TDBUniqueSearch('cliente_id', 'grafica', 'Cliente', 'id', 'nome');
		$vendedor_id = new TEntry('vendedor_id');
		$forma_pagamento = new TCombo('forma_pagamento');
		$created_at = new TEntry('created_at');


		$formas_pagamento = ['D' => 'Dinheiro', 'C' => 'Crédito',  'DE' => 'Débito', 'B' => 'Boleto'];
		$forma_pagamento->addItems($formas_pagamento);


		// add the fields
		$this->form->addFields([new TLabel('#')], [$id]);
		$this->form->addFields([new TLabel('Número')], [$numero]);
		$this->form->addFields([new TLabel('Cliente')], [$cliente_id]);
		$this->form->addFields([new TLabel('Vendedor')], [$vendedor_id]);
		$this->form->addFields([new TLabel('Forma Pagamento')], [$forma_pagamento]);
		$this->form->addFields([new TLabel('Data')], [$created_at]);


		// set sizes
		$id->setSize('100%');
		$numero->setSize('100%');
		$cliente_id->setSize('100%');
		$vendedor_id->setSize('100%');
		$forma_pagamento->setSize('100%');
		$created_at->setSize('100%');


		// keep the form filled during navigation with session data
		$this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

		// add the search form actions
		$btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
		$btn->class = 'btn btn-sm btn-primary';
		$this->form->addActionLink(_t('New'), new TAction(['VendaForm', 'onEdit']), 'fa:plus green');

		// creates a Datagrid
		$this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
		$this->datagrid->style = 'width: 100%';
		$this->datagrid->datatable = 'true';
		// $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


		// creates the datagrid columns
		$column_id = new TDataGridColumn('id', '#', 'right');
		$column_numero = new TDataGridColumn('numero', 'Número', 'left');
		$column_cliente_id = new TDataGridColumn('cliente->nome', 'Cliente', 'right');
		$column_vendedor_id = new TDataGridColumn('vendedor->name', 'Vendedor', 'right');
		$column_valor_real = new TDataGridColumn('valor_real', 'Valor', 'left');
		$column_desconto = new TDataGridColumn('desconto', 'Desconto', 'right');
		$column_forma_pagamento = new TDataGridColumn('forma_pagamento', 'Forma Pagamento', 'left');
		$column_status = new TDataGridColumn('status', 'Status', 'left');
		$column_created_at = new TDataGridColumn('created_at', 'Data', 'left');


		// add the columns to the DataGrid
		$this->datagrid->addColumn($column_id);
		$this->datagrid->addColumn($column_numero);
		$this->datagrid->addColumn($column_created_at);
		$this->datagrid->addColumn($column_cliente_id);
		$this->datagrid->addColumn($column_vendedor_id);
		$this->datagrid->addColumn($column_valor_real);
		$this->datagrid->addColumn($column_desconto);
		$this->datagrid->addColumn($column_forma_pagamento);

		$this->datagrid->addColumn($column_status);


		$column_created_at->setTransformer(function ($value) {
			return TDate::date2br($value);
		});

		$column_desconto->setTransformer(function ($value) {
			return $value . '%';
		});

		//$action1 = new TDataGridAction(['VendasForm', 'onEdit'], ['id' => '{id}']);
		$action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

		//$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
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

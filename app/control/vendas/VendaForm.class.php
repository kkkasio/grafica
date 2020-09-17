<?php

use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapDatagridWrapper;

/**
 * Vendas
 * @author  <Kásio Eduardo>
 */
class VendaForm extends TPage
{
	protected $form;

	function __construct()
	{
		parent::__construct();

		$this->form = new BootstrapFormBuilder('form_Venda');
		$this->form->setFormTitle('Nova Venda');
		$this->form->setClientValidation(true);

		$id          = new TEntry('id');
		$data        = new TDate('data');
		$cliente_id = new TDBUniqueSearch('cliente_id', 'grafica', 'Cliente', 'id', 'nome');
		$forma_pagamento = new TCombo('forma_pagamento');

		$formas_pagamento = ['Dinheiro', 'Crédito', 'Débito', 'Boleto'];
		$forma_pagamento->addItems($formas_pagamento);


		$produto_detail_unqid     		 = new THidden('produto_detail_uniqid');
		$produto_detail_id        	 	 = new THidden('produto_detail_id');
		$produto_detail_produto_id  	 = new TDBUniqueSearch('produto_detail_produto_id', 'grafica', 'Produto', 'id', 'nome');
		$produto_detail_preco     		 = new TEntry('produto_detail_preco');
		$produto_detail_quantidade     = new TEntry('produto_detail_quantidade');
		$porcentagem_desconto			 = new TEntry('porcentagem_desconto');
		$produto_detail_total	       = new TEntry('produto_detail_total');

		$id->setEditable(false);
		$cliente_id->setSize('100%');
		$cliente_id->setMinLength(1);
		$data->setSize('100%');
		$forma_pagamento->setSize('100%');
		$produto_detail_produto_id->setSize('100%');
		$produto_detail_produto_id->setMinLength(1);
		$produto_detail_preco->setSize('100%');
		$produto_detail_quantidade->setSize('100%');

		$data->addValidation('Data', new TRequiredValidator);
		$cliente_id->addValidation('Cliente', new TRequiredValidator);
		$forma_pagamento->addValidation('Forma de Pagamento', new TRequiredValidator);


		$produto_detail_produto_id->setChangeAction(new TAction([$this, 'onProductChange']));

		// add master form fields
		$this->form->addFields(
			[new TLabel('ID')],
			[$id],
			[new TLabel('Data (*)', '#FF0000')],
			[$data]
		);
		$this->form->addFields([new TLabel('Cliente (*)', '#FF0000')], [$cliente_id], [new TLabel('Forma de Pagamento', '#FF0000')], [$forma_pagamento]);


		$this->form->addContent(['<h4>Produtos</h4><hr>']);
		$this->form->addFields([$produto_detail_unqid], [$produto_detail_id]);
		$this->form->addFields(
			[new TLabel('Produto (*)', '#FF0000')],
			[$produto_detail_produto_id],
			[new TLabel('Quantidade(*)', '#FF0000')],
			[$produto_detail_quantidade]
		);

		$this->form->addFields(
			[new TLabel('Preço (*)', '#FF0000')],
			[$produto_detail_preco],
			[new Tlabel('Desconto (%)', '#FF0000')],
			[$porcentagem_desconto]
		);


		$add_product = TButton::create('add_product', [$this, 'onProductAdd'], 'Adicionar Produto', 'fa:plus-circle green');
		$add_product->getAction()->setParameter('static', '1');
		$this->form->addFields([], [$add_product]);


		$this->produto_lista = new BootstrapDatagridWrapper(new TDataGrid);
		$this->produto_lista->setHeight(150);
		$this->produto_lista->makeScrollable();
		$this->produto_lista->setId('produtos_lista');
		$this->produto_lista->generateHiddenFields();
		$this->produto_lista->style = "min-width: 700px; width:100%;margin-bottom: 10px";

		$col_uniq   	 = new TDataGridColumn('uniqid', 'Uniqid', 'center', '4%');
		$col_id     	 = new TDataGridColumn('id', 'ID', 'center', '10%');
		$col_pid    	 = new TDataGridColumn('produto_id', '#', 'center', '15%');
		$col_nome       = new TDataGridColumn('produto_id', 'Produto', 'left', '35%');
		$col_quantidade = new TDataGridColumn('quantidade', 'Quantidade', 'left', '15%');
		$col_preco  	 = new TDataGridColumn('preco_venda', 'Preço', 'right', '15%');
		//$col_disc   = new TDataGridColumn('discount', 'Discount', 'right', '15%');
		$col_subt   	 = new TDataGridColumn('={quantidade} * ( {preco_venda} ) ', 'Subtotal', 'right', '20%');

		$this->produto_lista->addColumn($col_uniq);
		$this->produto_lista->addColumn($col_id);
		$this->produto_lista->addColumn($col_pid);
		$this->produto_lista->addColumn($col_nome);
		$this->produto_lista->addColumn($col_quantidade);
		$this->produto_lista->addColumn($col_preco);
		$this->produto_lista->addColumn($col_subt);


		$col_nome->setTransformer(function ($value) {
			return Produto::findInTransaction('grafica', $value)->nome;
		});



		//$col_subt->enableTotal('sum', 'R$', 2, ',', '.');

		$col_subt->setTotalFunction(function ($values) {

			return array_sum((array) $values + 500);
		});


		$col_id->setVisibility(false);
		$col_uniq->setVisibility(false);

		$action1 = new TDataGridAction([$this, 'onEditItemProduto']);
		$action1->setFields(['uniqid', '*']);

		$action2 = new TDataGridAction([$this, 'onDeleteItem']);
		$action2->setField('uniqid');

		$this->produto_lista->addAction($action1, _t('Edit'), 'far:edit blue');
		$this->produto_lista->addAction($action2, _t('Delete'), 'far:trash-alt red');

		$this->produto_lista->createModel();

		$panel = new TPanelGroup;
		$panel->add($this->produto_lista);
		$panel->getBody()->style = 'overflow-x:auto';
		$this->form->addContent([$panel]);

		$format_value = function ($value) {
			if (is_numeric($value)) {
				return 'R$ ' . number_format($value, 2, ',', '.');
			}
			return $value;
		};

		$col_preco->setTransformer($format_value);
		$col_subt->setTransformer($format_value);


		$this->form->addAction('Save',  new TAction([$this, 'onSave'], ['static' => '1']), 'fa:save green');
		$this->form->addAction('Clear', new TAction([$this, 'onClear']), 'fa:eraser red');


		// create the page container
		$container = new TVBox;
		$container->style = 'width: 100%';
		//$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
		$container->add($this->form);
		parent::add($container);
	}

	public function onProductAdd($param)
	{
		try {
			$this->form->validate();
			$data = $this->form->getData();

			if ((!$data->produto_detail_produto_id) || (!$data->produto_detail_quantidade) || (!$data->produto_detail_preco)) {
				throw new Exception('Os Campos Produto, Quantidade e Preço são obrigatórios');
			}

			$uniqid = !empty($data->produto_detail_uniqid) ? $data->produto_detail_uniqid : uniqid();

			$grid_data = [
				'uniqid'      => $uniqid,
				'id'          => $data->produto_detail_id,
				'produto_id'  => $data->produto_detail_produto_id,
				'quantidade'  => $data->produto_detail_quantidade,
				'preco_venda' => $data->produto_detail_preco,
			];


			// insert row dynamically
			$row = $this->produto_lista->addItem((object) $grid_data);
			$row->id = $uniqid;

			TDataGrid::replaceRowById('produtos_lista', $uniqid, $row);

			// clear product form fields after add
			$data->produto_detail_uniqid         = '';
			$data->produto_detail_id             = '';
			$data->produto_detail_nome           = '';
			$data->produto_detail_quantidade     = '';
			$data->produto_detail_preco          = '';
			$data->produto_detail_produto_id     = '';


			// send data, do not fire change/exit events
			TForm::sendData('form_Venda', $data, false, false);
		} catch (Exception $e) {
			$this->form->setData($this->form->getData());
			new TMessage('error', $e->getMessage());
		}
	}

	public static function onEditItemProduto($param)
	{
		$data = new stdClass;
		$data->produto_detail_uniqid      = $param['uniqid'];
		$data->produto_detail_id          = $param['id'];
		$data->produto_detail_produto_id  = $param['produto_id'];
		$data->produto_detail_quantidade  = $param['quantidade'];
		$data->produto_detail_preco_venda = $param['preco_venda'];
		//$data->produto_detail_discount   = $param['discount'];

		// send data, do not fire change/exit events
		TForm::sendData('form_Venda', $data, false, false);
	}

	public static function onProductChange($params)
	{
		//$params['produto_detail_id']
		if (!empty($params['produto_detail_produto_id'])) {

			try {
				TTransaction::open('grafica');
				$produto   = new Produto($params['produto_detail_produto_id']);
				TForm::sendData('form_Venda', (object) ['produto_detail_preco' => $produto->valor_venda]);
				TTransaction::close();
			} catch (Exception $e) {
				new TMessage('error', $e->getMessage());
				TTransaction::rollback();
			}
		}
	}

	public static function onDeleteItem($param)
	{
		$data = new stdClass;
		$data->produto_detail_uniqid     = '';
		$data->produto_detail_id         = '';
		$data->produto_detail_produto_id = '';
		$data->produto_detail_quantidade = '';
		$data->produto_detail_preco      = '';
		$data->produto_detail_discount   = '';

		// send data, do not fire change/exit events
		TForm::sendData('form_Sale', $data, false, false);

		// remove row
		TDataGrid::removeRowById('produtos_lista', $param['uniqid']);
	}

	public function onSave($param)
	{
		try {
			TTransaction::open('grafica');

			$data = $this->form->getData();
			$this->form->validate();



			$venda = new Venda;
			$venda->fromArray((array) $data);
			$venda->numero = uniqid('DZ7');
			$venda->cliente_id = $data->cliente_id;
			$venda->vendedor_id = $_SESSION["erpmeuovoduro"]['userid'];
			$venda->desconto = 0;
			$venda->forma_pagamento = $data->forma_pagamento;
			$venda->status = 'F';
			$venda->store();

			VendaItem::where('venda_id', '=', $venda->id)->delete();

			$total = 0;
			if (!empty($param["produtos_lista_produto_id"])) {
				foreach ($param["produtos_lista_produto_id"] as $key => $item_id) {
					$item = new VendaItem;
					$item->produto_id  = $item_id;
					$item->quantidade  = (float) $param["produtos_lista_quantidade"][$key];
					$item->preco		 = (float) $param["produtos_lista_preco_venda"][$key];
					$item->total       = ($item->preco * $item->quantidade);

					$item->venda_id = $venda->id;
					$item->store();
					$total += $item->total;
				}
			}

			$venda->valor_real = $total;
			$venda->valor_recebido = $total;
			$venda->store(); // stores the object

			TForm::sendData('form_Venda', (object) ['id' => $venda->id]);

			TTransaction::close(); // close the transaction
			new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
		} catch (Exception $e) // in case of exception
		{
			new TMessage('error', $e->getMessage());
			$this->form->setData($this->form->getData()); // keep form data
			TTransaction::rollback();
		}
	}

	public function onLoad($param)
	{
		$data = new stdClass;
		$data->cliente_id   = $param['cliente_id'];
		$this->form->setData($data);
	}
	function onClear($param)
	{
		$this->form->clear();
	}


	public function onEdit($param)
	{
	}
}

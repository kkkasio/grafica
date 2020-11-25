<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\THyperLink;
use Adianti\Widget\Util\TTextDisplay;
use Adianti\Widget\Util\TTreeView;
use Adianti\Wrapper\BootstrapFormBuilder;

class VendaView extends TPage
{

  private $form;
  private $datagridProdutos;
  private $datagridPagamentos;
  public function __construct($param)
  {


    try {

      parent::__construct();

      if (!isset($param['id'])) {
        //$action = new TAction('VendasList');
        /*
          redirecionar para VendasList
        */

        throw new Exception('Erro ao carregar a venda');
      }

      $this->form = new BootstrapFormBuilder('form_VendaView');
      $this->form->appendPage('Dados do Cliente');
      $this->form->setFormTitle('Visualizar Venda');

      TTransaction::open('grafica');
      $venda = new Venda($param['id']);
      $cliente = $venda->cliente;

      $this->form->addFields(
        [new TLabel('Nome:')],
        [new TTextDisplay($cliente->nome)],
        [new TLabel('Email:')],
        [new TTextDisplay($cliente->email)],
        [new TLabel('Tipo:')],
        [new TTextDisplay($cliente->tipo)]
      );

      $this->form->addFields([new TLabel('CEP:')], [new TTextDisplay($cliente->cep)]);
      $this->form->addFields([new TLabel('Endereço:')], [new TTextDisplay($cliente->logradouro)], [new TLabel('Bairro:')], [new TTextDisplay($cliente->bairro)], [new TLabel('Número:')], [new TTextDisplay($cliente->numero)]);
      $this->form->addFields([new TLabel('Complemento:')], [new TTextDisplay($cliente->complemento)], [new TLabel('Cidade:')], [new TTextDisplay($cliente->cidade->nome)], [new TLabel('Estado:')], [new TTextDisplay($cliente->estado->nome)]);


      $this->form->appendPage('Produtos');
      $this->datagridProdutos = new BootstrapDatagridWrapper(new TDataGrid);
      $this->datagridProdutos->style = 'width: 100%';
      $this->datagridProdutos->datatable = 'true';

      $this->datagridProdutos->disableDefaultClick();

      $column_produto_id    = new TDataGridColumn('id_produto_venda', '#', 'left');
      $column_produto    = new TDataGridColumn('produto->nome', 'Produto', 'left');
      $column_quantidade = new TDataGridColumn('quantidade', 'Quantidade', 'left');
      $column_total      = new TDataGridColumn('total', 'Valor Total', 'left');
      $column_arte       = new TDataGridColumn('arte', 'Arte', 'center');

      $actionArte = new TDataGridAction(['UploadArte', 'onReload'], ['id_produto_venda' => '{id_produto_venda}']);
      $this->datagridProdutos->addAction($actionArte, 'Enviar Arte',   'fa:upload green');


      $this->form->addFields([new TLabel('ID: ')], [new TTextDisplay($venda->numero)], [new TLabel('Status: ')], [new TTextDisplay($venda->status)], [new TLabel('Vendedor')], [new TTextDisplay($venda->vendedor->name)]);
      $this->form->addFields([new TLabel('Total da Venda: ')], [new TTextDisplay('R$ ' . number_format($venda->valor_real, 2, ',', '.'))], [new TLabel('Forma Pagamento')], [new TTextDisplay($venda->forma_pagamento)], [new TLabel('Previsão de Entrega')], [new TTextDisplay($venda->previsao_entrega)]);


      $this->datagridProdutos->addColumn($column_produto_id)->setVisibility(false);
      $this->datagridProdutos->addColumn($column_produto);
      $this->datagridProdutos->addColumn($column_quantidade);
      $this->datagridProdutos->addColumn($column_total);
      $this->datagridProdutos->addColumn($column_arte);


      $column_arte->setTransformer(function ($value) {
        if ($value) {
          $b = new THyperLink('VER ARTE', 'download.php?file=' . $value, '#212529', 20, 'b', '', 'fas:external-link-alt white');
          $b->style = 'margin-top:0px';
          return  $b;
        }
        return 'SEM ARTE';
      });




      $this->datagridProdutos->createModel();

      $this->form->addContent([$this->datagridProdutos]);


      foreach ($venda->produtos as $produto) {

        $item = new stdClass;
        $item = $produto;
        $item->id_produto_venda = $produto->id;
        unset($item->id);
        $this->datagridProdutos->addItem($item);
        unset($item);
      }

      $this->form->appendPage('Pagamentos');
      $this->datagridPagamentos = new BootstrapDatagridWrapper(new TDataGrid);
      $this->datagridPagamentos->style = 'width: 100%';
      $this->datagridPagamentos->datatable = 'true';

      $column_id = new TDataGridColumn('id_pagamento', '#', 'left');
      $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
      $column_data = new TDataGridColumn('data_pagamento', 'Data', 'left');
      $column_pago = new TDataGridColumn('pago', 'Recebido', 'left');

      $this->datagridPagamentos->addColumn($column_id);
      $this->datagridPagamentos->addColumn($column_valor);
      $this->datagridPagamentos->addColumn($column_data);
      $this->datagridPagamentos->addColumn($column_pago);

      $column_data->setTransformer(function ($value) {
        return TDate::date2br($value);
      });

      $action1 = new TDataGridAction(['VendaView', 'onConfirmPagamento'], ['id' => $param['id'], 'idPg' => '{id_pagamento}']);
      $this->datagridPagamentos->addAction($action1, 'Marcar Como Recebido',   'fa:search green');

      $this->datagridPagamentos->createModel();

      $this->form->addContent([$this->datagridPagamentos]);


      foreach ($venda->pagamentos as $item) {
        $itemAux = $item;
        $itemAux->id_pagamento =  $item->id;


        //$itemAux->id_pagamento = $item->id;
        $this->datagridPagamentos->addItem($itemAux);
      }


      $container = new TVBox;
      $container->style = 'width: 100%';
      // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
      $container->add($this->form);

      parent::add($container);
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    } finally {
      TTransaction::close();
    }
  }

  public function onReload($param = null)
  {
    if (isset($param['tab']))
      $this->form->setCurrentPage($param['tab']);
  }

  static public function onConfirmPagamento($param)
  {

    $action1  = new TAction(array('VendaView', 'onAtualizaPagamento'));
    $action1->setParameter('id', $param['id']);
    $action1->setParameter('id_pagamento', $param['id_pagamento']);


    new TQuestion('Deseja Confirmar o recebimento do pagamento?', $action1);
  }

  public function onAtualizaPagamento($param)
  {

    try {
      TTransaction::open('grafica');
      $pagamento = new Pagamentos($param['id_pagamento']);
      $pagamento->pago = 'S';
      //$pagamento->store();


      $action = new TAction([__CLASS__, 'onReload'], ['id' => $param['id'], 'tab' => '2']);

      new TMessage('info', 'Pagamento atualizado com sucesso!', $action);
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    } finally {
      TTransaction::close();
    }
  }

  public function onEnviarArte($param)
  {
    var_dump($param);
  }
}

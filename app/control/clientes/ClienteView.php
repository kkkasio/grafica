<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;

class ClienteView extends TPage
{
  protected $form;
  protected $telefone_list;
  private $cliente;


  public function __construct($param)
  {
    parent::__construct();

    try {
      TTransaction::open('grafica');

      $cliente  = new Cliente($param['key']);
      $this->cliente = $cliente;

      $painel = new TPanelGroup('Visualizando Cliente - ' . $this->cliente->nome);
      $html = new THtmlRenderer('app/resources/teste.html');

      $painel->addHeaderActionLink('Cirar novo Orçamento', new TAction([$this, 'func1']), 'far:file-pdf red');


      $painel->addFooter('Cú de mariola');


      $indicator1 = new THtmlRenderer('app/resources/info-box.html');
      $indicator2 = new THtmlRenderer('app/resources/info-box.html');

      $indicator1->enableSection('main', ['title' => 'Total Gasto', 'icon' => 'user', 'background' => 'green', 'value' => '50']);
      $indicator2->enableSection('main', ['title' => _t('Groups'), 'icon' => 'users', 'background' => 'blue',   'value' => '100']);

      // replace the main section variables
      $html->enableSection('main', [
        'indicator1' => $indicator1,
        'indicator2' => $indicator2,
      ]);

      $painel->add($html);

      $container = new TVBox;
      $container->style = 'width: 100%';
      $container->add($painel);

      parent::add($container);

      TTransaction::close();
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    }
  }

  public function func1()
  {
  }
}

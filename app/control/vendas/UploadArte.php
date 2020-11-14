<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

class UploadArte extends TWindow
{

  const TARGET_PATH =  'uploads';

  public function __construct($param)
  {
    parent::__construct();
    parent::removePadding();
    parent::removeTitleBar();



    try {
      $this->form = new BootstrapFormBuilder('form_upload_arte');
      $this->form->setFormTitle('Enviar Arte');
      $this->form->setClientValidation(true);

      $id    = new THidden('id_produto_venda');
      $image = new TSlim('imagem');

      $image->container->style = 'width:100%;height:240px;border:2px solid black';
      $image->setDataProperties(['label' => 'Upload imagem', "button-edit-label" => '', "button-remove-label" => '']);

      //tamanho final no máximo 1500x1500 e proporção de 4:3 na janela de visualização
      $image->setDataProperties(['size' => '1500,1500']);
      $this->form->addFields([new TLabel('Arte')],  [$image]);
      $this->form->addFields([$id]);
      $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save green');

      $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');


      $vbox = new TVBox;
      $vbox->style = 'width: 100%';
      $vbox->add($this->form);
      parent::add($vbox);
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    }
  }


  public function onSave($param)
  {
    try {
      $data   = $this->form->getData();
      $images = Slim::getImages();

      TTransaction::open('grafica');

      if ($images) {
        $image = $images[0];
        // save output data if set
        if (isset($image['output']['data'])) {

          $name = $image['output']['name'];
          $extensao = explode('.', $name);

          $nome = uniqid() . '.' . end($extensao);
          $data = $image['output']['data'];


          $object = new VendaItem($param['id_produto_venda']);


          if ($object->arte) {
            if (file_exists($object->arte)) // se existir apaga o anterior
            {
              unlink($object->arte);
            }
          }
          $output = Slim::saveFile($data, time() . '-' . $nome, 'app/uploads/', false);

          $nome  = $output['path'];

          $object->arte = $nome;
          $object->store();

          $action = new TAction(['VendaView', 'onReload']);
          $action->setParameter('id',  $object->venda_id);
          $action->setParameter('tab', '1');

          new TMessage('info', 'Arte enviada com sucesso!', $action);
        }
      }
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
    } finally {
      TTransaction::close();
    }
  }

  public function onReload($param)
  {
    $item = new stdClass;
    $item = $param;
    TForm::sendData('form_upload_arte', $item);
  }

  public function onClose()
  {
    TWindow::closeWindow();
  }
}

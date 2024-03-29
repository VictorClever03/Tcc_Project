<?php

namespace App\Controllers;

use App\Helpers\DataActual;
use App\Helpers\Sessao;
use App\Helpers\Valida;
use App\Helpers\Url;
use App\Libraries\uploads;
use App\Libraries\Controller;
use App\Models\saler\Request;
// prints file
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

date_default_timezone_set('Africa/Luanda');

class  Saler  extends Controller
{
  private $Data;
  private $Food;
  private $Sale;
  private $Perfil;
  private $Request;
  private $Printer;
  private $Prato;
  public function __construct()
  {

    if (Sessao::nivel0()) :
      session_destroy();
      Url::redireciona('client/login');
    endif;
    $this->Data = $this->model("Saler\Usuarios");
    $this->Sale = $this->model("Saler\Venda");
    $this->Perfil = $this->model("Saler\Perfil");
    $this->Request = $this->model("Saler\Request");
    $this->Food = $this->model("client\Home");
    $this->Prato = $this->model("admin\Prato");
    $connector = new WindowsPrintConnector("EPSON");
    $this->Printer = new Printer($connector);
  }

  public function index()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    else :
      Url::redireciona("saler/home");
    endif;

    exit;
  }


  public function login()
  {
    if (Sessao::nivel1()) :
      Url::redireciona("saler");

    endif;

    $formulario = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    //  var_dump($formulario);
    if (isset($formulario['btn_log'])) :
      $dados = [
        'nome' => trim($formulario['nome']),
        'senha' => trim($formulario['senha']),
        'err_nome' => '',
        'err_senha' => ''
      ];

      if (in_array("", $formulario)) :

        if (empty($formulario['nome'])) :
          $dados['err_nome'] = "preencha o campo nome";
          Sessao::izitoast('loginE', 'Erro', 'Preencha todos campos:*nome', 'error');

        endif;

        if (empty($formulario['senha'])) :
          $dados['err_senha'] = "preencha o campo senha";
          Sessao::izitoast('loginE', 'Erro', 'Preencha todos campos:*senha', 'error');

        endif;

      else :

        $checarlogin = $this->Data->checalogin($dados['nome'], $dados['senha'], 1);
        // var_dump($checarlogin);
        // exit;
        if ($checarlogin) :

          Sessao::izitoast('loginS', 'Bemvindo', 'Login realizado com sucesso');
          Url::redireciona('saler');
          $this->criarsessao($checarlogin);
          // var_dump($_SESSION);
          exit;

        else :
          Sessao::izitoast('loginE', 'Erro', 'Nome ou senha estão errados', 'error');
          $dados['err_nome'] = "Dados invalidos";
          $dados['err_senha'] = "Dados invalidos";
        endif;



      endif;
    //  var_dump($formulario);
    else :
      $dados = [
        'nome' => '',
        'senha' => '',
        'err_nome' => '',
        'err_senha' => ''
      ];
    endif;

    $this->view('client/makelogin', compact('dados'));
  }

  private function  criarsessao($usuario)
  {

    $_SESSION['usuarioS_id'] = $usuario['usuario_id'];
    $_SESSION['usuarioS_nome'] = $usuario['u_nome'];
    $_SESSION['usuarioS_email'] = $usuario['email'];
    $_SESSION['usuarioS_img'] = !empty($usuario['imagem']) ? URL . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $usuario['imagem'] : URL . '/public/img/user-logo.jpg';
  }
  public function sair()
  {
    unset($_SESSION['usuarioS_id']);
    unset($_SESSION['usuarioS_nome']);
    unset($_SESSION['usuarioS_email']);
    unset($_SESSION['usuarioS_img']);
    session_destroy();
    Url::redireciona('saler/login');
  }
  public function home()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $allFood = $this->Food->getFood();
    $allcategory = $this->Food->getCategory();




    $file = "home";
    $this->view('layouts/saler/app', compact('file', 'allFood', 'allcategory'));
  }
  public function sale()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $form = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    // var_dump($form);


    // exit;
    $caixa = $this->Sale->saveCaixa($form);
    $venda = $this->Sale->saveSale($form);
    if ($caixa and $venda) {
      Sessao::izitoast('sale', 'Success', 'Venda efectuada');
      Url::redireciona('saler/home');
      try {
        //code...
        $this->printTickets($form);
        $this->printTickets($form);
      } catch (\Exception $th) {
        // simplesmente avança
      }
      exit;
    } else {
      Sessao::izitoast('sale', 'Error', 'Erro na venda', 'error');
      Url::redireciona('saler/home');
      exit;
    }
  }

  private function printTickets($data)
  {

    $this->Printer->setJustification(Printer::JUSTIFY_CENTER);

    /*
        logo
        */
    // try{
    // 	$img = EscposImage::load("logo.png");
    // $this->Printer -> graphics($img);
    // }catch(\Exception $e){/*No hacemos nada si hay error*/}

    /*
        */

    $this->Printer->text("\n" . "Refeitório Anherc" . "\n");
    $this->Printer->text("Direccion: Clever e Padjun #151" . "\n");
    $this->Printer->text("Tel: 244938295867" . "\n");

    $this->Printer->text(date("Y-m-d H:i:s") . "\n");
    $this->Printer->text('Cliente: ' . $data['cliente'] . "\n");
    $this->Printer->text("-----------------------------------------" . "\n");
    $this->Printer->setJustification(Printer::JUSTIFY_CENTER);
    $this->Printer->text("produto          qtd          valor\n");
    $this->Printer->text("-----------------------------------------" . "\n");
    /*

        */
    /**/
    $this->Printer->setJustification(Printer::JUSTIFY_LEFT);
    // $this->Printer->text("Productos\n");
    foreach ($data['id'] as $key => $value) {

      $this->Printer->text($data['nome'][$key] . "  --  " . $data['qtd'][$key] . "  --  " .   $data['total'][$key] . "   \n");
    }
    // $this->Printer->text("Sabrtitas \n");
    // $this->Printer->text("3  pieza    10.00 30.00   \n");
    // $this->Printer->text("Doritos \n");
    // $this->Printer->text("5  pieza    10.00 50.00   \n");
    /*

        */

    $total = 0;
    foreach ($data['total'] as $key => $value) {
      // echo $value.'<br>';
      $total += $value;
    }
    $this->Printer->text("-----------------------------------------" . "\n");
    $this->Printer->setJustification(Printer::JUSTIFY_RIGHT);
    $this->Printer->text("SUBTOTAL: " . $total . " kz\n");
    $this->Printer->text("IVA: 0.00\n");
    $this->Printer->text("TOTAL: " . $total . " kz\n");
    $this->Printer->text("TROCO: " . $data['troco'] . " kz\n");


    /*
        */
    $this->Printer->setJustification(Printer::JUSTIFY_CENTER);
    $this->Printer->text("Agredecemos pela sua compra\n");



    $this->Printer->feed(3);
    $this->Printer->cut();
    $this->Printer->pulse();
    $this->Printer->close();
  }
  private function printTicketsP($data)
  {

    $this->Printer->setJustification(Printer::JUSTIFY_CENTER);

    /*
        logo
        */
    // try{
    // 	$img = EscposImage::load("logo.png");
    // $this->Printer -> graphics($img);
    // }catch(\Exception $e){/*No hacemos nada si hay error*/}

    /*
        */

    $this->Printer->text("\n" . "Refeitório Anherc" . "\n");
    $this->Printer->text("Direccion: Clever e Padjun #151" . "\n");
    $this->Printer->text("Tel: 244938295867" . "\n");

    $this->Printer->text(date("Y-m-d H:i:s") . "\n");
    $this->Printer->text('Cliente: ' . $data['cliente'] . "\n");
    $this->Printer->text("-----------------------------------------" . "\n");
    $this->Printer->setJustification(Printer::JUSTIFY_CENTER);
    $this->Printer->text("prato                     valor\n");
    $this->Printer->text("-----------------------------------------" . "\n");
    /*

        */
    /**/
    $data['pedidos'] = str_replace("<br>", "\n", $data['pedidos']);
    $this->Printer->setJustification(Printer::JUSTIFY_LEFT);
    // $this->Printer->text("Productos\n");
    $this->Printer->text($data['pedidos'] . "   \n");

    // $this->Printer->text("Sabrtitas \n");
    // $this->Printer->text("3  pieza    10.00 30.00   \n");
    // $this->Printer->text("Doritos \n");
    // $this->Printer->text("5  pieza    10.00 50.00   \n");
    /*

        */


    $this->Printer->text("-----------------------------------------" . "\n");
    $this->Printer->setJustification(Printer::JUSTIFY_RIGHT);
    $this->Printer->text("SUBTOTAL: " . $data['total'] . " kz\n");
    $this->Printer->text("IVA: 0.00\n");
    $this->Printer->text("TOTAL: " . $data['total'] . " kz\n");


    /*
        */
    $this->Printer->setJustification(Printer::JUSTIFY_CENTER);
    $this->Printer->text("Agredecemos pela sua compra\n");



    $this->Printer->feed(3);
    $this->Printer->cut();
    $this->Printer->pulse();
    $this->Printer->close();
  }
  // end Home

  // <!-- ========== Start pedidos ========== -->

  public function pedidos()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    // $total = $this->Request->getSumTotal();
    // var_dump();
    // var_dump();

    // $pri = ['1', '2', '3'];
    // $seg = ['4', '5', '6'];
    // var_dump($pri);
    // var_dump($seg);
    // print_r($uni).'<br>';
    // foreach ($uni as $key => $value) {
    //   echo $value['re_nome'] . '<br>'.$value['pr_nome'];
    //   // echo $value['pr_nome'] . '<br>';
    // }
    // exit;

    $all = $this->Request->getAllRequest();

    $file = "pedidos";
    $this->view('layouts/saler/app', compact('file', 'all'));
  }
  public function payment($id)
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $data = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $metodo = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT);
    $form = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    
    if ($id and $metodo == 'POST') {
      if (isset($_POST['btnP'])) {
        $pagar = $this->Request->paymentUser($id,$form['status']);
        if ($pagar) :
          Sessao::izitoast('notify', 'Success', 'Pagamento registrado');
          Url::redireciona('saler/pedidos');
          try {
            //code...
            $this->printTicketsP($data);
          } catch (\Exception $th) {
            // simplesmente avança
          }
          
          exit;
        else :
          Sessao::izitoast('notify', 'Error', 'Pagamento não registrado', 'error');
          Url::redireciona('saler/pedidos');
          exit;
        endif;
      } else {
        Sessao::sms('erro', 'Não clicou no botão, intruso', 'alert alert-danger');
        Url::redireciona('saler/pedidos');
        exit;
      }
    } else {

      Sessao::sms('erro', 'Metodo de envio \'GET\' não é permitido', 'alert alert-danger');
      Url::redireciona('saler/pedidos');
      exit;
    }
  }
  public function deleteRq($id)
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $metodo = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT);
    $form = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    
    if ($id and $metodo == 'POST') {
      if (isset($_POST['btnD'])) {
        $pagar = $this->Request->deleteRq($id,$form['status']);
        if ($pagar) :
          Sessao::izitoast('notify', 'Success', 'Deletado com sucesso');
          Url::redireciona('saler/pedidos');
          exit;
        else :
          Sessao::izitoast('notify', 'Error', 'Não deletado', 'error');
          Url::redireciona('saler/pedidos');
          exit;
        endif;
      } else {
        Sessao::sms('erro', 'Não clicou no botão, intruso', 'alert alert-danger');
        Url::redireciona('saler/pedidos');
        exit;
      }
    } else {

      Sessao::sms('erro', 'Metodo de envio \'GET\' não é permitido', 'alert alert-danger');
      Url::redireciona('saler/pedidos');
      exit;
    }
  }
  public function notify($id)
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $metodo = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT);

    if ($id and $metodo == 'POST') {
      if (isset($_POST['btnN'])) {
        $notify = $this->Request->notifyUser($id);
        if ($notify) :
          Sessao::izitoast('notify', 'Success', 'Notificado com sucesso');
          Url::redireciona('saler/pedidos');
          exit;
        else :
          Sessao::izitoast('notify', 'Error', 'Algo deu errado, consulte BD', 'error');
          Url::redireciona('saler/pedidos');
          exit;
        endif;
      } else {
        Sessao::sms('erro', 'Não clicou no botão, intruso', 'alert alert-danger');
        Url::redireciona('saler/pedidos');
        exit;
      }
    } else {

      Sessao::sms('erro', 'Metodo de envio \'GET\' não é permitido', 'alert alert-danger');
      Url::redireciona('saler/pedidos');
      exit;
    }
  }
  public function confirm($id)
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;

    $data = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $metodo = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT);

    if ($id and $metodo == 'POST') {
      if (isset($_POST['btnT'])) {
        $confirm = $this->Request->confirmRequest($id, $_POST['data']);

        if ($confirm) :
          Sessao::izitoast('notify', 'Success', 'pedido terminado com sucesso');
          Url::redireciona('saler/pedidos');
          // try {
          //   //code...
          //   $this->printTicketsP($data);
          // } catch (\Exception $th) {
          //   // simplesmente avança
          // }
          exit;
        else :
          Sessao::izitoast('notify', 'Error', 'Algo deu errado, consulte BD', 'error');
          Url::redireciona('saler/pedidos');
          exit;
        endif;
      } else {
        Sessao::sms('erro', 'Não clicou no botão, intruso', 'alert alert-danger');
        Url::redireciona('saler/pedidos');
        exit;
      }
    } else {

      Sessao::sms('erro', 'Metodo de envio \'GET\' não é permitido', 'alert alert-danger');
      Url::redireciona('saler/pedidos');
      exit;
    }
  }
  public function historico()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $history = $this->Request->historico();
    // var_dump($history);
    // exit;

    $file = "historico";
    $this->view('layouts/saler/app', compact('file', 'history'));
  }
  // <!-- ========== End pedidos ========== -->

  // <!-- ========== Start saida ========== -->
  public function saidas()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    $pratos = $this->Prato->prato_read();
    if (isset($_POST['update'])) {
      $dado = trim($_POST['status']);
      $id = trim($_POST['id']);
      $update = $this->Prato->update_prato1($dado, $id);
      if ($update) {
        Sessao::izitoast('prato', 'Success', 'Prato actualizado');
        Url::redireciona("saler/saidas");
        exit;
      } else {
        Sessao::izitoast('prato', 'Error', 'Prato não actualizado');
        Url::redireciona("saler/saidas");
        exit;
      }
    }
    $file = "saidas";
    $this->view('layouts/saler/app', compact('file', 'pratos'));
  }
  // <!-- ========== End saida ========== -->

  // Controllers para perfil
  public function config()
  {
    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;


    $formulario = filter_input_array(INPUT_POST, FILTER_DEFAULT);

    // upload de imagem
    if (isset($formulario['load'])) :
      if (isset($_FILES['upload'])) :
        $foto = $this->Perfil->viewperfil($_SESSION['usuarioS_id']);
        unset($_SESSION['usuarioC_nome']);
        $_SESSION['usuarioS_email'] = $foto['email'];

        $upload = new Uploads();
        $road = "Users" . DIRECTORY_SEPARATOR . $_SESSION['usuarioS_email'];
        $upload->imagem($_FILES['upload'], 7, $road);
      // var_dump($upload->getexito(),$upload->geterro());
      endif;
      if ($upload->geterro()) :
        Sessao::sms('upload', $upload->geterro(), 'alert alert-danger');
      else :
        $up = [
          'path' => $_SESSION['path'],
          'id' => $_SESSION['usuarioS_id']
        ];

        if ($this->Perfil->updateupload($up)) :
          $foto = $this->Perfil->viewperfil($_SESSION['usuarioS_id']);

          unset($_SESSION['usuarioS_img']);
          $_SESSION['usuarioS_img'] = !empty($foto['imagem']) ? URL . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $foto['imagem'] : URL . 'public/img/user-logo.jpg';
          Sessao::sms('upload', $upload->getexito());
        // Url::redireciona('admin/config');
        else :
          die("erro ao armazenar o caminho da foto de perfil");
        endif;
      endif;



    endif;
    $readperfil = $this->Perfil->viewperfil($_SESSION['usuarioS_id']);
    // edicao de perfil

    if (isset($formulario['cad'])) :

      $dados = [
        'nome' => trim($formulario['nome']),
        'email' => trim($formulario['email']),
        'err_nome' => '',
        'id' => $_SESSION['usuarioS_id'],
        'perfil' => $readperfil,
        'err_email' => ''

      ];
      if (in_array("", $formulario)) :

        if (empty($formulario['nome'])) :
          $dados['err_nome'] = 'Preencha o campo Nome*';
        endif;

        if (empty($formulario['email'])) :
          $dados['err_email'] = 'Preencha o campo Email*';
        endif;
        // colloque mensagem
        Sessao::izitoast('config', 'Warning', ' Algo deu errado tente editar novamente', 'warning');


      else :
        if (Valida::email($formulario['email'])) :
          $dados['err_email'] = "preencha corretamente o email";
          Sessao::izitoast('config', 'Warning', ' Algo deu errado tente editar novamente', 'warning');

        else :
          // var_dump($dados);
          // exit;
          if ($this->Perfil->updateperfil($dados)) :
            Sessao::izitoast('config', 'Success', 'Perfil actualizado com sucesso');

          else :
            Sessao::izitoast('config', 'Error', 'Erro com a função->updateperfil', 'error');
            $dados = [
              'nome' => $readperfil['nome'],
              'email' => $readperfil['email'],
              'err_nome' => '',
              'err_email' => ''
            ];
          endif;
        endif;

      endif;
    else :
      $dados = [
        'nome' => $readperfil['nome'],
        'email' => $readperfil['email'],
        'err_nome' => '',
        'err_email' => ''
      ];

    endif;
    // Renovar a senha
    $senha = $this->Perfil->viewperfil($_SESSION['usuarioS_id']);
    // var_dump($senha);
    // exit;
    if (isset($formulario['altSenha'])) :
      $change = [
        'senha' => trim($formulario['senha']),
        'novasenha' => trim($formulario['novasenha']),
        'rnovasenha' => trim($formulario['rnovasenha']),
        'err_senha' => '',
        'err_newpass' => '',
        'err_renewpass' => ''
      ];
      if (in_array("", $formulario)) :
        if (empty($formulario['senha'])) :
          $change['err_senha'] = 'Preencha o campo*';
        endif;
        if (empty($formulario['novasenha'])) :
          $change['err_newpass'] = 'Preencha o campo Nova Senha*';
        endif;
        if (empty($formulario['rnovasenha'])) :
          $change['err_renewpass'] = 'Porfavor repita a Nova Senha*';
        endif;
        Sessao::izitoast('config', 'Warning', 'Algo deu errado, tente a senha novamente', 'error');

      else :
        // echo'<hr>';
        // var_dump($change);
        // exit;
        if (!password_verify($change['senha'], $senha['senha'])) :
          $change['err_senha'] = 'Senha errada';
          Sessao::izitoast('config', 'Warning', 'Algo deu errado, tente a senha novamente', 'error');
        elseif ($formulario['novasenha'] != $formulario['rnovasenha']) :
          $change['err_newpass'] = 'Senhas diferentes*';
          $change['err_renewpass'] = 'Senhas diferentes*';
          Sessao::izitoast('config', 'Warning', 'Algo deu errado, tente a senha novamente', 'error');
        else :
          $change['novasenha'] = password_hash(trim($formulario['novasenha']), PASSWORD_DEFAULT);
          $newpass = $this->Perfil->newpass($change, $_SESSION['usuarioS_id']);
          if ($newpass) :
            Sessao::izitoast('config', 'Success', 'Senha actualizado com sucesso');
            Url::redireciona('admin/config');
            exit;
          else :
            Sessao::sms('upload', 'Erro com a Model Usuarios->newpass', 'alert alert-danger');

          endif;
        endif;
      // var_dump($dados);
      endif;

    else :
      $change = [
        'senha' => '',
        'novasenha' => '',
        'rnovasenha' => '',
        'err_senha' => '',
        'err_newpass' => '',
        'err_renewpass' => ''
      ];
    endif;




    $file = 'config';
    return $this->view('layouts/saler/app', compact('file', 'dados', 'change'));
  }

  public function deletetofo()
  {

    if (!Sessao::nivel1()) :
      Url::redireciona("saler/login");
    endif;
    // $metodo = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT);
    $dados = ['id' => $_SESSION['usuarioS_id'], 'path' => 'img/user-logo.jpg'];

    if ($this->Perfil->deletefotos($dados)) :
      $_SESSION['usuarioS_img'] = URL . '/public/img/user-logo.jpg';

      Sessao::sms('upload', 'imagem deletada com sucesso');
      Url::redireciona('saler/config');
      exit;
    else :
      Sessao::sms('upload', 'imagem não deletada, erro com a Model Perfil->deletefoto', 'alert alert-danger');
      Url::redireciona('saler/config');
      exit;
    endif;
  }
  // end Configuracion

}

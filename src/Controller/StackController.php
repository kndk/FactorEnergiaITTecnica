<?php

namespace App\Controller;

//require('vendor/autoload.php');

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class StackController extends AbstractController{

  private $logger;

  public function __construct(LoggerInterface $logger)
  {
      $this->logger = $logger;
  }

  /**
   * @Route("", name="formstack")
   * 
   */
  public function formStack(){

    $response = new Response();
    $htmlStyle = '
      <style>
        form {
          /* Centrar el formulario en la página */
          margin: 0 auto;
          width: 400px;
          /* Esquema del formulario */
          padding: 1em;
          border: 1px solid #CCC;
          border-radius: 1em;
        }
        
        ul {
          list-style: none;
          padding: 0;
          margin: 0;
        }
        
        form li + li {
          margin-top: 1em;
        }
        
        label {
          /* Tamaño y alineación uniforme */
          display: inline-block;
          width: 90px;
          text-align: right;
        }

        input {
          font: 1em sans-serif;

          /* Tamaño uniforme del campo de texto */
          width: 300px;
          box-sizing: border-box;

          border: 1px solid #999;
        }

        input:focus,
        textarea:focus {
          border-color: #000;
        }

        .button {
          /* Alinear los botones con los campos de texto */
          padding-left: 90px; /* mismo tamaño que los elementos de la etiqueta */
        }
        
        button {
          /* Este margen adicional representa aproximadamente el mismo espacio que el espacio
             entre las etiquetas y sus campos de texto */
          margin-left: .5em;
        }
      </style>
    ';
    $htmlScripts='
      <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
        $(function(){
          $("#datepicker").datepicker();
        });
      </script>
    ';
    $htmlForm='
      <form action="/result" method="get">
        <ul style="list-style-type: none;">
          <li><label for="tag">Tag:</label></li>
          <li><input type="text" name="tag"></li>
          <li class="button" style="margin-top:10px">
           <button type="submit">Buscar</button>
          </li>
        </ul>
      </form>
    ';
    $response->setContent($htmlStyle.$htmlScripts.$htmlForm);
    return $response;
  }

  /**
   * @Route("/result", name="formstack_result")
   * 
   */

  public function formstackResult(Request $request){

    $tag=$request->get('tag','');
    $httpClient = HttpClient::create();
    if($tag!==''){
      $httpresponse = $httpClient->request('GET', 'https://api.stackexchange.com/2.2/questions?order=desc&sort=activity&tagged='.$tag.'&site=stackoverflow');
    } else{
      $httpresponse = $httpClient->request('GET', 'https://api.stackexchange.com/2.2/questions?order=desc&sort=activity&site=stackoverflow');
    }
    $response = new Response();
    $htmlStyle= '
      <style>
        .key {color: red;}
        .value {color: blue;}
        .item {
          margin-bottom: 1em;
          padding: 1em;
          border: 1px solid #CCC;
          border-radius: 1em;
        }
      </style>
    ';
    $htmlResponse = '';

    $data = json_decode($httpresponse->getContent(), true);

    $dataToSet;

    if($data['has_more']){
      foreach($data['items'] as $key => $value){
        $itemToSet;
        $htmlResponse=$htmlResponse.'<div class="item">';
        foreach($value as $newkey => $newvalue){
          $itemToSet = [$newkey => $newvalue];
          $test = json_encode($newvalue);
          $test2 = json_encode($newkey);
          $test3 = json_encode($itemToSet);
          $this->logger->info("TEST:".$test);
          $this->logger->info("KEY:".$test2);
          $this->logger->info("ITEMTOSET:".$test3);
          $dataToSet[] = $itemToSet;
          if(is_array($newvalue)){
            $htmlResponse=$htmlResponse.'<div><span class="key">'.$newkey.': </span><span class="value">'.implode(' ',$newvalue).'</span></div>';
          }else{
            $htmlResponse=$htmlResponse.'<div><span class="key">'.$newkey.': </span><span class="value">'.$newvalue.'</span></div>';
          }
        }
        $htmlResponse=$htmlResponse.'</div>';
      }
      
    }
    if($htmlResponse==='')$htmlResponse='<div>No se ha encontrado nada>/div>';
    $response->setContent('<a href="/">Volver atrás</a>'.$htmlStyle.$htmlResponse);
    return $response;
  }
}

 ?>

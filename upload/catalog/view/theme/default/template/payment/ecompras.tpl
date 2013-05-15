<?php include_once('resources/ecomprasobj.php'); ?>

<form action="<?php echo $action; ?>" method="post">

 <input name="eCompras_hdnCarrito" type="hidden" id="eCompras_hdnCarrito" value="<?php

$carrito = new EcomprasShoppingCart();
$carrito->codigoCarrito = $cart_order_id;
$carrito->montoTotal = $total;
$carrito->moneda = $currency_code; 
$carrito->tiendaid = $sid;
$carrito->llave = $this->config->get('ecompras_secret');
$carrito->nombreEmpresa = $StoreName;
$carrito->paginaReporte = $return_url;;

$i = 0;
foreach ($products as $product) {


$det1 = new EcomprasDetalle();
$det1->articuloid = $product['product_id'];
$det1->descripcion = $product['name'] . " " . $product['model'];
$det1->total = $product['price'];
$det1->cantidad = $product['quantity'];
$carrito->adicionarDetalle($det1);
$i++;   
  } 
echo urlencode($carrito->toXml());

?>" />
 
  <div class="buttons">
    <div class="right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
</form>

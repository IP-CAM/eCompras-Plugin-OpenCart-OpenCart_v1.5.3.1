<?php 
/*
    Copyright (c) 2012 Javier León
    e-mail: schildren@gmail.com

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class ControllerPaymentEcompras extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['action'] = 'https://www.ecompras.com.bo/pos.aspx';

		$this->data['sid'] = $this->config->get('ecompras_account');
		
		$this->data['StoreName'] = $this->config->get('ecompras_StoreName');
		
		$this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$this->data['cart_order_id'] = $this->session->data['order_id'];
		$this->data['card_holder_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
		$this->data['street_address'] = $order_info['payment_address_1'];
		$this->data['city'] = $order_info['payment_city'];
		$this->data['currency_code'] = $order_info['currency_code'];
		
		if ($order_info['payment_iso_code_2'] == 'US' || $order_info['payment_iso_code_2'] == 'CA') {
			$this->data['state'] = $order_info['payment_zone'];
		} else {
			$this->data['state'] = 'XX';
		}
		
		$this->data['zip'] = $order_info['payment_postcode'];
		$this->data['country'] = $order_info['payment_country'];
		$this->data['email'] = $order_info['email'];
		$this->data['phone'] = $order_info['telephone'];
		
		if ($this->cart->hasShipping()) {
			$this->data['ship_street_address'] = $order_info['shipping_address_1'];
			$this->data['ship_city'] = $order_info['shipping_city'];
			$this->data['ship_state'] = $order_info['shipping_zone'];
			$this->data['ship_zip'] = $order_info['shipping_postcode'];
			$this->data['ship_country'] = $order_info['shipping_country'];
		} else {
			$this->data['ship_street_address'] = $order_info['payment_address_1'];
			$this->data['ship_city'] = $order_info['payment_city'];
			$this->data['ship_state'] = $order_info['payment_zone'];
			$this->data['ship_zip'] = $order_info['payment_postcode'];
			$this->data['ship_country'] = $order_info['payment_country'];			
		}
		
		$this->data['products'] = array();
		
		$products = $this->cart->getProducts();

		//Carga de Productos  
		foreach ($products as $product) {
			$this->data['products'][] = array(
				'product_id'  => $product['product_id'],
				'name'        => $product['name'],
				'description' => $product['name'],
				'quantity'    => $product['quantity'],
				'model'=>$product['model'],
				'price'		  => $this->currency->format($product['price']*$product['quantity'], $order_info['currency_code'], $order_info['currency_value'], false)
			);
		}
	
		// Validar cargos varios Ej: Recargo, shipping, otros. 
		$extras= $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], $order_info['currency_value'], false);
		
		if (substr($extras, -1, 1) == "0") {
		  $extras = substr($extras, 0, strlen($extras) - 1);
		}
		if (substr($extras, -1, 1) == "0") {
		  $extras = substr($extras, 0, strlen($extras) - 2);
		}
		if ($extras > 0) {
			$Array_Aux=array(
				'product_id'  => '0',
				'name'        => 'Envio/shipping',
				'description' => ' ',
				'quantity'    => '1',
				'model'=>' ',
				'price'=> $extras,
				);
			array_push($this->data['products'],$Array_Aux); 
		}

		if ($this->config->get('ecompras_test')) {
			$this->data['demo'] = 'Y';
		} else {
			$this->data['demo'] = '';
		}
		
		$this->data['lang'] = $this->session->data['language'];

		$this->data['return_url'] = $this->url->link('payment/ecompras/callback', '', 'SSL');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ecompras.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/ecompras.tpl';
		} else {
			$this->template = 'default/template/payment/ecompras.tpl';
		}	
		
		$this->render();
	}
	
	public function callback() {
	
		include_once('resources/ecomprasobj.php');
		// Leemos la respuesta en el post
		$respuesta = new EcomprasRespuesta($_POST);		
			
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($respuesta->transaccionid);
		$order_id=$respuesta->codigoCarrito;
		$order_number=$respuesta->transaccionid;
		$this->session->data['order_id'];		
		
			//Validación de llaves
			//Estados de una orden
			//2		Processing
			//3		Shipped
			//7		Canceled
			//5		Complete
			//8		Denied
			//9		Canceled Reversal
			//10		Failed
			//11		Refunded
			//12		Reversed
			//13		Chargeback
			//1		Pending
			//16		Voided
			//15		Processed
			//14		Expired
			
			$notify = true;
			if($respuesta->transaccionid == 0)
			{
				//Transaccion no valida
				// si el transaction_id es cero - no valido
				$comment = "El pago no pudo completarse, el operador de la tarjeta no pudo verificar los datos de la transacción. / The payment could not be completed, the operator of the card could not verify the transaction data.";
				//Falló la transaccion - 10		Failed
				$order_status_id=10;
				// confirmar la orden con el nuevo estado          
				$this->model_checkout_order->confirm($order_id, $order_status_id, $comment, $notify);
			}
			else{
				// Se genero una transacción
				// confirmar la orden con el nuevo estado
			   // si el codigo de respuesta es 0, entonces todo salió bien
			   // los campos que se utilizan son:
			   //       $respuesta->transaccionid - Numero que identifica la transacción para ecompras
			   //       $respuesta->respuesta - Mensaje literal sobre el resultado de la transacción
			   //       $respuesta->codigoCarrito - el identificador de la compra para su tienda
			   if ($respuesta->codigoRespuesta == 0) 
			   {
					$comment = "Tra. ID:" .  $respuesta->transaccionid . " - Pedido procesado, pago recibido por eCompras. / Order processed, payment received by eCompras.";
				   //id =2 Processing/procesando
				   $order_status_id=2;
					$this->model_checkout_order->confirm($order_id, $order_status_id,$comment, $notify);
				   echo "codigo de respuesta ok"."-".  $order_id . "-".$order_status_id;
					echo '<html>' . "\n";
					echo '<head>' . "\n";
					echo '  <meta http-equiv="Refresh" content="0; url=' . $this->url->link('checkout/success') . '">' . "\n";
					echo '</head>'. "\n";
					echo '<body>' . "\n";
					echo '  <p>Please follow <a href="' . $this->url->link('checkout/success') . '">link</a>!</p>' . "\n";
					echo '</body>' . "\n";
					echo '</html>' . "\n";
					exit();
			   }
			   else
			   {
					$comment = "El pago no pudo completarse, el operador de la tarjeta no pudo verificar los datos de la transacción. / The payment could not be completed, the operator of the card could not verify the transaction data.";
					//1		Pending
					$order_status_id=1;
					$this->model_checkout_order->confirm($order_id, $order_status_id,$comment, $notify);
					$this->redirect($this->url->link('common/home'));
				}	
			}
	}
}
?>
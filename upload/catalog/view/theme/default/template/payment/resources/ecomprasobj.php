<?php
class EcomprasDetalle {
	public $articuloid;
	public $cantidad;
  public $descripcion;
  public $total;
  
  public function EcomprasDetalle() {
  	$this->articuloid = 0;
  	$this->cantidad = 0;
  	$this->descripcion = "";
  	$this->total = 0.0;
  }	
}

class EcomprasShoppingCart {
	public $codigoCarrito;
	public $montoTotal;
	public $tiendaid;
	public $llave;
	public $detalle;
	public $fecha;
	public $lenguaje;
	public $moneda;
	public $nombreEmpresa;
	public $paginaReporte;
	
	public function EcomprasShoppingCart() {
		$this->codigoCarrito = "";
		$this->montoTotal = 0.0;
		$this->tiendaid = 0;
		$this->llave = "";
		$this->detalle = array();
		
		$ahora = time();
		$this->fecha = date('Y-m-d',$ahora) . "T" . date('H:i:s',$ahora) . ".0000-04:00";
		
		
		$this->lenguaje = "es";
		$this->moneda = "USD";
		$this->nombreEmpresa = "";
		$this->paginaReporte = "";
	}
	
	public function adicionarDetalle($det) {
		$i = count($this->detalle);
		$this->detalle[$i] = $det;
	}
	
	public function toXml() {
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$raiz = $doc->createElement("ecompraspos");
		
		$raizNs = $doc->createAttribute("xmlns");
		$raizNsValue = $doc->createTextNode("http://tempuri.org/ecompraspos.xsd");
		$raizNs->appendChild($raizNsValue);
		$raiz->appendChild($raizNs);
		
		$xmlcart = $doc->createElement("shoppingcart");
		
		$xmlelemento = $doc->createElement("firmacarrito");
		$texto = $doc->createTextNode($this->calcularFirma());
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("carritoentiendaid");
		$texto = $doc->createTextNode($this->codigoCarrito);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("fecha");
		$texto = $doc->createTextNode($this->fecha);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("lenguaje");
		$texto = $doc->createTextNode($this->lenguaje);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("nombreempresa");
		$texto = $doc->createTextNode($this->nombreEmpresa);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("total");
		$texto = $doc->createTextNode($this->montoTotal);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("moneda");
		$texto = $doc->createTextNode($this->moneda);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("tiendaid");
		$texto = $doc->createTextNode($this->tiendaid);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
		$xmlelemento = $doc->createElement("paginareporte");
		$texto = $doc->createTextNode($this->paginaReporte);
		$xmlelemento->appendChild($texto);
		$xmlcart->appendChild($xmlelemento);
		
	  foreach($this->detalle as $det) {
	  	$xmlDetalle = $doc->createElement("detalle");
	  	
	  	$xmlelemento = $doc->createElement("total");
		  $texto = $doc->createTextNode($det->total);
		  $xmlelemento->appendChild($texto);
		  $xmlDetalle->appendChild($xmlelemento);
		  
		  $xmlelemento = $doc->createElement("cantidad");
		  $texto = $doc->createTextNode($det->cantidad);
		  $xmlelemento->appendChild($texto);
		  $xmlDetalle->appendChild($xmlelemento);
		  
		  $xmlelemento = $doc->createElement("descripcion");
		  $texto = $doc->createTextNode($det->descripcion);
		  $xmlelemento->appendChild($texto);
		  $xmlDetalle->appendChild($xmlelemento);
		  
		  $xmlelemento = $doc->createElement("articuloid");
		  $texto = $doc->createTextNode($det->articuloid);
		  $xmlelemento->appendChild($texto);
		  $xmlDetalle->appendChild($xmlelemento);
	  	
	  	$xmlcart->appendChild($xmlDetalle);
	  }
		
		$raiz->appendChild($xmlcart);
		$doc->appendChild($raiz);
		return $doc->saveXML();
	}
	
	private function calcularFirma() {
		$textoAFirmar = $this->tiendaid . "#";
		$textoAFirmar .= $this->codigoCarrito . "#";
		$textoAFirmar .= $this->llave . "#";
		$textoAFirmar .= $this->montoTotal;
		
		$firma = sha1($textoAFirmar);
		return $firma;
	}
}

class EcomprasRespuesta {
	public $transaccionid;
	public $codigoCarrito;
	public $llave;
	public $codigoRespuesta;
	public $respuesta;
	
	public function EcomprasRespuesta($objPost) {
		$varPost = "eCompras_hdnRespuesta";
		
		$objXml = urldecode($objPost[$varPost]);
		
		$sxml = new SimpleXMLElement($objXml);
		
		$this->transaccionid = $sxml->ecomprasrespuesta[0]->transaccionid;
		$this->codigoCarrito = $sxml->ecomprasrespuesta[0]->carritoentiendaid;
		$this->llave = $sxml->ecomprasrespuesta[0]->firmacarrito;
		$this->codigoRespuesta = $sxml->ecomprasrespuesta[0]->codigorespuesta;
		$this->respuesta = $sxml->ecomprasrespuesta[0]->respuesta;
		
	}
}
?>
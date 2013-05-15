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
	private $error = array(); 

	public function index() {
		$this->load->language('payment/ecompras');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ecompras', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['entry_StoreName'] = $this->language->get('entry_StoreName');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		
		$this->data['entry_account'] = $this->language->get('entry_account');
		$this->data['entry_secret'] = $this->language->get('entry_secret');
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		 
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['account'])) {
			$this->data['error_account'] = $this->error['account'];
		} else {
			$this->data['error_account'] = '';
		}	
		
		if (isset($this->error['secret'])) {
			$this->data['error_secret'] = $this->error['secret'];
		} else {
			$this->data['error_secret'] = '';
		}	
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/ecompras', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/ecompras', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['ecompras_StoreName'])) {
			$this->data['ecompras_StoreName'] = $this->request->post['ecompras_StoreName'];
		} else {
			$this->data['ecompras_StoreName'] = $this->config->get('ecompras_StoreName');
		}
		
		if (isset($this->request->post['ecompras_account'])) {
			$this->data['ecompras_account'] = $this->request->post['ecompras_account'];
		} else {
			$this->data['ecompras_account'] = $this->config->get('ecompras_account');
		}

		if (isset($this->request->post['ecompras_secret'])) {
			$this->data['ecompras_secret'] = $this->request->post['ecompras_secret'];
		} else {
			$this->data['ecompras_secret'] = $this->config->get('ecompras_secret');
		}
		
		if (isset($this->request->post['ecompras_test'])) {
			$this->data['ecompras_test'] = $this->request->post['ecompras_test'];
		} else {
			$this->data['ecompras_test'] = $this->config->get('ecompras_test');
		}
		
		if (isset($this->request->post['ecompras_total'])) {
			$this->data['ecompras_total'] = $this->request->post['ecompras_total'];
		} else {
			$this->data['ecompras_total'] = $this->config->get('ecompras_total'); 
		} 
				
		if (isset($this->request->post['ecompras_order_status_id'])) {
			$this->data['ecompras_order_status_id'] = $this->request->post['ecompras_order_status_id'];
		} else {
			$this->data['ecompras_order_status_id'] = $this->config->get('ecompras_order_status_id'); 
		}
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['ecompras_geo_zone_id'])) {
			$this->data['ecompras_geo_zone_id'] = $this->request->post['ecompras_geo_zone_id'];
		} else {
			$this->data['ecompras_geo_zone_id'] = $this->config->get('ecompras_geo_zone_id'); 
		}
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['ecompras_status'])) {
			$this->data['ecompras_status'] = $this->request->post['ecompras_status'];
		} else {
			$this->data['ecompras_status'] = $this->config->get('ecompras_status');
		}
		
		if (isset($this->request->post['ecompras_sort_order'])) {
			$this->data['ecompras_sort_order'] = $this->request->post['ecompras_sort_order'];
		} else {
			$this->data['ecompras_sort_order'] = $this->config->get('ecompras_sort_order');
		}

		$this->template = 'payment/ecompras.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/ecompras')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['ecompras_account']) {
			$this->error['account'] = $this->language->get('error_account');
		}

		if (!$this->request->post['ecompras_secret']) {
			$this->error['secret'] = $this->language->get('error_secret');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>
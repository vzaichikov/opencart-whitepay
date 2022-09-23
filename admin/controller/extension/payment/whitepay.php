<?php
class ControllerExtensionPaymentWhitepay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/whitepay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('whitepay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_pay'] = $this->language->get('text_pay');
		$data['text_card'] = $this->language->get('text_card');

		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_signature'] = $this->language->get('entry_signature');
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}

		if (isset($this->error['signature'])) {
			$data['error_signature'] = $this->error['signature'];
		} else {
			$data['error_signature'] = '';
		}

		if (isset($this->error['type'])) {
			$data['error_type'] = $this->error['type'];
		} else {
			$data['error_type'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/whitepay', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/whitepay', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post['whitepay_merchant'])) {
			$data['whitepay_merchant'] = $this->request->post['whitepay_merchant'];
		} else {
			$data['whitepay_merchant'] = $this->config->get('whitepay_merchant');
		}

		if (isset($this->request->post['whitepay_api_key'])) {
			$data['whitepay_api_key'] = $this->request->post['whitepay_api_key'];
		} else {
			$data['whitepay_api_key'] = $this->config->get('whitepay_api_key');
		}

		if (isset($this->request->post['whitepay_webhook_key'])) {
			$data['whitepay_webhook_key'] = $this->request->post['whitepay_webhook_key'];
		} else {
			$data['whitepay_webhook_key'] = $this->config->get('whitepay_webhook_key');
		}

		if (isset($this->request->post['whitepay_type'])) {
			$data['whitepay_type'] = $this->request->post['whitepay_type'];
		} else {
			$data['whitepay_type'] = $this->config->get('whitepay_type');
		}

		if (isset($this->request->post['whitepay_total'])) {
			$data['whitepay_total'] = $this->request->post['whitepay_total'];
		} else {
			$data['whitepay_total'] = $this->config->get('whitepay_total');
		}

		if (isset($this->request->post['whitepay_success_order_status_id'])) {
			$data['whitepay_success_order_status_id'] = $this->request->post['whitepay_success_order_status_id'];
		} else {
			$data['whitepay_success_order_status_id'] = $this->config->get('whitepay_success_order_status_id');
		}

		if (isset($this->request->post['whitepay_order_status_id'])) {
			$data['whitepay_order_status_id'] = $this->request->post['whitepay_order_status_id'];
		} else {
			$data['whitepay_order_status_id'] = $this->config->get('whitepay_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['whitepay_geo_zone_id'])) {
			$data['whitepay_geo_zone_id'] = $this->request->post['whitepay_geo_zone_id'];
		} else {
			$data['whitepay_geo_zone_id'] = $this->config->get('whitepay_geo_zone_id');
		}

		$this->load->model('catalog/pricegroup');
		$data['pricegroups'] = $this->model_catalog_pricegroup->getPriceGroups();

		if (isset($this->request->post['whitepay_exclude_pricegroups'])) {
			$data['whitepay_exclude_pricegroups'] = $this->request->post['whitepay_exclude_pricegroups'];
		} else {
			$data['whitepay_exclude_pricegroups'] = $this->config->get('whitepay_exclude_pricegroups');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['whitepay_status'])) {
			$data['whitepay_status'] = $this->request->post['whitepay_status'];
		} else {
			$data['whitepay_status'] = $this->config->get('whitepay_status');
		}

		if (isset($this->request->post['whitepay_sort_order'])) {
			$data['whitepay_sort_order'] = $this->request->post['whitepay_sort_order'];
		} else {
			$data['whitepay_sort_order'] = $this->config->get('whitepay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/whitepay', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/whitepay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['whitepay_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['whitepay_api_key']) {
			$this->error['signature'] = $this->language->get('error_signature');
		}

		return !$this->error;
	}
}
<?php
class ModelExtensionPaymentWhitePay extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/whitepay');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('whitepay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('whitepay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$description = $this->language->get('text_description');
		$text_danger = '';
		$dummy		 = false;


		if ($status && $this->config->get('whitepay_total') > 0 && $this->config->get('whitepay_total') > $total) {
			$status = true;
			$dummy  = true;
			$text_danger = $this->language->get('whitepay_min_sum_text_danger');
			$description = '';
		}

		if ($status && $this->cart->hasPriceGroups($this->config->get('whitepay_exclude_pricegroups'))){
			$status = true;
			$dummy  = true;
			$text_danger = $this->language->get('whitepay_pricegroups_danger');
			$description = '';
		}

		if ($status && !$this->cart->getIfPaymentIsPossible()){
			$status = true;
			$dummy  = true;
			$text_danger = $this->language->get('whitepay_text_danger');
			$description = '';

		}

		if ($_SERVER['REMOTE_ADDR'] != '31.43.104.37'){
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       	=> 'whitepay',
				'title'      	=> $this->language->get('text_title'),
				'title_simple'	=> $this->language->get('text_title_simple'),
				'description'	=> $description,
				'terms'      	=> '',
				'text_danger' 	=> $text_danger,
				'dummy'			=> $dummy,
				'terms'      	=> '',
				'sort_order' 	=> $dummy?498:$this->config->get('whitepay_sort_order')
			);
		}

		return $method_data;
	}
}
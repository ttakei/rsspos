<?php

class SiteApi extends BaseController {

	public function __construct(){
	}

	public function allUrlEncoded(){
		$sites = Sites::where('isactive', 1)->get();
		$output = "";
		foreach ($sites as $site) {
			$acc = $site['acc'];
			$acc_url_encoded = urlencode($acc);
			$output .= "$acc_url_encoded\n";
		}
		print $output;
		exit;
	}
}

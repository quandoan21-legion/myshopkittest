<?php

namespace MyShopKit\MailServices\Shared;

trait TraitGenerateRestEndpoint {

	public function getSetUpMailServicesEndPoint( string $mailServices ): string {
		return 'me/' . $mailServices . '/setup';
	}

	public function getSaveListIdEndPoint( string $mailServices ): string {
		return 'me/' . $mailServices . '/lists';
	}

	public function getSaveEmailEndPoint( string $mailServices ): string {
		return 'me/' . $mailServices . '/lists/members';
	}
}
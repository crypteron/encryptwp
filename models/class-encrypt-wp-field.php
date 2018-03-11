<?php
class EncryptWP_Field {
	/**
	 * @var string
	 */
	public $label;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var int
	 */
	public $state;

	public function __construct($label, $state, $slug) {
		$this->label = $label;
		$this->state = $state;
		$this->slug = $slug;
	}
}
<?php
class EncryptWP_Field {
	/**
	 * @var string
	 */
	public $label;

	/**
	 * @var EncryptWP_Field_State
	 */
	public $state;

	public function __construct($label, $state) {
		$this->label = $label;
		$this->state = $state;
	}
}
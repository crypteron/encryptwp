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
	 * @var EncryptWP_Field_State
	 */
	public $state;

	public function __construct($label, $state, $slug) {
		$this->label = $label;
		$this->state = $state;
		$this->slug = $slug;
	}
}
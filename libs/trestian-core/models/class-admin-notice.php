<?php
namespace TrestianCore\v1;

class Admin_Notice {
	protected $message;

	protected $type;

	protected $is_dismissible;

	protected $domain;

	public function __construct($message, $type = 'info', $is_dismissible = true, $domain = '') {
		$this->message = $message;
		$this->type = $type;
		$this->is_dismissible = $is_dismissible;
		$this->domain = $domain;
	}

	public function display(){
		?>
		<div class="notice notice-<?= $this->type ?> <?= $this->is_dismissible ? 'is-dismissible' :'' ?>">
			<p><?php _e( $this->message, $this->domain ); ?></p>
		</div>
		<?php
	}

}
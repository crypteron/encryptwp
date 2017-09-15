<?php
namespace TrestianCore\v1;

class Admin_Notice_Manager {
	protected $settings;

	/**
	 * Admin_Notice_Manager constructor.
	 *
	 * @param Plugin_Settings $settings
	 */
	public function __construct(Plugin_Settings $settings) {
		$this->settings = $settings;
	}

	/**
	 * Create an admin notice
	 *
	 * @param string $message
	 * @param string $type - notice, error, success, warning or info
	 * @param bool $is_dismissible
	 * @param string $visible - all, user, network or admin
	 */
	public function add_notice($message, $type = 'notice', $is_dismissible = true, $visible = 'all' ){
		if(!in_array($type, array('notice', 'error', 'success', 'warning', 'info'))){
			throw new \InvalidArgumentException("Invalid admin notice type: $type. Supported types are 'notice', 'error', 'success', 'warning', or 'info'");
		}

		if(!in_array($visible, array('all', 'user', 'network', 'admin'))){
			throw new \InvalidArgumentException("Invalid admin notice visibility: $visible. Supported options are 'all', 'user', 'network', or 'admin'");
		}
		$hook = $visible == 'admin' ? 'admin_notices' : $visible . '_admin_notices';

		$notice = new Admin_Notice($message, $type, $is_dismissible ? true : false, $this->settings->get_prefix());

		add_action($hook, array($notice, 'display'));
	}

}
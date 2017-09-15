<?php
namespace TrestianCore\v1;

/**
 * Admin AJAX Functionality
 *
 *
 * @package    TrestianWPManagers
 * @subpackage TrestianWPManagers/managers
 * @author     Yaron Guez <yaron@trestian.com>
 */
class Ajax_Manager{

	/**
	 * Return an AJAX response
	 *
	 * @param $message - Message to include in payload
	 * @param $success - Whether to return a success or error response
	 * @param array $data - Optional data to include in payload
	 */
    public function return_response($message, $success, $data=array()){
        $response = array(
            'success' => $success,
            message => $message
        );
        if(!empty($data)){
        	$response = array_merge($data, $response);
        }
        echo json_encode($response);
        wp_die();
    }

	/**
	 * Returns an AJAX error response
	 *
	 * @param $message - Error message to include in payload
	 * @param array $data - Optional data to include in payload
	 */
    public function return_error($message, $data=array()){
        $this->return_response($message, false, $data);
    }

	/**
	 * Returns an AJAX success response
	 *
	 * @param $message - Success message to include in payload
	 * @param array $data - Optional data to include in payload
	 */
    public function return_success($message, $data=array()){
        $this->return_response($message, true, $data);
    }

	/**
	 * Fetches and optionally sanitizes data from POST while triggering error if missing
	 * @param $field
	 * @param $message
	 * @param bool $sanitize_text
	 *
	 * @return string
	 */
    public function check_missing_data($field, $message, $sanitize_text = true)
    {
        if (!isset($_POST[$field]) || !strlen($_POST[$field])) {
            $this->return_error($message);
        } else if($sanitize_text){
        	return sanitize_text_field($_POST[$field]);
        }
        else {
            return $_POST[$field];
        }
    }

	/**
	 * Helper function to validate nonce in form submission
	 *
	 * @param string $nonce_field
	 * @param string $action_field
	 * @param string $message
	 * @return string - action of form
	 */
    public function validate_nonce($nonce_field = 'nonce', $action_field = 'action', $message='The form has expired. Please refresh the page and try again'){
    	if(!isset($_POST[$nonce_field]) || !isset($_POST[$action_field]) || !wp_verify_nonce($_POST[$nonce_field], $_POST[$action_field])){
    		$this->return_error($message);
	    }

	    return $_POST[$action_field];
    }
}

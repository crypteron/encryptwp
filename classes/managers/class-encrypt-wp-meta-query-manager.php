<?php
use \CipherCore\v1\Encryptor;

class EncryptWP_Meta_Query_Manager{
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	private static $invalid_comparisons = array('>', '>=', '<', '<=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' );

	/**
	 * EncryptWP_Meta_Query_Manager constructor.
	 *
	 * @param Encryptor $encryptor
	 */
	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function parse_query_vars($qv, $secure_fields){
		// Is a meta key provided?
		if(!empty($qv['meta_key'])){
			$key = $qv['meta_key'];

			// Is meta key secure?
			if(isset($secure_fields[$key])){
				// If secure meta key NOT searchable, or unsupported meta compare is provided, clear out the meta key, value and compare
				if($secure_fields[$key] === false || (!empty($qv['meta_compare']) && in_array($qv['meta_compare'], self::$invalid_comparisons)) ){
					$qv['meta_key'] = '';
					$qv['meta_value'] = '';
					$qv['meta_compare'] = '';
				} else {
					$qv['meta_compare'] = 'RLIKE';
					$value = isset($qv['meta_value']) ? $qv['meta_value'] : '';
					$prefix = $this->encryptor->getSearchPrefix($value);
					$qv['meta_value'] = '^' . preg_quote($prefix);
				}
			}
		}

		// Is a meta query provided
		if(isset($qv['meta_query']) && is_array($qv['meta_query'])){
			$qv['meta_query'] = $this->parse_meta_query($qv['meta_query'], $secure_fields);
		}

		return $qv;

	}

	/**
	 * @param $meta_query array
	 * @param $secure_fields array
	 *
	 * @return array
	 */
	public function parse_meta_query($meta_query, $secure_fields){
		foreach ( $meta_query as $key => $query ) {

			// Skip relations and non arrays
			if ( 'relation' === $key || !is_array($query)) {
				continue;
			}

			// First-order clause.
			if (isset( $query['key'] )) {
				// Skip insecure keys
				if(!isset($secure_fields[$query['key']])){
					continue;
				}

				// If an unsupported comparison is used, drop the query entirely
				if(isset($query['compare']) && in_array($query['compare'], self::$invalid_comparisons)){
					unset($meta_query[$key]);
					continue;
				}

				$meta_query[$key]['compare'] = 'RLIKE';
				$value = isset($meta_query[$key]['value']) ? $meta_query[$key]['value'] : '';
				$prefix = $this->encryptor->getSearchPrefix($value);
				$meta_query[$key]['value'] = '^' . preg_quote($prefix);

				// Otherwise, it's a nested query, so we recurse.
			} else {
				$sub_query = $this->parse_meta_query( $query, $secure_fields );

				if ( ! empty( $sub_query ) ) {
					$meta_query[ $key ] = $sub_query;
				}
			}
		}

		return $meta_query;

	}
}
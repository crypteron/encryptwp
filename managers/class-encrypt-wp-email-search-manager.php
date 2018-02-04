<?php
class EncryptWP_Email_Search_Manager {

	/**
	 * @var EncryptWP_Encryption_Manager
	 */
	protected $encryption_manager;

	public function __construct(EncryptWP_Encryption_Manager $encryption_manager) {
		$this->encryption_manager = $encryption_manager;
	}

	/**
	 * @param $query WP_User_Query
	 *
	 * @return mixed
	 */
	public function update_query_for_email_search($query){
		if(!$query->get('search')){
			return $query;
		}

		$query->query_from = $this->get_from_sql($query);
		$query->query_where = $this->get_where_sql($query);

		return $query;
	}

	/**
	 * Get text to be added to a FROM clause for searching on email
	 * @var $query WP_User_Query
	 * @return string
	 */
	public function get_from_sql($query){
		global $wpdb;
		return $query->query_from . " LEFT OUTER JOIN $wpdb->usermeta AS ewp_meta ON (ewp_meta.user_id = {$wpdb->users}.ID AND ewp_meta.meta_key = '" . EncryptWP_Constants::EMAIL_META_KEY . "')";
	}

	/**
	 * @param $query WP_User_Query
	 *
	 * @return string
	 */
	public function get_where_sql($query){
		$search = $query->get('search');
		if (!$search) {
			return $query->query_where;
		}
		$search = trim($search, '*');

		$search_regex = $this->encryption_manager->get_search_regex($search);
		$search_sql_unwrapped = $this->get_unwrapped_search_sql($query);
		$search_sql = $this->wrap_search_sql($search_sql_unwrapped);

		global $wpdb;
		$new_search_sql = $search_sql_unwrapped . $wpdb->prepare(" OR ewp_meta.meta_value RLIKE '%s'", $search_regex);
		$new_search_sql = $this->wrap_search_sql($new_search_sql);

		$search_sql = str_replace($search_sql, $new_search_sql, $query->query_where);
		return $search_sql;
	}

	/**
	 * Given a WP_User_Query, extracts the string used in the where clause as part of a user search.
	 * Copied and modified from WP_User_Query
	 *
	 * @param $query WP_User_Query
	 *
	 * @return bool|string
	 */
	protected function get_unwrapped_search_sql($query){
		if($query->get('search') == null){
			return false;
		}

		$search = trim( $query->get('search'));

		$leading_wild = ( ltrim($search, '*') != $search );
		$trailing_wild = ( rtrim($search, '*') != $search );
		if ( $leading_wild && $trailing_wild )
			$wild = 'both';
		elseif ( $leading_wild )
			$wild = 'leading';
		elseif ( $trailing_wild )
			$wild = 'trailing';
		else
			$wild = false;
		if ( $wild )
			$search = trim($search, '*');

		$search_columns = array();
		if ( $query->get('search_columns') )
			$search_columns = array_intersect( $query->get('search_columns'), array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename' ) );
		if ( ! $search_columns ) {
			if ( false !== strpos( $search, '@') )
				$search_columns = array('user_email');
			elseif ( is_numeric($search) )
				$search_columns = array('user_login', 'ID');
			elseif ( preg_match('|^https?://|', $search) && ! ( is_multisite() && wp_is_large_network( 'users' ) ) )
				$search_columns = array('user_url');
			else
				$search_columns = array('user_login', 'user_url', 'user_email', 'user_nicename', 'display_name');
		}

		/**
		 * Filters the columns to search in a WP_User_Query search.
		 *
		 * The default columns depend on the search term, and include 'user_email',
		 * 'user_login', 'ID', 'user_url', 'display_name', and 'user_nicename'.
		 *
		 * @since 3.6.0
		 *
		 * @param array         $search_columns Array of column names to be searched.
		 * @param string        $search         Text being searched.
		 * @param WP_User_Query $this           The current WP_User_Query instance.
		 */
		$search_columns = apply_filters( 'user_search_columns', $search_columns, $search, $query );

		global $wpdb;

		$searches = array();
		$leading_wild = ( 'leading' == $wild || 'both' == $wild ) ? '%' : '';
		$trailing_wild = ( 'trailing' == $wild || 'both' == $wild ) ? '%' : '';
		$like = $leading_wild . $wpdb->esc_like( $search ) . $trailing_wild;

		foreach ( $search_columns as $col ) {
			if ( 'ID' == $col ) {
				$searches[] = $wpdb->prepare( "$col = %s", $search );
			} else {
				$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
			}
		}

		return implode(' OR ', $searches);

	}

	protected function wrap_search_sql($sql){
		return " AND ($sql)";
	}

}
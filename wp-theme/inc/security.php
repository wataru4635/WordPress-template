<?php

// ==========================================================================
// 投稿者アーカイブ無効化
// ==========================================================================

add_filter('author_rewrite_rules', '__return_empty_array');
add_action('template_redirect', function () {
	if (is_author() || get_query_var('author_name') || get_query_var('author')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
		nocache_headers();
		include get_404_template();
		exit;
	}
});

// ==========================================================================
// ユーザーのサイトマップを無効化
// ==========================================================================
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
  return $name === 'users' ? false : $provider;
}, 10, 2);
<?php
// ==========================================================================
// 「投稿」の名称を「お知らせ」に変更
// ==========================================================================
function change_post_menu_label() {
	global $menu;
	global $submenu;
	$name = 'お知らせ';
	$menu[5][0] = $name;
	$submenu['edit.php'][5][0] = $name . '一覧';
	$submenu['edit.php'][10][0] = '新しい' . $name;
}
function change_post_object_label() {
	global $wp_post_types;
	$name = 'お知らせ';
	$labels = &$wp_post_types['post']->labels;
	$labels->name = $name;
	$labels->singular_name = $name;
	$labels->add_new = _x('追加', $name);
	$labels->add_new_item = $name . 'の新規追加';
	$labels->edit_item = $name . 'の編集';
	$labels->new_item = '新規' . $name;
	$labels->view_item = $name . '一覧を見る';
	$labels->search_items = $name . 'を検索';
	$labels->not_found = $name . 'が見つかりませんでした';
	$labels->not_found_in_trash = 'ゴミ箱に' . $name . 'は見つかりませんでした';
}
add_action('init', 'change_post_object_label');
add_action('admin_menu', 'change_post_menu_label');

// ==========================================================================
// 投稿から「タグ」機能を削除
// ==========================================================================
function remove_post_taxonomies() {
	unregister_taxonomy_for_object_type('post_tag', 'post');
}
add_action('init', 'remove_post_taxonomies');
// ==========================================================================
// コメントの無効化
// ==========================================================================
function comment_status_none( $open, $post_id ) {
    $post = get_post( $post_id );
    //投稿のコメントを投稿できないようにします
    if( $post->post_type == 'post' ) {
        return false;
    }
    //固定ページのコメントを投稿できないようにします
    if( $post->post_type == 'page' ) {
        return false;
    }
    //メディアのコメントを投稿できないようにします
    if( $post->post_type == 'attachment' ) {
        return false;
    }
    return false;
}

add_filter( 'comments_open', 'comment_status_none', 10 , 2 );
function remove_menus() {
    remove_menu_page( 'edit-comments.php' ); // コメント
  }
  add_action( 'admin_menu', 'remove_menus', 999 );

// ==========================================================================
// 管理画面：カテゴリ編集でWPカラーピッカーを使用してスラッグに任意の色を設定
// ==========================================================================

add_action('admin_enqueue_scripts', function($hook){
  // カテゴリ追加/編集画面のみでOK
  if ($hook === 'edit-tags.php' || $hook === 'term.php') {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
  }
});

// 新規追加フォームに色フィールド
add_action('category_add_form_fields', function(){
  ?>
  <div class="form-field term-color-wrap">
    <label for="cat_color">カラー</label>
    <input type="text" name="cat_color" id="cat_color" class="wp-color-picker-field" data-default-color="#E00000" />
    <p class="description">このカテゴリの表示色を選択してください。</p>
    <script>jQuery(function($){ $('.wp-color-picker-field').wpColorPicker(); });</script>
  </div>
  <?php
});

// 編集フォームに色フィールド
add_action('category_edit_form_fields', function($term){
  $color = get_term_meta($term->term_id, '_cat_color', true) ?: '#E00000';
  ?>
  <tr class="form-field term-color-wrap">
    <th scope="row"><label for="cat_color">カラー</label></th>
    <td>
      <input type="text" name="cat_color" id="cat_color"
             value="<?php echo esc_attr($color); ?>"
             class="wp-color-picker-field" data-default-color="#E00000" />
      <p class="description">このカテゴリの表示色を選択してください。</p>
      <script>jQuery(function($){ $('#cat_color.wp-color-picker-field').wpColorPicker(); });</script>
    </td>
  </tr>
  <?php
});

// 保存（新規/編集）
function my_save_cat_color($term_id){
  if (isset($_POST['cat_color'])) {
    $val = sanitize_hex_color($_POST['cat_color']);
    if ($val) {
      update_term_meta($term_id, '_cat_color', $val);
    } else {
      delete_term_meta($term_id, '_cat_color');
    }
  }
}
add_action('created_category', 'my_save_cat_color');
add_action('edited_category',  'my_save_cat_color');
add_action('wp_head', function(){
  $terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
  if (is_wp_error($terms) || empty($terms)) return;

  echo "<style id='category-color-map'>";
  foreach ($terms as $t) {
    $color = get_term_meta($t->term_id, '_cat_color', true);
    if ($color) {
			echo "." . esc_attr($t->slug) . "{--cat-color:" . esc_html($color) . ";}";
    }
  }
  echo "</style>";
});

// ▼ CSS側で利用 background-color:var(--cat-color, #333); /* カテゴリ色を背景に反映、未設定なら#333 */
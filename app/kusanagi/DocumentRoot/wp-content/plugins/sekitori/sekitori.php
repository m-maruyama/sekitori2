<?php
/**
 * @package pressman
 */
/*
Plugin Name: sekitori
Plugin URI:
Description:
Version:
Author:
Author URI:
License: GPLv2 or later
Text Domain:
*/
// ユーザープロフィールの項目のカスタマイズ
function my_user_meta($wb)
{
	//項目の追加
	$wb['seat_no'] = '座席番号';
	$wb['project'] = 'プロジェクト';

	return $wb;
}
add_filter('user_contactmethods', 'my_user_meta', 10, 1);


/**
 * カスタム投稿タイプとマスタ管理の追加をします
 */
function add_custom_post_types() {

	$labels = [
		"name"          => __( '座席', '' ),
		"singular_name" => __( '座席', '' ),
		"add_new"       => __( '座席追加', '' ),
		"add_new_item"  => __( '座席追加', '' ),
		"edit_item"     => __( '座席編集', '' ),
		"menu_name"     => __( '座席', '' ),
		"all_items"     => __( '座席一覧', '' ),
	];
	add_post_type( $labels, 'seat', true, 50 );
}
add_action( 'init', 'add_custom_post_types' );

/**
 * 投稿タイプを追加します
 *
 * @param      $labels
 * @param      $post_type
 * @param bool $show_in_menu
 * @param null $position
 */
function add_post_type( $labels, $post_type, $show_in_menu = true, $position = null ) {
	$args = [
		'label'               => __( $labels['name'], '' ),
		'labels'              => $labels,
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_rest'        => false,
		'rest_base'           => '',
		'has_archive'         => false,
		'show_in_menu'        => $show_in_menu,
		'menu_position'       => $position,
		'exclude_from_search' => false,
		"capability_type" => "post",
//		'capabilities'        => [ 'create_posts' => 'create_' . $post_type ],
		'map_meta_cap'        => true,
		'hierarchical'        => false,
		'rewrite'             => [ 'slug' => $post_type, 'with_front' => true ],
		'query_var'           => true,
		'supports'            => [
			'title'
		],
	];
	register_post_type( $post_type, $args );
}

//wordpressに用意されているアクションフックで独自APIを作成します。

add_action('rest_api_init', function() {
	register_rest_route( 'wp/v2', '/sekitori_api', array(
		'methods' => 'GET',
		'callback' => 'sekitori_api',
	));
});
function sekitori_api( ) {

	global $wpdb;
	//get値からuser_idを取得する
	$user_id = $_GET['user_id'];

	//値が渡って来なかった場合
	if ( empty( $user_id ) ) {
		return urlencode(home_url().'/sekitori/');
	}
	//存在しないuser_idの場合
	$user_data = get_userdata($user_id);
	if(!$user_data){
		return urlencode(home_url().'/sekitori/');
	}

	//すでに座席が確保されているユーザーは確保している座席番号を返して終了
	$my_seat_id_query = "SELECT ID FROM $wpdb->posts posts, $wpdb->postmeta postmeta 
WHERE posts.ID = postmeta.post_id 
AND posts.post_type = 'seat' 
AND posts.post_status = 'publish'
AND postmeta.meta_key = 'user_id'
AND postmeta.meta_value = '".$user_id."'
ORDER BY posts.ID ASC";
	$my_seat_id = $wpdb->get_results( $my_seat_id_query, ARRAY_A );
	if($my_seat_id){
		//自分の座席を確保した状態の席とり表のURLを返す
		return urlencode(home_url().'/sekitori/?seat_id='.$my_seat_id[0]['post_title']);
	}

	//まだユーザーが割り当てられていない座席を取得
	$get_empty_seat_query = "SELECT ID,post_title FROM $wpdb->posts posts, $wpdb->postmeta postmeta 
WHERE posts.ID = postmeta.post_id 
AND posts.post_type = 'seat' 
AND posts.post_status = 'publish'
AND postmeta.meta_key = 'user_id'
AND postmeta.meta_value = ''
ORDER BY posts.ID ASC";
	$empty_seats = $wpdb->get_results( $get_empty_seat_query, ARRAY_A );

	//座席をシャッフル
	shuffle($empty_seats);
	//座席を割り当て
	update_post_meta($empty_seats[0]['ID'],'user_id',$user_id);

	return urlencode(home_url().'/sekitori/?seat_id='.$empty_seats[0]['post_title']);

}
function set_org_query_vars( $query_vars ) {
	$query_vars[] = 'seat_id';       // 座席ID
	return $query_vars;
}
add_filter('query_vars', 'set_org_query_vars');

function seki_yoyaku(){
	global $wpdb;
	$user_id = $_POST['user_id'];
	$seat_post_id = $_POST['seat_post_id'];
	$seat = get_post($seat_post_id);
	$seat_id = $seat->post_title;

	//すでに座席が確保されているユーザーは確保している座席番号を返して終了
	$my_seat_id_query = "SELECT ID FROM $wpdb->posts posts, $wpdb->postmeta postmeta 
WHERE posts.ID = postmeta.post_id 
AND posts.post_type = 'seat' 
AND posts.post_status = 'publish'
AND postmeta.meta_key = 'reserved_user_id'
AND postmeta.meta_value = '".$user_id."'
ORDER BY posts.ID ASC";
	$my_seat_id = $wpdb->get_results( $my_seat_id_query, ARRAY_A );
	if($my_seat_id){
//		return urlencode(home_url().'/sekiyoyaku/?seat_id='.$seat_id);
		// PHPの配列をJSONに変換して出力
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode( home_url().'/sekiyoyaku/' );
		die();
	}
	//座席を割り当て
	update_post_meta($seat_post_id,'reserved_user_id',$user_id);

//	return urlencode(home_url().'/sekiyoyaku/?seat_id='.$seat_id);
	// PHPの配列をJSONに変換して出力
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode( home_url().'/sekiyoyaku/?seat_id='.$seat_id );
	die();
}
add_action( 'wp_ajax_seki_yoyaku', 'seki_yoyaku' );
add_action( 'wp_ajax_nopriv_seki_yoyaku', 'seki_yoyaku' );

function make_pull_down($users,$seat_post_id,$disabled,$user_id){
	$opt = '<option>選択してください</option>';
	$sel = '<select id="member" name="member" onChange="seki_yoyaku(this);" '.$disabled.'>'; // option の value 値を URL とする
	foreach ($users as $one_user){
		$selected = '';
		if($user_id==$one_user->ID){
			$selected = 'selected';
		}
		$opt = $opt.'<option value="'.$one_user->ID.','.$seat_post_id.'" '.$selected.'>'.esc_html($one_user->display_name).'</option>';
	}
	$sel_end = '</select>';
	return $sel.$opt.$sel_end;
}
function make_cancel_down($cancel_button,$seat_post_id){
	if($cancel_button){
		$cancel_button = '&nbsp;&nbsp;<button id="cancel" value="'.$seat_post_id.'">解除</button>';
		return $cancel_button;
	}else{
		return '';
	}
}

function cancel(){
	$user_id = $_POST['user_id'];
	$seat_post_id = $_POST['seat_post_id'];
	delete_post_meta($seat_post_id,'reserved_user_id');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode( home_url().'/sekiyoyaku/' );
	die();
}
add_action( 'wp_ajax_cancel', 'cancel' );
add_action( 'wp_ajax_nopriv_scancel', 'cancel' );
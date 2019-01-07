<?php
/**
 * The template used for displaying page content
 *
 * @package Resonar
 * @since Resonar 1.0
 */
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript">
    // $(document).ready( function(){
    //     $('#names').focus();
    // });
</script>
<link rel='stylesheet' id='my-css'  href='<?php home_url();?>/wp-content/themes/resonar/sekitori.css' type='text/css' media='all' />
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		if ( has_post_thumbnail() && ! post_password_required() ) :
			$featuredimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'resonar-large' );
	?>
		<div class="entry-header-background" style="background-image:url(<?php echo esc_url( $featuredimage[0] ); ?>)">
			<div class="entry-header-wrapper">
				<header id="entry-header" class="entry-header">
					<div class="entry-header-inner">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</div>
					<div class="scroll-indicator-wrapper">
						<a href="#" id="scroll-indicator" class="scroll-indicator"><span class="screen-reader-text"><?php _e( 'Scroll down to see more content', 'resonar' );?></span></a>
					</div>
				</header>
			</div>
		</div>
	<?php else : ?>
		<header class="entry-header">
			<div class="entry-header-inner">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</div>
		</header>
	<?php endif; ?>

	<div class="entry-content-footer">
		<div class="entry-content">
            <table id="tablepress-1" class="tablepress tablepress-id-1">
                <tbody class="row-hover">
                <?php
                //座席のpost_id
                $para_seat_id = get_query_var( 'seat_id' );?>

                <?php
                //orderbyで座席番号順にする？
                $args = array(
	                'posts_per_page'   => -1,
	                'offset'           => 0,
	                'category'         => '',
	                'category_name'    => '',
	                'orderby'          => 'cast(post_title as signed)',
	                'order'            => 'ASC',
	                'include'          => '',
	                'exclude'          => '',
	                'meta_key'         => '',
	                'meta_value'       => '',
	                'post_type'        => 'seat',
	                'post_mime_type'   => '',
	                'post_parent'      => '',
	                'author'	   => '',
	                'post_status'      => 'publish',
	                'suppress_filters' => true
                );
                $seats = get_posts( $args );
                ?>
                <div class="authors">
	                <?php
                    //座席数のカウント
                    $cnt = 1;
	                //一列の座席数
                    $max = 6;
                    //座席行のカウント
	                $row_cnt = 0;

	                $user_cnt = count($seats);
	                echo '
<tr>
<th class="" rowspan=20 width="30px">窓側</th>
<th class="" colspan=6>ロッカー側</th>
</tr>';
                    foreach($seats as $seat) {
                        $color_red = '';
	                    $user_nicename = '';
	                    $avator = '';

                        //席が確保されているか
                        $user_id = get_post_meta($seat->ID,'user_id',true);
                        if($user_id){
                            $user_data = get_userdata($user_id);
	                        $user_nicename = $user_data->display_name;
	                        $user_nicename = ' - '.$user_nicename;

	                        //qrコードをかざしたユーザーの座席を赤くする
	                        if($seat->post_title==$para_seat_id){
		                        $color_red = ' style="background-color:red;"';
	                        }
	                        $avator = get_avatar($user_id, 40);
	                        if($avator){
		                        $avator = '<br>'.$avator;
                            }
                        }
                        if($row_cnt==2){
                            echo '<th class="" colspan=6></th>';
	                        $row_cnt = 0;
                        }
                        //1行目なら
                        if($cnt===1){
                            echo '<tr class="row-'.$cnt.' odd">';
                        }
                        if($cnt<$max){
	                        echo '<td class="column-'.$cnt.'"'.$color_red.'>'.$seat->post_title.$avator.'</td>';
                        }
                        if($cnt===$max||$cnt==$user_cnt){
	                        echo '<td class="column-'.$cnt.'"'.$color_red.'>'.$seat->post_title.$avator.'</td>';
                            echo '</tr>';
	                        $cnt = 1;
	                        $row_cnt++;
	                        continue;
                        }
	                    $cnt++;
		                $uid = $seat->ID; ?>
	                <?php } ?>
                </div>
                </tbody>
            </table>

			<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'resonar' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'resonar' ) . ' </span>%',
					'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
		</div><!-- .entry-content -->

		<?php edit_post_link( __( 'Edit', 'resonar' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>
	</div>
</article><!-- #post-## -->

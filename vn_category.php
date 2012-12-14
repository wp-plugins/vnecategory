<?php
/*
Plugin Name: Vnexpress Category Posts
Plugin URI: http://blog.casanova.vn/
Description: Hien thi cac post va category nhu VNExpress.net
Author: Nguyen Duc Manh
Version: 1.0
Author URI: http://casanova.vn/
*/

class VNEcategory extends WP_Widget {

	function VNEcategory() {
	
		parent::WP_Widget(false, $name='VNEcategory Posts', array( 'description' => __( 'Show parent category and subcategy and post in these categories. See screenshot to know' ) ));
		$this->VNEscript();
		add_theme_support( 'post-thumbnails' ); 
	}
	
	
	
	/**
	
	 * Displays category posts widget on blog.
	
	 */
	
	function widget($args, $instance) {
		global $post;
		$post_old = $post; // Save the post object.
		extract( $args );
	
		// If not title, use the name of the category.
		if( !$instance["title"] ) {
			$category_info = get_category($instance["cat"]);
			$instance["title"] = $category_info->name;
	  }
	
	  $valid_sort_orders = array('date', 'title', 'comment_count', 'rand');
	  
	  if ( in_array($instance['sort_by'], $valid_sort_orders) ) {
		$sort_by = $instance['sort_by'];
		$sort_order = (bool) $instance['asc_sort_order'] ? 'ASC' : 'DESC';
	
	  } else {
		// by default, display latest first
		$sort_by = 'date';
		$sort_order = 'DESC';
	  }
		
		//+++ stickys
		$sticky	=	NULL;
		if($instance['post_sticky']){
			$sticky = get_option( 'sticky_posts' );
			rsort( $sticky );
			array_reverse($sticky);
		}
		
		
		
		$args = array(
			'showposts' => $instance["vne_num"],
			'cat' 		=> $instance["cat"],
			'orderby'	=>	$sort_by,
			'order'		=>	$sort_order,
			'post__in'  => $sticky,
		);
	
	  $cat_posts = new WP_Query($args);
	
		echo $before_widget;
		//++++ show category and sub category
		$parent_cat = $instance["cat"];
		$taxonomy = 'category';
		$cat_children = get_term_children( $parent_cat, $taxonomy );
	?>
     <div class="vne_box" id="vne_box_<?php echo $id; ?>"><!-- vne_box_<?php echo $id; ?> -->
        <table width="100%" cellpadding="0" cellspacing="0" class="vne_box_header" id="vne_box_header_<?php echo $id; ?>">
            <tr>
                <td class="vne_parent"><a href="<?php echo get_category_link($instance["cat"]); ?>"><?php echo get_cat_name($instance["cat"]) ?></a></td>
                <?php
                    if ($cat_children && $instance["show_sub"]):
                        foreach($cat_children as $category):
                        ?>
                 <td class="vne_sub"><a href="<?php echo get_category_link($category);?>"><?php echo get_cat_name($category);?></a></td>
                      <?php      
                      endforeach;
                    endif;
                ?>
            </tr>
        </table>
        
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <?php
                             $i	=	0;
                            while ( $cat_posts->have_posts() ):
                                $cat_posts->the_post();
                                if($i==0):
                            ?>
                                <h2 class="vne_heading"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
                                <div class="vne_thumb"><a href="<?php the_permalink() ?>"><?php echo get_the_post_thumbnail($post->ID, 'thumbnail');  ?></a></div>
                                <div class="vne_desc"><?php the_excerpt(); ?>	</div>
								<br  clear="all" />
                           <?php			
                                endif;
                            $i++;
                            endwhile;
                         ?>
                    </td>
                    <td style="padding-left:20px;">
                        <ul class="vne_box_list">
                        <?php
                             $j	=	0;
                            while ( $cat_posts->have_posts() ):
                                $cat_posts->the_post();
                                if($j!=0):
                            ?>
                                <li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></li>
                           <?php			
                                endif;
                            $j++;
                            endwhile;
                         ?>
                         </ul>
                    </td>
                </tr>
            </table>
		<?php	
			echo "</div><!-- End vne_box_$id -->";
            echo $after_widget;
            $post = $post_old; 
            //wp_reset_query();
        }

	
	/**
	 * Form processing... Dead simple.
	 */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}
	
	/**
	 * The configuration form.
	 */
	
	function form($instance) {
	?>
	<p>
	  <label>
		<?php _e( 'Category' ); ?>
		:
		<?php wp_dropdown_categories( array( 'name' => $this->get_field_name("cat"), 'selected' => $instance["cat"], 'hide_empty'=>0,'tab_index' =>1 ) ); ?>
	  </label>
	</p>
	<p>
		<label><?php _e('Show sub category'); ?>
		<select id="<?php echo $this->get_field_id("show_sub"); ?>" name="<?php echo $this->get_field_name("show_sub"); ?>">
		  <option value="1"<?php selected( $instance["show_sub"], "1" ); ?>>Yes</option>
		  <option value="0"<?php selected( $instance["show_sub"], "0" ); ?>>No</option>
		</select>
		</label>
	</p>
	
	<p>
	  <label for="<?php echo $this->get_field_id("vne_num"); ?>">
		<?php _e('Number of posts to show'); ?>
		:
		<input style="text-align: center;" id="<?php echo $this->get_field_id("vne_num"); ?>" name="<?php echo $this->get_field_name("vne_num"); ?>" type="text" value="<?php echo absint($instance["vne_num"]?$instance["vne_num"]:5); ?>" size='3' />
	  </label>
	</p>
	<p>
	  <label for="<?php echo $this->get_field_id("sort_by"); ?>">
		<?php _e('Sort by'); ?>
		:
		<select id="<?php echo $this->get_field_id("sort_by"); ?>" name="<?php echo $this->get_field_name("sort_by"); ?>">
		  <option value="date"<?php selected( $instance["sort_by"], "date" ); ?>>Date</option>
		  <option value="title"<?php selected( $instance["sort_by"], "title" ); ?>>Title</option>
		  <option value="comment_count"<?php selected( $instance["sort_by"], "comment_count" ); ?>>Number of comments</option>
		  <option value="rand"<?php selected( $instance["sort_by"], "rand" ); ?>>Random</option>
		</select>
	  </label>
	</p>
	<p>
	  <label for="<?php echo $this->get_field_id("asc_sort_order"); ?>">
		<input type="checkbox" class="checkbox" 
	
			  id="<?php echo $this->get_field_id("asc_sort_order"); ?>" 
	
			  name="<?php echo $this->get_field_name("asc_sort_order"); ?>"
	
			  <?php checked( (bool) $instance["asc_sort_order"], true ); ?> />
		<?php _e( 'Reverse sort order (ascending)' ); ?>
	  </label>
	</p>
	<p>
	  <label>
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("post_sticky"); ?>" name="<?php echo $this->get_field_name("post_sticky"); ?>"<?php checked( (bool) $instance["post_sticky"], true ); ?> />
		<?php _e( 'Show post sticky only' ); ?>
	  </label>
	</p>
	<?php
	}
	
	function VNEscript()
	{
		wp_enqueue_style(
			'vne_category',
				plugins_url('/vne_category.css', __FILE__)
			);
	}
}//--- End class
add_action( 'widgets_init', create_function('', 'return register_widget("VNEcategory");') );
?>

<div class="submitbox" id="submitpopup">

<div id="misc-publishing-actions">
<div class="misc-pub-section"> 
</div>
</div>


<div id="major-publishing-actions">
	
	<?php if ( $post_status == 'publish'  ): ?>
		<div id="delete-action">
			<a class="submitdelete deletion" href="<?php echo $delete_link ?>">
				<?php echo __( 'Move to Trash', 'wprp' ); ?>
			</a>
		</div>
	<?php endif; ?>
	
	<div id="publishing-action">
		<?php 
			if ( $post_status != 'publish'  ):
				submit_button( __( 'Create Popup', 'wprp' ), 'primary', 'publish', false );
			else:
				submit_button( __( 'Update Popup', 'wprp' ), 'primary', 'submit', false );
			endif;
		?>
	</div>
	
</div>

<div class="clear"></div>

</div>
<input type="hidden" name="post_type_is_wprp_popup" value="yes" />
<input type="hidden" name="wprp_popup_nonce" value="<?php echo $nonce ?>" />
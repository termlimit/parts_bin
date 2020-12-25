<!--  start message-<?php echo $class; ?> -->
<div id="message-<?php echo $class; ?>">
	<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="<?php echo $class; ?>-left"><?php echo $message;//echo '<pre>'; print_r($_SESSION); echo '</pre>';//$this->Session->flash(); ?></td>
			<td class="<?php echo $class; ?>-right"><a class="close-<?php echo $class; ?>"><img src="/img/table/icon_close_<?php echo $class; ?>.gif"   alt="" /></a></td>
		</tr>
	</table>
</div>
<!--  end message-<?php echo $class; ?> -->
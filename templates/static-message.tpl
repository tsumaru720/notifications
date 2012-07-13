<div class="alert alert-<?=$messageType;?>">
	<?php if (isset($messageTopic) && !empty($messageTopic)) { ?><h4 class="alert-heading"><?=$messageTopic;?></h4><?php } ?>
	<p><?=$messageText;?></p>
</div>

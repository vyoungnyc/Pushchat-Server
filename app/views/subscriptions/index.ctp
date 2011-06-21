<div class="subscriptions index">
<h2><?php __('Subscriptions');?></h2>
<?php
$i = 0;
foreach ($subscriptions as $subscription):
?>
<?php echo ' id:'.$subscription['Subscription']['id']; ?>
<?php echo ' user_id:'.$subscription['Subscription']['user_id']; ?>
<?php echo ' thread_id:'.$subscription['Subscription']['thread_id']; ?>
<?php echo "<br/>"; ?>
<?php endforeach; ?>
</div>

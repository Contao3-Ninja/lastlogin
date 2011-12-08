
<div class="mod_last_login">
  <ul class="members_online">
<?php $count=0; ?>
<?php foreach ($this->users as $user): ?>
    <li><?php echo $user; ?></li>
<?php if ($this->count !==false) { $count++; if ($this->count == $count) {break;} } ?>
<?php endforeach; ?>
  </ul>
</div>

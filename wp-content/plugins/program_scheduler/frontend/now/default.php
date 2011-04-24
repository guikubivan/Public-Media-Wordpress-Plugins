<?php
/*********
REQUIRED VARIABLES
  $ps_query['program'] - contains program properties such as $ps_query['program']->name, which can be accessible with tag functions (see schedule_viewer.php)
*********/
if( !empty($ps_query['program']->url) ): ?>
<a href='<?= ps_program_info_link() ?>' ><?= ps_program_name() ?></a>
<?
else:
    ps_program_name();
endif; ?>
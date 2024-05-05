<?php
use chillerlan\QRCode\QRCode;
 $data = 'https://programmerthailand.com';
 $qr = new QRCode();
?>
<div class="d-flex align-items-center">
    <div class="flex-shrink-0">
      <img src="<?=$qr->render($data)?>" alt="...">
  </div>
  <div class="flex-grow-1 ms-3">
    This is some content from a media component. You can replace this with any content and adjust it as needed.
  </div>
</div>
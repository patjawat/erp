<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($model->ListOrderItems() as $item): ?>
    
       
            <?php for ($x = 1; $x <= $item->qty; $x++):?>
              
               <tr>
                   <th scope="row">1</th>
                   <td> <?php echo $item->asset_item?></td>
                   <td>Otto</td>
                   <td>@mdo</td>
                </tr>
                <?php endfor;?>
    <?php endforeach;?>
  </tbody>
</table>
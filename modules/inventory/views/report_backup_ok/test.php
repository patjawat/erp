<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">item</th>
                <th scope="col">name</th>
                <th scope="col">total</th>
                <th scope="col">total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($querys as $item):?>
            <tr class="">
                <td scope="row"><?=$item['asset_item']?></td>
                <td><?=$item['asset_name']?></td>
                <td><?=$item['total']?></td>
                <td>
                    <?php
                    echo "<pre>";
                    print_r($item);
                    echo "</pre>";

                    ?>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>

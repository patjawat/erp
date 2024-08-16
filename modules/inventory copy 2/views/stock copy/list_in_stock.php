<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">Column 1</th>
                <th scope="col">Column 2</th>
                <th scope="col">Column 3</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listItem as $item): ?>
            <tr class="">
                <td scope="row"><?= $item->product_id ?></td>
                <td><?= $item->qty ?></td>
                <td>R1C3</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
    </table>
</div>

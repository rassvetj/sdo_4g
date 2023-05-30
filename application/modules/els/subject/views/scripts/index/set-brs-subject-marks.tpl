<table>
    <tr>
        <?php
        foreach (array_keys($this->result[0]) as $key) {
            ?>
            <td>
                <?php
                echo $key;
                ?>
            </td>
            <?php
        }
        ?>
    </tr>
    <?php
    foreach ($this->result as $row) {
        ?>
        <tr>
            <?php
            foreach ($row as $col) {
                ?>
                <td>
                    <?php
                    echo $col;
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>
        <?php
    }
    ?>
</table>
Общее количество: <?=count($this->result)?>
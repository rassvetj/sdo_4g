За период <?php echo date('d.m.Y', intval($this->filter->getFromTime())); ?> - <?php echo date('d.m.Y', intval($this->filter->getToTime())); ?> в системе произошли новые события


<?php
    if (sizeof($this->notifies) > 0) {
        echo '<p><b>Новые оповещения</b></p>';
        foreach ($this->notifies as $notify) {
            echo "<p>".$notify."</p>";
        }
    }
    if (sizeof($this->discussions) > 0) {
        echo '<p><b>Новые обсуждения</b></p>';
        foreach ($this->discussions as $dis) {
            echo "<p>".$dis."</p>";
        }
    }
    if (sizeof($this->messages) > 0) {
        echo '<p><b>Новые сообщения</b></p>';
        foreach ($this->messages as $message) {
            echo "<p>".$message."</p>";
        }
    }
?>
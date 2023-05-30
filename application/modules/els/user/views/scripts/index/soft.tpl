<span id="hm-user-software"></span>
<script>
    $('#hm-user-software').html(
        hm.core.ClassManager.require('hm.core.HardwareDetect').get().renderTableBySystemInfo(<?php echo json_encode($this->systemInfo); ?>, true)
    );
</script>
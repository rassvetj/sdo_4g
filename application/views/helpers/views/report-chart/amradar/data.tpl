<chart>
  <axes>
    <?php foreach ($this->data as $axis => $graphs):?>
    <axis xid="<?php echo $axis?>"><?php echo $graphs['title'];?></axis>
    <?php endforeach;?>
  </axes>
  <graphs>
    <?php $data = array();?>
    <?php foreach ($this->data as $axis => $graphs):?>
        <?php foreach ($graphs as $graph => $value):?>
        <?php if ($graph === 'title') continue; ?>
        <?php $data[$graph][$axis] = $value;?>
        <?php endforeach;?>
    <?php endforeach;?>
    <?php foreach ($data as $graph => $axis):?>
    <graph gid="<?php echo $graph;?>" title="<?php echo $this->series['head'][$graph];?>" color="<?php echo $this->colors[$graph]?>" fill_alpha="20">
        <?php foreach ($axis as $key => $value):?>
        <value xid="<?php echo $key?>"><?php echo $value;?></value>
        <?php endforeach;?>
    </graph>
    <?php endforeach;?>
  </graphs>
</chart>
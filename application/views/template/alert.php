<?php if ($this->session->flashdata('success')) : ?>
    <div class="alert alert-success text-left"><!-- success message -->
        <a class="close" href="#" data-dismiss="alert">x</a>
        <div><?php echo $this->session->flashdata('success') ?></div>
    </div>
<?php	$this->session->set_flashdata('success', ''); endif; ?>



<?php if ($this->session->flashdata('error')) : ?>
    <div class="alert alert-danger text-left"><!-- error message -->
        <a class="close" href="#" data-dismiss="alert">x</a>
        <div><?php echo $this->session->flashdata('error') ?></div>
    </div>
<?php	$this->session->set_flashdata('error', ''); endif; ?>




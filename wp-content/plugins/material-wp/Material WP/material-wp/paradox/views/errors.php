<div class="error">
  <p><?php printf(__('%s got some errors while processing your request:', $this->td), $this->config['name']); ?><br>
    <?php foreach ($this->errors as $error) : ?>
      <strong>· <?php echo $error; ?></strong><br>
    <?php endforeach; ?>
  </p>
</div>
<div class="well well-sm">
  <div class="row">
    <div class="container-fluid">
      <h4>
      	<img src="/favicon.ico" width="50"/>Barak Framework
      	<a href="<?= $repo; ?>" class="pull-right" style="margin-top:10px;">GitHub</a>
      </h4>
      <hr>

      <center>
        <h4><?= $title; ?></h4>
        <h5 style="color:#aaa">
          <b><?= $description; ?></b>
          (<a href="<?= $guide; ?>">README.md</a>)
        </h5>

      </center>
      <br/>

      <div class="list-group">

        <?php foreach ($guides as $guide) { ?>

        <a href="<?= $guide['link']; ?>" target="_blank" class="list-group-item list-group-item-action">
          <h5>
            &#8594; <?= $guide["title"]; ?>
            (<code><?= $guide["directory"]; ?></code>)
          </h5>
        </a>

        <?php } ?>

      </div>
    </div>
    <hr>
    <center>
      Copyright &copy; <?php echo date("Y"); ?>
    </center>

  </div>
</div>
</div>
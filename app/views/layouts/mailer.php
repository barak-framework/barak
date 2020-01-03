<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Barak Framework</title>
  <link href="" rel="alternate" title="" type="application/atom+xml" />
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="stylesheet" href="/app/assets/css/syntax.css" type="text/css" />
  <link href="https://fonts.googleapis.com/css?family=Fira+Sans+Condensed" rel="stylesheet" type="text/css">

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="/app/assets/js/html5shiv.min.js"></script>
  <script src="/app/assets/js/respond.min.js"></script>
  <![endif]-->

  <script src="http://code.jquery.com/jquery.js"></script>
  <script src="/app/assets/js/bootstrap.min.js"></script>

  <style type="text/css">
  .mailer_body {
    width: 100% !important
  }
  .mailer_content {
    padding: 0px;
    border-top: 0px solid transparent;
    border-left: 0px solid transparent;
    border-right: 0px solid transparent;
    border-bottom: 0px solid transparent;
  }
  </style>
</head>
<body>
  <div class="mailer_body">
    <div class="mailer_content">
      <?= $yield; ?>
    </div>
  </div>
</body>
</html>

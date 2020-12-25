<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="LoadedFinance provides personal finance services to anyone who wants a powerful and easy to use finance system to accurately monitor their accounts, cash flow, and budget!" />
	<meta name="author" content="LoadedFinance.com" />
	<meta name="keywords" content="Loaded Finance, Finance, Financial, Financial Planning, Budgeting" />

	<title>Loaded Finance - Easy, Secure Financial Planning <?= $this->fetch('title'); ?></title>
	<link rel="shortcut icon" href="/img/favicon.png" />

	<?=$this->Html->css(['bootstrap.min', 'font-awesome.min', 'AdminLTE.min', 'skin-blue-light.min', 'carousel', 'override-carousel'])?>
	<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-1921996-17']);
			_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
	</head>
  <body>
    <div class="navbar-wrapper">
	  <div class="container">

        <nav class="navbar navbar-inverse navbar-static-top">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="https://www.loadedfinance.com/"><img src="/img/logo.png" alt="LoadedFinance.com" height="40px" style="vertical-align:top"></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
              <ul class="nav navbar-nav">
                <li class="<?=($this->name == 'Pages' && ($this->view == 'index')) ? 'active ' : ''?>"><a href="#">Home</a></li>
                <li class="<?=($this->name == 'Pages' && ($this->view == 'about')) ? 'active ' : ''?>"><a href="/about">About</a></li>
                <li class="<?=($this->name == 'Pages' && ($this->view == 'contact')) ? 'active ' : ''?>"><a href="/contact">Contact</a></li>
				<li class="<?=($this->name == 'Pages' && ($this->view == 'faq')) ? 'active ' : ''?>"><a href="/faq">FAQ</a></li>
                <li class="<?=($this->name == 'Users' && ($this->view == 'add')) ? 'active ' : ''?>"><a href="/users/add">Register</a></li>
                <li class="<?=($this->name == 'Users' && ($this->view == 'login')) ? 'active ' : ''?>"><a href="/users/login">Login</a></li>
              </ul>
            </div>
          </div>
        </nav>

      </div>
	<?=$this->fetch('content')?>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script src="/js/jquery-2.1.4.min.js"></script>
	<script src="/js/jquery-ui.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/app.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>

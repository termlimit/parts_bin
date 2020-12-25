<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Open source parts bin tracker">
    <meta name="author" content="Partsbin.com">
	<meta name="keywords" content="Open source parts tracking, project tracking, and asset management" />

	<title><?=$this->fetch('title')?> | <?=__('myparts.loadedfinance.com')?></title>
	<link rel="shortcut icon" href="/img/favicon.png" />
	
	<?=$this->Html->css(['bootstrap.min', 'font-awesome.min', 'daterangepicker', 'datepicker3', 'AdminLTE.min', 'skin-green.min', 'override-bootstrap', 'override-jquery-ui', 'bootstrap-tour.min'])?>
    <?=$this->fetch('css')?>
</head>
<body class="skin-green sidebar-mini">
<div class="wrapper">
    <header class="main-header">

        <!-- Logo -->
        <a href="" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini">PB</span>
			<span class="logo-lg">Parts Bin</span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
				  <li class="hidden-sm hidden-xs">
					<a href="#" id="help" data-route="<?=strtolower($title)?>.<?=strtolower($action)?>">
					  <i class="fa fa-question-circle"></i>
					</a>
				  </li>
				  <li>
				    <span style="color:#fff;padding: 15px;display: block;line-height: 20px;">
					  <span class="hidden-xs" ></span>
					</span>
				  </li>
				  <li id="sidebar-toggle">
				    <a href="#" data-toggle="control-sidebar" id="daterange"></a>
				  </li>
				  <!-- User Account: style can be found in dropdown.less -->
				  <li class="dropdown user user-menu">
				    <span style="cursor:default;color:#fff;padding: 15px;display: block;line-height: 20px;">
					  <span class="hidden-xs">email</span>
					</span>
				  </li>
				</ul>
			</div>
        </nav>
    </header>
	<?=$this->element('leftsidebar')?>
	<!-- LAYOUT HERE -->
	<?=$this->fetch('content')?>
	<?=$this->element('footer')?>
</div>
<!-- ./wrapper -->

<!-- Modal -->
<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="helpModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="helpTitle">&nbsp;</h4>
            </div>
            <div class="modal-body" id="helpBody">&nbsp;</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>
<script src="/js/daterangepicker.js"></script>
<script src="/js/bootstrap-datepicker.js"></script>
<script src="/js/app.min.js"></script>
<script src="/js/bootstrap-tour.min.js" type="text/javascript"></script>

<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/js/ie10-viewport-bug-workaround.js"></script>
<?=$this->fetch('script')?>
<?=$this->fetch('scriptBottom')?>
<script src="/js/lf.js"></script>
<script src="/js/help.js"></script>
<?=$this->fetch('chartTop')?>
<?=$this->fetch('chartBottom')?>
</body>
</html>
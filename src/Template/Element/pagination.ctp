<div class="box-footer clearfix">
	<ul class="pagination no-margin pull-right">
		<?=$this->Paginator->first('&laquo; First', ['escape' => false])?>
		<?=$this->Paginator->prev('&lsaquo; Prev', ['escape' => false])?>
		<?=$this->Paginator->numbers(['modulus' => 8])?>
		<?=$this->Paginator->next('Next &rsaquo;', ['escape' => false])?>
		<?=$this->Paginator->last('&raquo; Last', ['escape' => false])?>
	</ul>
</div>
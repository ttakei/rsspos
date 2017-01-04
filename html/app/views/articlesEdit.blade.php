@extends('layout.admin')

@section('css')
<link href="/js/air/dist/css/datepicker.min.css" rel="stylesheet" type="text/css">
@endsection

@section('js')
<script src="/js/air/dist/js/datepicker.min.js"></script>
<script src="/js/air/dist/js/i18n/datepicker.en.js"></script>
<script>
$(function(){
	$("#id-field-title_rewrite").bind("change keyup",function(){
	var count = $(this).val().length;
		$("#id-field-title_rewrite-cnt").text(count);
	});
	$("#id-field-seo_desc").bind("change keyup",function(){
	var count = $(this).val().length;
		$("#id-field-seo_desc-cnt").text(count);
	});
});
</script>
@endsection

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">記事内容編集 <span class="label label-danger">注意：保存しないとSEO系、予約投稿時間が保存されません=予約投稿は出来ません</span></div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
				<img src="{{$articleObj->imgurl}}" width=360 height=240>
			</div>
			<div class="col-md-6">
				<?php
				$movSite = $articleObj->movSite;
				if($movSite!=''){
					$tpl = array_get(Config::get('app.movtag'),$articleObj->movSite);
					$tpl = str_replace("#movid#",$articleObj->movid,$tpl);
					$tpl = str_replace("#title#",$articleObj->title,$tpl);
					echo $tpl;
				}
				?>
			</div>
		</div>
		{{Form::open(array('url'=>'rss/article/edit'))}}
		{{Form::hidden('id',$articleObj->id)}}

		{{--Form::staticField('blog_name','配信元',$articleObj->blogname)--}}
		{{--Form::staticField('url','記事URL',$articleObj->url)--}}

		{{Form::textField('title_rewrite','タイトル <span class="badge" id="id-field-title_rewrite-cnt">'.mb_strlen($articleObj->title_rewrite).'</span> <small>'.$articleObj->title.'</small>',$articleObj->title_rewrite)}}
		{{--Form::textField('movid','動画ID',$articleObj->movid)--}}
		{{--Form::textField('movSite','動画サイト（テンプレート適用）',$articleObj->movSite)--}}
		<div class="form-group">
		<label for="id-field-category" class="control-label">カテゴリ</label>
		@if(count($categoryAry)>0)
		<div class="checkbox">
		@foreach($categoryAry as $k=>$v)
		<label>
		@if(in_array($k,explode(',',$articleObj->category)))
		<input type="checkbox" name="category[]" value="{{$k}}" checked>{{$v}}
		@else
		<input type="checkbox" name="category[]" value="{{$k}}">{{$v}}
		@endif
		</label>
		@endforeach
		</div>
		@else
		カテゴリが設定されていません
		@endif
		</div>

		{{--Form::checkboxField('category','カテゴリ',$categoryAry)--}}
		<div class="form-group">
		<label for="id-field-tag" class="control-label">タグ</label>
		@if($site->tags!='' && count(explode(',',$site->tags))>0)
		<div class="checkbox">
		@foreach(explode(',',$site->tags) as $v)
		<label>
		@if(in_array($v,explode(',',$articleObj->tag)))
		<input type="checkbox" name="tag[]" value="{{$v}}" checked>{{$v}}
		@else
		<input type="checkbox" name="tag[]" value="{{$v}}">{{$v}}
		@endif
		</label>
		@endforeach
		</div>
		@else
		<div class="alert alert-danger">
			サイトのタグが設定されていません<br>
			<a href="{{route('siteedit',['id'=>$site->id])}}">設定する</a>
		</div>
		@endif
		</div>
		{{Form::textField('tag[]','タグ',$articleObj->tag)}}

		{{--Form::textField('seo_title','SEO タイトル',($articleObj->seo_title!='')?$articleObj->seo_title:$articleObj->title_org )--}}

		<?php
			$seo_desc = ($articleObj->seo_desc =='')?$articleObj->title_org:$articleObj->seo_desc;
		?>
		{{Form::textField('seo_desc','SEO DESCRIPTION <span class="badge" id="id-field-seo_desc-cnt">'.mb_strlen($seo_desc).'</span>',$seo_desc)}}

		{{--Form::textField('seo_keyword','SEO KEYWORD',($articleObj->seo_keyword!='')?$articleObj->seo_keyword:$articleObj->tag )--}}

		{{--Form::textField('posted_at','投稿時刻 0000-00-00 00:00:00 の場合未投稿',($articleObj->posted_at=='0000-00-00 00:00:00')?'未投稿':$articleObj->posted_at )--}}

		{{Form::textField('researved_at','予約時刻(変更しなければ次回投稿)',($articleObj->researved_at!='0000-00-00 00:00:00')?$articleObj->researved_at:date('Y-m-d H:i:s'),['class'=>'datepicker-here form-control','data-language'=>'en','data-position'=>'top left','data-timepicker'=>true,'data-time-format'=>'hh:ii:00','data-date-format'=>'yyyy-mm-dd'])}}


		{{Form::submit('保存',array('class'=>'btn btn-primary'))}}
		{{Form::close()}}
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop

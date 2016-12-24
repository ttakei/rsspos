<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useFiles(storage_path().'/logs/laravel.log');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

if( !Config::get( 'app.debug' ) ){
    App::error(function(Exception $exception, $code)
    {
        $message = $exception->getMessage() == '' ? 'error:'.$code : $exception->getMessage();
        Log::error($exception);
        return $message;
    });
}else{
    App::error(function(Exception $exception, $code)
    {
       Log::error($exception);
   });
}

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function()
{
	return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

Form::macro('rbinline',function($name,$label=null,$values=null,$val,$attributes=[]){
  $html = '';
  foreach($values as $k=>$v){
    $check = ($val==$k)?'checked':'';
    $html .= sprintf("<label class='radio-inline'><input type='radio' name='%s' id='%s' value='%s'%s>%s</label>",$name,$name.'-'.$k,$k,$check,$v);
  }
  return fieldWrapper($name, $label, $html);
  return $html;
});

Form::macro('rb',function($name,$values,$val){
 $markup="";
 foreach($values as $key=>$value){
 $markup.='<div class="radio">'."\n";
 $markup.='<label class="radio-inline"><input type="radio"';
 $markup.=' name='.$name;
 $markup.=' value='.$key;
 if($val==$key){
 $markup.=' checked';
 }
 $markup.='> '.$value."\n";
 $markup.='</label></div>'."\n";
 }
 return $markup;
});

Form::macro('staticField', function($name, $label = null, $value = null, $attributes = [])
{
  $element = '<p class="form-control-static">'.$value.'</p>';

  return fieldWrapper($name, $label, $element);
});

 Form::macro('textField', function($name, $label = null, $value = null, $attributes = array())
{
    $element = Form::text($name, $value, fieldAttributes($name, $attributes));

    return fieldWrapper($name, $label, $element);
});

Form::macro('passwordField', function($name, $label = null, $attributes = array())
{
    $element = Form::password($name, fieldAttributes($name, $attributes));

    return fieldWrapper($name, $label, $element);
});

Form::macro('textareaField', function($name, $label = null, $value = null, $attributes = array())
{
    $element = Form::textarea($name, $value, fieldAttributes($name, $attributes));

    return fieldWrapper($name, $label, $element);
});

Form::macro('selectField', function($name, $label = null, $options, $value = null, $attributes = array())
{
    $element = Form::select($name, $options, $value, fieldAttributes($name, $attributes));

    return fieldWrapper($name, $label, $element);
});

Form::macro('selectMultipleField', function($name, $label = null, $options, $value = null, $attributes = array())
{
    $attributes = array_merge($attributes, ['multiple' => true]);
    $element = Form::select($name, $options, $value, fieldAttributes($name, $attributes));

    return fieldWrapper($name, $label, $element);
});

Form::macro('checkboxField', function($name, $label = null, $value = 1, $checked = null, $attributes = array())
{
    $attributes = array_merge(['id' => 'id-field-' . $name], $attributes);

    $out = '<div class="checkbox';
    $out .= fieldError($name) . '">';
    $out .= '<label>';
    $out .= Form::checkbox($name, $value, $checked, $attributes) . ' ' . $label;
    $out .= '</div>';

    return $out;
});

function fieldWrapper($name, $label, $element)
{
    $out = '<div class="form-group';
    $out .= fieldError($name) . '">';
    $out .= fieldLabel($name, $label);
    $out .= $element;
    $out .= '</div>';

    return $out;
}

function fieldError($field)
{
    $error = '';

    if ($errors = Session::get('errors'))
    {
        $error = $errors->first($field) ? ' has-error' : '';
    }

    return $error;
}

function fieldLabel($name, $label)
{
    if (is_null($label)) return '';

    $name = str_replace('[]', '', $name);

    $out = '<label for="id-field-' . $name . '" class="control-label">';
    $out .= $label . '</label>';

    return $out;
}

function fieldAttributes($name, $attributes = array())
{
    $name = str_replace('[]', '', $name);

    return array_merge(['class' => 'form-control', 'id' => 'id-field-' . $name], $attributes);
}

function siteList1(){

    $sitesList = Sites::select('acc','name')->where('userid',Session::get('user'))->get();

    $selectsite = '<div class=""><div class="input-group"><select style="font-size:13px;vertical-align:middel" class="accsel form-control" name="acc" onchange="submit(this.form)">';

    if(Session::get('acc')==''){
        $selectsite .= '<option>選択してください</option>';
    }

    foreach($sitesList as $k=>$v){

        if(Session::get('acc')==$v['acc']){
            $selectsite .= sprintf("<option value='%s' selected>%s</option>",$v['acc'],$v['name']);
        }else{
            $selectsite .= sprintf("<option value='%s'>%s</option>",$v['acc'],$v['name']);
        }
    }
    return $selectsite.'</select><span class="input-group-btn">
        <button class="btn btn-default" type="submit">Go!</button>
      </span></div></div>';
}

function fetchMultiUrl($urls, $timeout = 10, &$errorUrls = array()) {

    $mh = curl_multi_init();

    foreach ($urls as $key => $url) {
        $conn[$key] = curl_init($url);
        curl_setopt($conn[$key], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($conn[$key], CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn[$key], CURLOPT_FAILONERROR, 1);
        curl_setopt($conn[$key], CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conn[$key], CURLOPT_MAXREDIRS, 3);

        if ($timeout) {
            curl_setopt($conn[$key], CURLOPT_TIMEOUT, $timeout);
        }

        curl_multi_add_handle($mh, $conn[$key]);
    }

    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active and $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    //データを取得
    $res = array();
    foreach ($urls as $key => $url) {
        if (($err = curl_error($conn[$key])) == '') {
            $res[$key] = curl_multi_getcontent($conn[$key]);
        } else {
            $errorUrls[$key] = $urls[$key];
        }
        curl_multi_remove_handle($mh, $conn[$key]);
        curl_close($conn[$key]);
    }
    curl_multi_close($mh);

    return $res;
}

function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

function curl_get_contents( $url, $timeout = 10 ){
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36');
    curl_setopt( $ch, CURLOPT_HEADER, false );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    $result = curl_exec( $ch );
    curl_close( $ch );
    return $result;
}

function ImgUrlExtraction($text1=NULL, $text2=NULL)
{

  $pattern = '/(https?)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)\.(jpg|jpeg|gif|png)/i';

  if($text1){
    $matches = array();
    preg_match_all($pattern, $text1, $matches, PREG_SET_ORDER);
    foreach($matches as $val){
      $headers = @get_headers($val[0]);
      if(strpos($headers[0], 'OK'))
      return $val[0];
    }
  }

  if($text2){
    $matches = array();
    preg_match_all($pattern, $text2, $matches, PREG_SET_ORDER);
    foreach($matches as $val){
      $headers = @get_headers($val[0]);
      if(strpos($headers[0], 'OK'))
        return $val[0];
    }
  }

  return NULL;
}

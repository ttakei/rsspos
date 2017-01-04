  @if(Session::has('errors'))
  <div class="alert alert-danger" role="alert">
  @foreach(Session::get('errors')->all() as $msg)
  {{$msg}}<br>
  @endforeach
  </div>
  @endif

  @if(Session::has('messages'))
  <div class="alert alert-danger" role="alert">
  @foreach(Session::get('messages')->all() as $msg)
  {{$msg}}<br>
  @endforeach
  </div>
  @endif
  
  @if(Session::has('msg'))
  <div class="alert alert-success alert-msg">{{Session::get('msg')}}</div>
  @endif

  @if(Session::has('error-msg'))
  <div class="alert alert-danger alert-msg">{{Session::get('error-msg')}}</div>
  @endif

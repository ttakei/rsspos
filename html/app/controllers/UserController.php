<?php

class UserController extends BaseController
{

  public function __construct(){
    $this->beforeFilter('admauth');
  }

  public function index(){
    $cfg['selected']='';
    //$userSites = Users::find(Session::get('user'))->sites->lists('site_id');
    //dd($userSites);

    $userObj = Users::orderBy('id','asc')->get();
    return View::make('user.index',compact('userObj','cfg'));
  }

  public function create(){
    $cfg['selected']='';
    return View::make('user.create',compact('cfg'));
  }

  public function store(){
    $input = Input::except('_token','sites');

    // if(!preg_match("/^[A-Za-z0-9]+$/",$input['tag'])){
    //   return \Redirect::back()
    //     ->withInput()
    //     ->with('error-msg','サイトアカウントは半角英数のみ利用可能です');
    // }

    $rules = array(
      'email'=>'required|unique:users,email',
      'password'=>'required|between:1,20',
      'nickname'=>'required'
    );

    $messages = array(
      'email.required' => 'メールアドレスを入力してください',
      'email.unique' => 'すでに登録されたメールアドレスです',
      'password.required'=>'パスワードを入力してください',
      'password.between'=>'パスワードは:min - :max文字で設定してください',
      'nickname.required'=>'ニックネームを入力してください',
    );

    //バリデーション処理
    $val=\Validator::make($input,$rules,$messages);
    if($val->fails()){
      return \Redirect::back()
        ->withErrors($val->errors())
        ->withInput();
    }

    $user = Users::create($input);

    $sites = Input::get('sites');
    UserSites::where('users_id',$user->id)->delete();
    foreach($sites as $site_id){
      UserSites::create(['users_id'=>$user->id,'site_id'=>$site_id]);
    }

    return Redirect::route('admin.user.index')->with('msg','保存しました');
  }

  public function edit($id){
    $cfg['selected']='';

    //dd(Users::find($id)->sites->lists('site_id'));

    $user = Users::find($id);
    return View::make('user.edit',compact('user','cfg'));
  }

  public function update(){
    $input = Input::except('_token','id','sites');
    $id = Input::get('id');

    $rules = array(
      'email'=>'required|unique:users,email,'.$id,
      'password'=>'required|between:1,20',
      'nickname'=>'required'
    );

    $messages = array(
      'email.required' => 'メールアドレスを入力してください',
      'email.unique' => 'すでに登録されたメールアドレスです',
      'password.required'=>'パスワードを入力してください',
      'password.between'=>'パスワードは:min - :max文字で設定してください',
      'nickname.required'=>'ニックネームを入力してください',
    );

    //バリデーション処理
    $val=\Validator::make($input,$rules,$messages);
    if($val->fails()){
      return \Redirect::back()
        ->withErrors($val->errors())
        ->withInput()
        ->with('messages');
    }

    Users::where('id',$id)->update($input);

    $sites = Input::get('sites');
    UserSites::where('users_id',$id)->delete();
    foreach($sites as $site_id){
      UserSites::create(['users_id'=>$id,'site_id'=>$site_id]);
    }

    return Redirect::route('admin.user.index')->with('msg','保存しました');
  }

  public function delete($id){
    Users::where('id',$id)->delete();
    return Redirect::route('admin.user.index')->with('msg','削除しました');
  }
}

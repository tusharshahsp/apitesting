<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use Mail;
use DB;
//use DateTime;
use Log;
//auth
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
//tables
use App\Models\Logins;
use App\Models\ToiletRegister;
use App\Models\UserRegister;
use App\Models\ToiletImages;
//image
use Image;
use File;
use Storage;
class ToiletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * create new toilet entry
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'toilet_location' => 'required|min:5'
            //'toilet_ward' => 'required|email|unique:login,username',
           // 'toilet_address' => 'required|digits:10|unique:User_Register,contact',
           // 'toilet_organisation' => 'required|min:5'
        ]);

        if ($validator->fails()) {
            return json_encode($validator->errors());
        }

        DB::transaction(function($request) use ($request){

            //adding toilet details
            $user = JWTAuth::parseToken()->authenticate();//finding the user from the token
            $userid=UserRegister::where(['email'=>$user->username])->pluck('id');//getting user_id

            $toiletdetails=new ToiletRegister;
            $toiletdetails->location=$request->toilet_location;
            $toiletdetails->user_id=$userid;
            if($request->toilet_ward!=null)
                $toiletdetails->ward=$request->toilet_ward;
            if($request->toilet_address!=null)
                $toiletdetails->address=$request->toilet_address;
            if($request->toilet_organisation!=null)
                $toiletdetails->organisation=$request->toilet_organisation;
            if($request->toilet_ownership!=null)
                $toiletdetails->ownership=$request->toilet_ownership;
            $toiletdetails->save();
            


            // //Send mail to user for confirmation
            // Mail::queue('email.team_member_invite', ['email' => $request->user_email, 'token' => $v_token_mem1, 'password' => $mem1_pwd, 'name' => ucwords(strtolower($request->teamMember1_first_name)), 'team_id' => $teamId], function($message) use ($mem1_email)
            // {
            //     $message
            //     ->to($mem1_email)
            //     ->cc('admin@e-yantra.org')
            //     ->subject('Registration: FindaLoo')
            //     ->from('admin@e-yantra.org', 'e-Yantra IITB');
            // });

           
        });//end of transaction

     // return redirect('register')->with('success','Thank you for registering! A confirmation email has been sent to each team member. Each team member should click on the Activation link to activate their account. Follow the validation instructions as specified in confirmation e-mail to complete the registration process.');
        $data=array("status"=>"success","data"=>null, "message"=>"Thank you for registering the new Toilet! It will be visible on the map after verification");
        return json_encode($data);
    }//end of create

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
         //
        $toiletdetails = ToiletRegister::all();
        $data=array("status"=>"success","data"=>$toiletdetails, "message"=>"Toilet data fetched");
        return json_encode($data);
        // $user = JWTAuth::parseToken()->authenticate();
        // $username=$user->username;
        // return response()->json(compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * photo upload for Toilets
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'toiletPht' => 'required|image:jpeg,bmp,png|max:3000',
            ],

            [
            'toiletPht.required' => 'Please select a photo.',
            'toiletPht.image' => 'Please upload only .jpg/.png/.bmp file.',
            'toiletPht.max' => 'Size of the file should be less than 3MB.',
            // 'toiletPht.dimensions' => 'The image should be of at least 800 x 600 resolution'
        ]);
        if ($validator->fails()) {
            $data=array("status"=>"fail","data"=>$validator->errors(), "message"=>"Photo upload failed");
            return json_encode($data);
        }
        DB::transaction(function($request) use ($request){
            $path = public_path().'/img/toilets/';
            $user = JWTAuth::parseToken()->authenticate();//finding the user from the token
            $userid=UserRegister::where(['email'=>$user->username])->pluck('id');//getting user_id
            $ext = strtolower($request->file('toiletPht')->getClientOriginalExtension());
            $filename = str_random(15).".".$ext;
            $request->file('toiletPht')->move($path, $filename);
            $toiletPhoto=new ToiletImages;
            $toiletPhoto->user_id=$userid;
            $toiletPhoto->toilet_id=$request->toilet_id;
            $toiletPhoto->image_name=$filename;
            if($request->image_title!=null)
                $toiletPhoto->image_title=$request->image_title;
            $toiletPhoto->active=1;
            $toiletPhoto->save();
        });
        $data=array("status"=>"success","data"=>null, "message"=>"Image uploaded");
        return json_encode($data);

    }
    /**
     * for fetching all images
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showImages()
    {
        $ToiletImages = ToiletImages::all();
        $data=array("status"=>"success","data"=>$ToiletImages, "message"=>"All Images fetched");
        return json_encode($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

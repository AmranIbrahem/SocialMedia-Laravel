<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckCodePasswordRequest;
use App\Http\Requests\Auth\EmailVerifiedRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Requests\Auth\updatePasswordRequest;
use App\Http\Responses\Response;
use App\Mail\PasswordEmail;
use App\Models\User\User;
use App\Models\User\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Register :
    public function register(RegistrationRequest $request)
    {
        $user = User::create([
            "FirstName" => $request->FirstName,
            "LastName" => $request->LastName,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "recovery_code"=> mt_rand(5000,500000)
        ]);
        $profile=UserProfile::create([
           "user_id"=>$user->id
        ]);

        $token =JWTAuth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);
//        try {
////            Mail::to($user->email)->send(new EmailVerification("http://localhost:8000/api/EmailVerified1/$user->id",$user->nmae))
//            Mail::to($user->email)->send(new EmailVerification($user->recovery_code,$user->nmae));
//
//        } catch (\Exception $e) {
//            $user->delete();
//            return Response::Message('There is a problem sending the email confirmation code or the email does not exist',401);
//        }

        if ($user) {
            return Response::AuthSuccess("Registration successfully",$user,$token,200);
        } else {
            return Response::Message("Registration failed..!",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Login :
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user){
            $token = JWTAuth::attempt([
                'email' => $request->email,
                'password' => $request->password
            ]);

            if($token){
                return Response::AuthSuccess("User login successfully",$user,$token,200);
            } else{
                return Response::Message("Password does not match.",422);
            }
        }else{
            return Response::Message("The email dose not match ",401);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Logout :
    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        try {
            // invalidate token
            JWTAuth::invalidate(JWTAuth::getToken());
            return Response::logout(true,'Logout successfully',200);
        } catch (JWTException $e) {
            return Response::logout(false,'Failed to logout..!',500);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Email verified :
    public function EmailVerified(EmailVerifiedRequest $request,$id){
        $user=User::where('id',$id)->first();
        if($user->email_verified_at==true){
            return Response::Message("The account is already confirmed",200);
        }else{
            if($user) {
                $check=User::where('id',$id)->where('recovery_code',$request->recovery_code)->first();
                if($check){
                    $check->email_verified_at = \Carbon\Carbon::now();
                    $check->save();
                    return Response::Message("The email has been confirmed successfully ",200);
                }else{
                    return Response::Message("Recovery Code don't macht",401);
                }
            }else{
                return Response::Message("ID User Noy Found'",401);
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Forgot Password:
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->recovery_code=mt_rand(5000, 5000000);
            Mail::to($user->email)->queue(new PasswordEmail ($user->recovery_code,$user->FirstName));

            $user->update();
            return Response::PasswordSuccess($user->id,202);
        } else {
            return Response::Message("The email dose not match ",401);
        }
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public  function CheckCodePassword(CheckCodePasswordRequest $request,$id){
        $user=User::find($id);
        if($user){
            $check=User::where('id',$id)->where('recovery_code',$request->recovery_code)->first();
            if($check){
                return Response::Message('recovery code match ',200);
            }else{
                return Response::Message('recovery code dose not match ',401);
            }

        }else{
            return Response::Message('ID User dose not match ',401);
        }
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function updatePassword(updatePasswordRequest $request, $user_id)
    {
        $user = User::find($user_id);

        if ($user) {
            $user->password = Hash::make($request->password);

            $user->save();

            $token = auth()->attempt([
                'email' => $user->email,
                'password' => $request->password,
            ]);
            return Response::AuthSuccess("User login successfully",$user,$token,200);
        } else {
            return Response::Message('id failed..!',422);
        }
    }

}

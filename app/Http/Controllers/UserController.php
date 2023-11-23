<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;



class UserController extends Controller
{
    public function Register(Request $request){

        $validation = Validator::make($request->all(),[
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
            
            
        ]);

        if($validation->fails())
            return $validation->errors();

        return $this -> createUser($request);
        
    }

    private function createUser($request){
        $user = new User();
        $user -> name = $request -> post("name");
        $user -> email = $request -> post("email");
        $user -> password = Hash::make($request -> post("password"));   
        $user -> save();
        
         $this -> enviarNotificacionPorEmail("JefeSupremo@email.com", "¡Bienvenido {$user->name}!, tu usuario se creo correctamente!", $user->email, "Estoy contento de que formes parte de este mundillo, divertita pa");

        return $user;
    }

    public function ValidateToken(Request $request){
        return auth('api')->user();
    }

    public function Logout(Request $request){
        $request->user()->token()->revoke();
        return ['mensaje' => 'Token rebocado'];
        
        
    }

    public function CambiarContra(Request $request){
        $user = $request->user();
        if(Hash::check($request -> post("contrasena_actual"),$user->password)){
            $validation = Validator::make($request->all(),[
            'password' => 'required|confirmed'
            ]);

            if($validation->fails())
                return $validation->errors();
            $user -> password = Hash::make($request -> post("password"));
            $user -> save();
            return response()->json(["mensaje"=>"Contraseña cambiada con exito!"]);
        }
        return response()->json(["mensaje"=>"Contraseña actual no valida."]);
    }

    public function Find(Request $request, $idUsuario){
        return $tarea = User::FindOrFail($idUsuario);
    }

    private function enviarNotificacionPorEmail($from, $subjet, $to, $body){
         $response = Http::withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ])-> post("mail-api.tareas-namespace.svc.cluster.local/api/enviar",[
            "from"=> $from,
           "subject"=> $subjet,
          	"to"=> $to,
            "body"=> $body

        ]);



    }  


    
}

<?php

namespace App\Http\Controllers;

use App\Helpers\BestHelper;
use App\Helpers\Helpers;
use App\Http\Requests\LoginRequest_v1;
use App\Http\Requests\TokenRequest_v1;
use App\Http\Resources\TokenResource;
use App\Models\blacklist;
use App\Models\Token;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Rutas para el manejo de sesiones')]
class AuthController extends Controller
{
    //Conformar el token
    public function conformarToken($algoritmo, $tipo, $emisor, $idUsuario, $dispositivo, $fechaCreacion, $fechaValidez,  $fechaExpiracion, $idToken){
        $headerToken = json_encode(['alg' => $algoritmo,
                                    'typ' => $tipo]);

        $payloadToken = json_encode([   'iss' => $emisor,
                                        'sub' => $idUsuario,
                                        'aud' => $dispositivo,
                                        'exp' => $fechaExpiracion,
                                        'nbf' => $fechaValidez,
                                        'iat' => $fechaCreacion,
                                        'jti' => $idToken]);

        $secretKeyApk = env('SECRET_KEY');
        $unsignedToken = base64_encode($headerToken).'.'.base64_encode($payloadToken);
        $signatureToken = hash_hmac('sha256', $unsignedToken, $secretKeyApk);

        $token = $unsignedToken.'.'.$signatureToken;

        return $token;
    }

    /**
    * Login de usuario
    * @unauthenticated
    */
    public function login(LoginRequest_v1 $request){

        //Extraer usuario de la BD
        $usuario = User::with('persona')->where('usuario', $request->usuario)->first();

        //Comprueba si el usuario está registrado en la BD
        if (!$usuario) {
            AuditoriaController::auditoria('Inicio de sesión fallido.','---', '---', $request->ip());
            return BestHelper::Data(null, 403, false, 'Usuario no registrado.');
        }

        //Validar la cantidad de intentos al sistema
        if ($usuario->int >= 3) {
            $usuario->estado = false;
            $usuario->save();

            AuditoriaController::auditoria('Usuario '.($usuario->persona ? trim(($usuario->persona->nombres ?? "") . " " . ($usuario->persona->apellidos ?? "")) : $usuario->usuario).' bloqueado', $usuario->usuario, ($usuario->persona ? trim(($usuario->persona->nombres ?? "") . " " . ($usuario->persona->apellidos ?? "")) : $usuario->usuario), $request->ip());
            return BestHelper::Data(null, 406, false, 'Usuario bloqueado(El usuario a excedido el numero de intentos para acceder a la página por favor contactar con su administrador).');
        }

        //Comprueba si el usuario está activo
        if (!$usuario->estado) {
            if ($usuario->id !== 1) {
                $usuario->int = $usuario->int + 1;
                $usuario->save();
            }

            AuditoriaController::auditoria('Inicio de sesión fallido(Usuario inactivo).', $usuario->usuario, ($usuario->persona ? trim(($usuario->persona->nombres ?? "") . " " . ($usuario->persona->apellidos ?? "")) : $usuario->usuario), $request->ip());
            return BestHelper::Data(null, 400, false, 'Usuario inactivo.');
        }

        //Comprueba si el usuario ya cambio su contraseña x primera vez
        if (!$usuario->valid) {
            return BestHelper::Data(null, 402, false, 'Usuario necesita cambiar la contraseña por primera vez.');
        }

        //Comprueba si el usuario y la contraseña son válidos
        if (!Hash::check($request->password, $usuario->password)) {
            if ($usuario->id !== 1) {
                $usuario->int = $usuario->int + 1;
                $usuario->save();
            }

            AuditoriaController::auditoria('Inicio de sesión fallido(Contraseña inválida).', $usuario->usuario, ($usuario->persona ? trim(($usuario->persona->nombres ?? "") . " " . ($usuario->persona->apellidos ?? "")) : $usuario->usuario), $request->ip());
            return BestHelper::Data(null, 401, false, 'Usuario y/o contraseña inválidos.');
        }

        //Comprueba si la contraseña del usuario ya expiró
        if ($usuario->passwordHasExpired()) {
            if ($usuario->id !== 1) {
                $usuario->int = $usuario->int + 1;
                $usuario->save();
            }

            AuditoriaController::auditoria('Inicio de sesión fallido(Contraseña expirada).', $usuario->usuario, ($usuario->persona ? trim(($usuario->persona->nombres ?? "") . " " . ($usuario->persona->apellidos ?? "")) : $usuario->usuario), $request->ip());
            return BestHelper::Data(null, 405, false, 'Contraseña expirada.');
        }

        //Cerrar sesión previamente abierta (si existe)
        Token:: where('user_id', $usuario->id)->
                where('dispositivo', $request->terminal)->delete();

        //Abrir una nueva sesión
        $token = new Token;
        $token->user_id = $usuario->id;
        $token->dispositivo = Str::lower($request->terminal);
        $token->timezone = $request->has('zona_horaria') ? $request->zona_horaria: 'America/Havana';
        $token->validez_ini = $request->has('inicio')? $request->inicio: env('VALIDEZ_INI', '+0 min');
        $token->validez_inter = $request->has('intermedio')? $request->intermedio: env('VALIDEZ_INTER', '+30 min');
        $token->validez_fin = $request->has('fin')? $request->fin: env('VALIDEZ_FIN', '+30 min');
        $token->save();


        //Conformar el token
        $validezIni = new DateTime($token->created_at);
        $validezIni->modify($token->validez_ini);

        $validezFin = new DateTime($token->created_at);
        $validezFin->modify($token->validez_fin);

        $token->token = $this->conformarToken(env('ALGORITMO', 'sha256'), env('TIPO', 'JWT'), env('EMISOR', 'cdasi'), $token->usuario_id, $token->dispositivo, $token->created_at->format('U'), $validezIni->format('U'), $validezFin->format('U'), $token->id);
        $token->save();

        //////////////////AUDITORIA/////////////////////////////////////////
        if (!$usuario->relationLoaded('persona')) {
            $usuario->load('persona');
        }
        if (!$token->relationLoaded('user')) {
            $token->load('user.persona');
        }
        $nombresAudit = ($usuario->persona) ? trim(($usuario->persona->nombres ?? "") . " " . ($usuario->persona->apellidos ?? "")) : $usuario->usuario;
        AuditoriaController::auditoria('Se inició la sesión del usuario '.$nombresAudit, $usuario->usuario, $nombresAudit, $request->ip());

        $usuario->int = 0;
        $usuario->save();

        //devolver el token ya conformado
        return BestHelper::Data(new TokenResource($token), 200, true, 'Inicio de sesión satisfactorio.');
    }

    //Chequear token
    public function checkLogin(Request $request,&$code, &$message){
        //procesar token
        $tokenFormateado = str_replace('Bearer ', '',$request->header('Authorization'));

        $tokenFragment = explode('.',$tokenFormateado, 3);
        $headerToken = $tokenFragment[0];
        $payloadToken = $tokenFragment[1];
        $signatureTokenRequest = $tokenFragment[2];

        //verificar que el token no esté corrupto
        $secretKeyApk = env('SECRET_KEY');

        $headerTokenDecod = json_decode(base64_decode($headerToken));
        $alg = $headerTokenDecod->alg;

        $unsignedToken = $headerToken.'.'.$payloadToken;
        $signatureToken = hash_hmac($alg, $unsignedToken, $secretKeyApk);

        if ($signatureTokenRequest != $signatureToken) {
            $code = 400;
            $message = 'Petición inválida.';
            return false;
        }

        // verificar la existencia del token en la BD
        $tokenBD = Token::where('token', $tokenFormateado)->first();
        if (!$tokenBD) {
            $code = 409;
            $message = 'Sesión inválida. Debe autenticarse primero.';
            return false;
        }

        //realizar verificaciones de tiempo
        $now = now();

        //si el token tiene validez inicial (con respecto a la fecha de creación)
        $validezIni = new DateTime($tokenBD->created_at);
        $validezIni->modify($tokenBD->validez_ini);
        if ($now < $validezIni) {
            $code = 425;
            $message = 'Inicie sesión en otro momento.';
            return false;
        }

        //si el token tiene validez intermedia (con respecto a la fecha de uso)
        $validezInter = $tokenBD->used_at? new DateTime($tokenBD->used_at) : new DateTime($now);
        $validezInter->modify($tokenBD->validez_inter);
        if ($now > $validezInter) {
            $code = 410;
            $tokenBD->delete();
            $message = 'Por seguridad cerramos su sesión después de varios minutos sin utilizar la aplicación.';
            return false;
        }

        //si el token tiene validez final (con respecto a la fecha de creación)
        $validezFin = new DateTime($tokenBD->created_at);
        $validezFin->modify($tokenBD->validez_fin);
        if ($now > $validezFin) {
            $tokenBD->delete();
            $code = 410;
            $message = 'Por seguridad cerramos su sesión despúes de varias horas.';
            return false;
        }

        //si llegó la ejecución hasta aquí es que todo está OK.
        $tokenBD->used_at = $now;//Se actualiza la última vez que se utilizó el token
        $tokenBD->save();
        return true;
    }

    /**
    * Logout de usuario
    */
    public function logout(Request $request){
        //////////////////AUDITORIA/////////////////////////////////////////
        $user = Helpers::user_log_all($request->header('Authorization'));
        if ($user && !$user->relationLoaded('persona')) {
            $user->load('persona');
        }
        $nombresAudit = ($user && $user->persona) ? trim(($user->persona->nombres ?? "") . " " . ($user->persona->apellidos ?? "")) : ($user->usuario ?? "");
        AuditoriaController::auditoria('Se cerró la sesión del usuario '.$nombresAudit, $user->usuario ?? '---', $nombresAudit, $request->ip());

        $tokenFormateado = str_replace('Bearer ', '',$request->header('Authorization'));
        Token::where('token', $tokenFormateado)->delete();

        return BestHelper::Data(null, 200, true, 'Sesión cerrada satisfactoriamente.');
    }

    /**
    * Info Usuario
    */
    public function auth_me(Request $request){
        //////////////////AUDITORIA/////////////////////////////////////////
        $user = Helpers::user_log_all($request->header('Authorization'));
        if ($user && !$user->relationLoaded('persona')) {
            $user->load('persona');
        }
        $nombresAudit = ($user && $user->persona) ? trim(($user->persona->nombres ?? "") . " " . ($user->persona->apellidos ?? "")) : ($user->usuario ?? "");
        AuditoriaController::auditoria('Consultar usuario', $user->usuario ?? '---', $nombresAudit, $request->ip());

        return BestHelper::Data($user, 200, true, 'Consulta generada satisfactoriamente.');
    }

    ///////////////////////////////// API Servicio Identity Server //////////////////////////////////

    /**
    * Devolver Token de acceso y Token ID 
    */
    public function get_token(TokenRequest_v1 $request){
        $jwtToken = Http::asForm()->withOptions([
            'verify' => false,
        ])->post('https://10.10.3.100:9444/oauth2/token', [
            'code' => $request->code,
            'client_id' => $request->client_id,
            'grant_type' => "authorization_code",
            'redirect_uri' => $request->redirect_uri,
        ]);

        return $jwtToken;
    }

    
    /**
    * Verificar Token
    */
    public function verificar_token(Request $request){
        return 'respuesta satisfactoria';
    }

    /**
    * Revocar Token
    */
    public function revocar_token(Request $request){
        blacklist::create([
            'token' => $request->header('X-Authorization'),
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Revocar token');

        return response()->json([
            'message' => 'Token revocado satisfactoriamente'
        ]);
    }

    // Validar Token
    public function validar_token(Request $request,&$code, &$message){
        
        // Validar la estructura del Token
        if (strpos($request->header('X-Authorization'), '.') !== false) {
            list($base64Header, $base64Payload, $base64Signature) = explode('.', $request->header('X-Authorization'));
        }else {
            $code = 400;
            $message = "Token invalido";
            return false;
        }
        $signature = Helpers::safeUrlDecode($base64Signature);

        // Validar si el Token esta en la blacklist
        $blacklist = blacklist::all()->pluck('token');
        if ($blacklist->contains($request->header('X-Authorization'))) {
            $code = 400;
            $message = "Token invalido";
            return false;
        }

        $publicKey = file_get_contents(env('WSO_KEY'));
        // Validar la firma del Token
        if (openssl_verify("$base64Header.$base64Payload", $signature, $publicKey, OPENSSL_ALGO_SHA256)){
            $payload = json_decode(base64_decode($base64Payload));

            // Validar el tiempo de expiracion del Token
            if (time() > $payload->exp) {
                $code = 400;
                $message = "Token expirado";
                return false;
            }

            // Validar si el Token es propio de la aplicación
            if (env('CLIENT_ID') !== $payload->client_id) {
                $code = 400;
                $message = "Token inválido";
                return false;
            }

            $code = 200;
            $message = "Token valido";
            return true;
        } else{
            $code = 400;
            $message = "Token invalido";
            return false;
        }
    }
}

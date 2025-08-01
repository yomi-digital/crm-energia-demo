<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    /**
    * Create user
    *
    * @param  [string] name
    * @param  [string] email
    * @param  [string] password
    * @param  [string] password_confirmation
    * @return [string] message
    */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email'=>'required|string|unique:users',
            'password'=>'required|string',
            'c_password' => 'required|same:password'
        ]);

        $user = new User([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($user->save()) {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'Successfully created user!',
                'accessToken'=> $token,
            ], 201);
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
    }

    /**
    * Login user and create token
    *
    * @param  [string] email
    * @param  [string] password
    * @param  [boolean] remember_me
    */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);
        if(! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();
        
        // Aggiorna IP e timestamp di login
        $user->update([
            'ip' => $request->ip(),
            'last_login_at' => now(),
        ]);
        
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        // Fill required data for theme
        $user->fullName = trim(implode(' ', [$user->name, $user->last_name]));
        // $user->role = $user->roles()->first()->name;
        $user->avatar = $user->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->fullName) . '&background=random&color=fff';

        $userAbilities = $this->getUserAbilties($user);

        // This section is to allow the group heading to be shown in the vertical navigation
        $headings = [
            'workflow' => [
                'access customers',
                'access paperworks',
            ],
            'configuration' => [
                'access mandates',
                'access agencies',
                'access brands',
                'access products',
            ],
            'communications' => [
                'access communications',
                'access alerts',
                'access tickets',
            ],
            'business' => [
                'access business-economy',
                'access business-reconciliation',
                'access business-appointments',
            ],
            'administration' => [
                'access users',
                'access logs',
            ],
            'reports' => [
                'access reports-admin',
                'access reports-production',
                'access reports-appointments',
            ]
        ];

        if ($user->hasRole('gestione')) {
            $userAbilities[] = [
                'action' => 'manage',
                'subject' => 'all',
            ];
        }

        foreach ($headings as $heading => $abilities) {
            $hasAbility = false;
            foreach ($abilities as $ability) {
                if ($user->can($ability)) {
                    $hasAbility = true;
                    break;
                }
            }
            if ($hasAbility) {
                $userAbilities[] = [
                    'action' => 'see',
                    'subject' => $heading,
                ];
            }
        }

        return response()->json([
            'accessToken' => $token,
            'token_type' => 'Bearer',
            // 'userAbilityRules' => [
            //     [
            //         'action' => 'access',
            //         'subject' => 'customers',
            //     ],
            // ],
            'userAbilityRules' => $userAbilities,
            'userData' => $user,
        ]);
    }

    /**
    * Get the authenticated User
    *
    * @return [json] user object
    */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
    * Logout user (Revoke the token)
    *
    * @return [string] message
    */
    public function logout(Request $request)
    {
        // Aggiorna timestamp di logout
        $request->user()->update([
            'last_logout_at' => now(),
        ]);
        
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function csrf(Request $request)
    {
        return response()->json([
            'csrf_token' => csrf_token(),
        ]);
    }

    public function getUserAbilties($user)
    {
        $abilities = [];
        $permissions = \App\Models\User::find($user->id)->getAllPermissions();
        foreach ($permissions as $permission) {
            $split = explode(' ', $permission->name);
            $abilities[] = [
                'action' => $split[0],
                'subject' => $split[1],
            ];
        }

        return $abilities;
    }
}

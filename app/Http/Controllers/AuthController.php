<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use External\Bar\Auth\LoginService;
use External\Bar\Exceptions\ServiceUnavailableException;
use External\Baz\Auth\Authenticator;
use External\Baz\Auth\Responses\Success;
use External\Foo\Auth\AuthWS;
use External\Foo\Exceptions\AuthenticationFailedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

class AuthController extends Controller
{
    protected string $name = 'name';

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {

        $valid = $this->validate($request, [
            $this->loginName() => 'required', 'password' => 'required',
        ]);

        try {
            $loginValue = $this->getLoginByCompany($request);
        } catch (ServiceUnavailableException | AuthenticationFailedException $e) {
            return response()->json([
                'status' => 'failure',
            ]);
        }

        if (!$valid || !$loginValue) {
            return response()->json([
                'status' => 'failure',
            ]);
        }

        if (!$token = auth()->attempt($valid)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::created(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function userProfile(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function createNewToken(string $token): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
        ]);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function loginName(): string
    {
        return property_exists($this, 'name') ? $this->name : 'email';
    }

    /**
     * @param $request
     *
     * @return bool
     *
     * @throws AuthenticationFailedException
     * @throws ServiceUnavailableException
     */
    private function getLoginByCompany($request): bool
    {
        $business = substr($request->name, 0, 3);

        switch ($business) {
            case 'FOO':
                try {
                    (new AuthWS())->authenticate($request->name, $request->password);
                    return true;
                } catch (Exception $e) {
                    throw new AuthenticationFailedException('Wrong login data FOO');
                }
            case 'BAR':
                try {
                    return (new LoginService())->login($request->name, $request->password);
                } catch (Exception $e) {
                    throw new ServiceUnavailableException('Wrong login data BAR');
                }
            case 'BAZ':
                $result = (new Authenticator())->auth($request->name, $request->password);
                if ($result instanceof Success) {
                    return true;
                } else {
                    throw new AuthenticationFailedException('Wrong login data BAZ');
                }
        }

        return false;
    }
}

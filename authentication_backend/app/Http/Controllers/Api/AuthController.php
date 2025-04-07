<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Mail\EmailVerificationMail;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Create new user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'verification_token' => Str::random(60), // Always generate token for new user
            ]);

            // Send email verification link (always for new users)
            Mail::to($user->email)->send(new EmailVerificationMail($user));

            // Generate auth token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response
            return response()->json([
                'message' => 'User registered successfully. Please verify your email.',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            $user = User::where('email', $validatedData['email'])->first();
            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                // Generate new token if needed and send verification email
                if (!$user->verification_token) {
                    $user->verification_token = Str::random(60);
                    $user->save();
                }
                Mail::to($user->email)->send(new EmailVerificationMail($user));

                return response()->json([
                    'message' => 'email_not_verified',
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // Verify Email
    public function verifyEmail($token)
    {
        try {
            $user = User::where('verification_token', $token)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Verification link has expired or is invalid.',
                    'status' => 'expired',
                ], 410); // 410 Gone for expired resources
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified.',
                    'status' => 'already_verified',
                ], 200);
            }

            $user->email_verified_at = now();
            $user->verification_token = null; // Clear token after use
            $user->save();

            $authToken = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Email verified successfully.',
                'access_token' => $authToken,
                'token_type' => 'Bearer',
                'status' => 'success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to verify email.',
                'error' => $e->getMessage(),
                'status' => 'failed',
            ], 500);
        }
    }

    // Resend Verification Email
    public function resendVerificationEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email is already verified.',
                    'status' => 'already_verified',
                ], 200);
            }

            // Generate new token
            $user->verification_token = Str::random(60);
            $user->save();

            Mail::to($user->email)->send(new EmailVerificationMail($user));

            return response()->json([
                'message' => 'A fresh verification link has been sent to your email.',
                'status' => 'success',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'status' => 'failed',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send verification email.',
                'error' => $e->getMessage(),
                'status' => 'failed',
            ], 500);
        }
    }


    // Change Password
    public function changePassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);
            $user = Auth::user();
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect'],
                ]);
            }
            $user->update([
                'password' => Hash::make($validatedData['new_password'])
            ]);
            // Revoke all tokens
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Password changed successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    
    // Send Password Reset Link
    public function sendPasswordResetLink(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);
            $email = $request->email;
            // Generate a secure token
            $token = Str::random(60);
            // Save token to password_resets table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'email' => $email,
                    'token' => $token, // Store raw token, not hashed
                    'created_at' => now(),
                ]
            );
            // Send the email using a Mailable class
            Mail::to($email)->send(new PasswordResetMail($token, $email));
            return response()->json([
                'message' => 'Password reset link sent to your email',
                'status' => 'success',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'status' => 'failed',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send password reset link',
                'error' => $e->getMessage(), // In production, you might want to hide detailed errors
                'status' => 'failed',
            ], 500);
        }
    }
    // Reset Password
    public function resetPassword(Request $request, $token)
    {
        try {
            // Clean up expired tokens (2 minutes expiration)
            DB::table('password_reset_tokens')
                ->where('created_at', '<=', Carbon::now()->subMinutes(2))
                ->delete();
            // Validate the request
            $request->validate([
                'password' => 'required|confirmed|min:8', // Added min length for security
            ]);
            // Find the reset token
            $passwordReset = DB::table('password_reset_tokens')
                ->where('token', $token)
                ->first();
            if (!$passwordReset) {
                return response()->json([
                    'message' => 'Token is invalid or expired',
                    'status' => 'failed'
                ], 404);
            }
            // Update user's password
            $user = User::where('email', $passwordReset->email)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'status' => 'failed'
                ], 404);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            // Delete the used token
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();
            return response()->json([
                'message' => 'Password reset successfully',
                'status' => 'success'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'status' => 'failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password',
                'status' => 'failed'
            ], 500);
        }
    }
    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
